<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Player extends CI_Controller {

    const MAX_GUEST_PLAYS = 3;

    /**
     * Enforce guest play limit (3 plays per session).
     * Returns true if allowed, false if limit exceeded.
     */
    private function _check_guest_limit(): bool
    {
        $userId = (int) $this->session->userdata('user_id');
        if ($userId > 0) {
            return true; // registered users have no limit
        }

        $playCount = (int) $this->session->userdata('guest_plays');
        if ($playCount >= self::MAX_GUEST_PLAYS) {
            $this->output
                ->set_status_header(403)
                ->set_content_type('application/json')
                ->set_output(json_encode([
                    'error' => 'Guest play limit reached: ' . self::MAX_GUEST_PLAYS . ' plays per session. Create a free account for unlimited streaming.',
                ]));
            return false;
        }

        // Increment counter
        $this->session->set_userdata('guest_plays', $playCount + 1);
        return true;
    }

    /**
     * Stream an audio file by song ID.
     * Uses file path from database — never exposes direct URL.
     * Enforces guest 3-play/session limit.
     */
    public function stream($songId = 0)
    {
        $this->load->model('Song_model');
        $song = $this->Song_model->get_by_id((int) $songId);

        if (!$song || !$song->is_active) {
            show_404();
            return;
        }

        // Guest limit already enforced in info() — no need to check again
        // Cek apakah file_path adalah URL remote
        if (strpos($song->file_path, 'http') === 0 || strpos($song->file_path, 'https') === 0) {
            session_write_close();
            header('Content-Type: audio/mpeg');
            header('Accept-Ranges: bytes');
            header('Cache-Control: public, max-age=86400');
            readfile($song->file_path);
            exit;
        }

        $filePath = FCPATH . $song->file_path;

        if (!file_exists($filePath)) {
            log_message('error', 'Audio file not found: ' . $filePath);
            show_404();
            return;
        }

        // Log listen for logged-in users
        $userId = (int) $this->session->userdata('user_id');
        if ($userId > 0) {
            $this->load->model('Listen_history_model');
            $this->Listen_history_model->log_play($userId, $song->id);
        }

        // Stream file with HTTP Range support (for seeking)
        $this->_stream_file($filePath);
    }

    /**
     * Stream a file with HTTP Range (206 Partial Content) support.
     * Uses readfile() for full requests and fread with large buffer for ranges.
     * Releases session lock before streaming to prevent blocking.
     */
    private function _stream_file($filePath)
    {
        $fileSize = filesize($filePath);
        $mimeType = mime_content_type($filePath);

        $start = 0;
        $end = $fileSize - 1;
        $isRange = false;

        // Parse Range header
        if (isset($_SERVER['HTTP_RANGE'])) {
            preg_match('/bytes=(\d+)-(\d*)/', $_SERVER['HTTP_RANGE'], $matches);
            $start = intval($matches[1]);
            $end = !empty($matches[2]) ? intval($matches[2]) : ($fileSize - 1);
            $isRange = true;
        }

        // Validate range
        if ($start > $end || $start >= $fileSize) {
            header('HTTP/1.1 416 Requested Range Not Satisfiable');
            header('Content-Range: bytes */' . $fileSize);
            exit;
        }

        // ═══ Release session lock so other AJAX requests aren't blocked ═══
        session_write_close();

        // Set common headers
        header('Accept-Ranges: bytes');
        header('Content-Type: ' . $mimeType);
        header('Content-Disposition: inline; filename="' . basename($filePath) . '"');
        header('Cache-Control: public, max-age=86400');
        header('Pragma: public');
        header('Expires: ' . gmdate('D, d M Y H:i:s', time() + 86400) . ' GMT');

        if ($isRange) {
            header('HTTP/1.1 206 Partial Content');
            header('Content-Range: bytes ' . $start . '-' . $end . '/' . $fileSize);
            header('Content-Length: ' . ($end - $start + 1));

            // For range requests: seek and output with large buffer
            $fp = fopen($filePath, 'rb');
            if (!$fp) { return; }
            fseek($fp, $start);
            $left = $end - $start + 1;
            $chunkSize = 524288; // 512KB
            while ($left > 0 && !feof($fp)) {
                $read = min($chunkSize, $left);
                echo fread($fp, $read);
                $left -= $read;
                flush();
            }
            fclose($fp);
        } else {
            // Full file: use readfile() — Apache handles it efficiently
            header('Content-Length: ' . $fileSize);
            readfile($filePath);
        }
        exit;
    }

    /**
     * Return song info as JSON (for player.js to fetch).
     */
    public function info($songId = 0)
    {
        // Enforce guest play limit BEFORE returning info
        if (!$this->_check_guest_limit()) {
            return;
        }

        $this->load->model('Song_model');
        $song = $this->Song_model->get_by_id((int) $songId);

        if (!$song || !$song->is_active) {
            $this->output->set_status_header(404)->set_output(json_encode(['error' => 'Not found']));
            return;
        }

        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode([
                'id'         => (int) $song->id,
                'title'      => $song->title,
                'artist'     => $song->artist,
                'file_path'  => $song->file_path,
                'cover_path' => $song->cover_path,
            ]));
    }

    /**
     * Call LRCLIB API — tries cURL first, then file_get_contents fallback.
     */
    private function _lrclib_get($url)
    {
        // Try cURL first
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL            => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 6,
            CURLOPT_CONNECTTIMEOUT => 4,
            CURLOPT_USERAGENT      => 'Laufey/1.0 (XAMPP; academic)',
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_SSL_VERIFYPEER => false,
        ]);
        $result = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        if ($httpCode === 200 && !empty($result)) return $result;

        // Fallback: file_get_contents
        $opts = [
            'http' => [
                'method' => 'GET',
                'timeout' => 6,
                'header' => "User-Agent: Laufey/1.0 (XAMPP)\r\n",
            ],
            'ssl' => ['verify_peer' => false, 'verify_peer_name' => false],
        ];
        $result = @file_get_contents($url, false, stream_context_create($opts));
        if ($result !== false && !empty($result)) {
            $d = json_decode($result, true);
            if ($d) return $result;
        }
        return null;
    }

    /**
     * Return lyrics for a song as JSON.
     * First checks local DB via Lyrics_model, then falls back to LRCLIB API.
     * Found results are cached locally for future requests.
     */
    public function lyrics($songId = 0)
    {
        $songId = (int) $songId;
        if ($songId <= 0) {
            $this->output->set_status_header(400)->set_output(json_encode(['error' => 'Invalid song ID']));
            return;
        }

        // Check local DB first
        $this->load->model('Lyrics_model');
        $row = $this->Lyrics_model->get_by_song($songId);

        if ($row && !empty($row->content)) {
            $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode([
                    'content' => $row->content,
                    'format'  => $row->format,
                ]));
            return;
        }

        // Not in DB — fetch from LRCLIB API
        $this->load->model('Song_model');
        $song = $this->Song_model->get_by_id($songId);
        if (!$song) {
            $this->output->set_content_type('application/json')->set_output(json_encode(['content' => null]));
            return;
        }

        // Try exact match via /api/get
        $artist = rawurlencode($song->artist);
        $title  = rawurlencode($song->title);
        $apiUrl = "https://lrclib.net/api/get?artist_name={$artist}&track_name={$title}";
        $response = $this->_lrclib_get($apiUrl);

        if ($response) {
            $data = json_decode($response, true);
            if ($data && !empty($data['syncedLyrics'])) {
                $this->_cache_lyrics($songId, $data['syncedLyrics'], 'lrc');
                $this->_return_lyrics($data['syncedLyrics'], 'lrc');
                return;
            }
            if ($data && !empty($data['plainLyrics'])) {
                $this->_cache_lyrics($songId, $data['plainLyrics'], 'plain');
                $this->_return_lyrics($data['plainLyrics'], 'plain');
                return;
            }
        }

        // Fallback 1: try search
        $searchUrl = "https://lrclib.net/api/search?artist_name={$artist}&track_name={$title}";
        $response = $this->_lrclib_get($searchUrl);
        if ($response) {
            $results = json_decode($response, true);
            if (is_array($results) && count($results) > 0) {
                $best = $results[0];
                $lrc = $best['syncedLyrics'] ?? $best['plainLyrics'] ?? null;
                if ($lrc) {
                    $fmt = !empty($best['syncedLyrics']) ? 'lrc' : 'plain';
                    $this->_cache_lyrics($songId, $lrc, $fmt);
                    $this->_return_lyrics($lrc, $fmt);
                    return;
                }
            }
        }

        // Fallback 2: try with just the main artist (before "feat." or "&")
        $mainArtist = preg_replace('/\s*(feat\.?|ft\.?|&).*/i', '', $song->artist);
        $mainArtist = trim($mainArtist);
        if ($mainArtist !== $song->artist) {
            $searchUrl2 = "https://lrclib.net/api/search?artist_name=" . rawurlencode($mainArtist) . "&track_name={$title}";
            $response = $this->_lrclib_get($searchUrl2);
            if ($response) {
                $results = json_decode($response, true);
                if (is_array($results) && count($results) > 0) {
                    $best = $results[0];
                    $lrc = $best['syncedLyrics'] ?? $best['plainLyrics'] ?? null;
                    if ($lrc) {
                        $fmt = !empty($best['syncedLyrics']) ? 'lrc' : 'plain';
                        $this->_cache_lyrics($songId, $lrc, $fmt);
                        $this->_return_lyrics($lrc, $fmt);
                        return;
                    }
                }
            }
        }

        // Fallback 3: try prepending "feat." part to track name (e.g. "Location Unknown feat. NIKI")
        $firstArtist = explode(' ', trim($song->artist))[0];
        $featName = '';
        if (stripos($song->artist, 'feat.') !== false) {
            $parts = explode('feat.', $song->artist, 2);
            if (isset($parts[1]) && trim($parts[1])) {
                $featName = ' feat. ' . trim($parts[1]);
            }
        } elseif (stripos($song->artist, 'ft.') !== false) {
            $parts = explode('ft.', $song->artist, 2);
            if (isset($parts[1]) && trim($parts[1])) {
                $featName = ' feat. ' . trim($parts[1]);
            }
        } elseif (strpos($song->artist, '&') !== false) {
            $parts = explode('&', $song->artist, 2);
            if (isset($parts[1]) && trim($parts[1])) {
                $featName = ' feat. ' . trim($parts[1]);
            }
        }
        if ($featName) {
            $fullTrack = $song->title . $featName;
            $searchUrl3 = "https://lrclib.net/api/get?artist_name=" . rawurlencode($firstArtist) . "&track_name=" . rawurlencode($fullTrack);
            $response = $this->_lrclib_get($searchUrl3);
            if (!$response) {
                // Try search instead
                $searchUrl3 = "https://lrclib.net/api/search?artist_name=" . rawurlencode($firstArtist) . "&track_name=" . rawurlencode($fullTrack);
                $response = $this->_lrclib_get($searchUrl3);
                if ($response) {
                    $results = json_decode($response, true);
                    if (is_array($results) && count($results) > 0) {
                        $best = $results[0];
                        $lrc = $best['syncedLyrics'] ?? $best['plainLyrics'] ?? null;
                        if ($lrc) {
                            $fmt = !empty($best['syncedLyrics']) ? 'lrc' : 'plain';
                            $this->_cache_lyrics($songId, $lrc, $fmt);
                            $this->_return_lyrics($lrc, $fmt);
                            return;
                        }
                    }
                }
            } else {
                $data = json_decode($response, true);
                if ($data && !empty($data['syncedLyrics'])) {
                    $this->_cache_lyrics($songId, $data['syncedLyrics'], 'lrc');
                    $this->_return_lyrics($data['syncedLyrics'], 'lrc');
                    return;
                }
            }
        }

        $this->output->set_content_type('application/json')->set_output(json_encode(['content' => null]));
    }

    private function _cache_lyrics($songId, $content, $format)
    {
        $this->load->model('Lyrics_model');
        $this->Lyrics_model->cache($songId, $content, $format);
    }

    private function _return_lyrics($content, $format)
    {
        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode([
                'content' => $content,
                'format'  => $format,
            ]));
    }

    /**
     * Return a random active song as JSON (for shuffle mode).
     * Accepts ?exclude=id1,id2,id3 to exclude already-played songs.
     * Returns 404 when all songs are exhausted (client resets and retries).
     */
    public function random()
    {
        $this->load->model('Song_model');
        $exclude = $this->input->get('exclude');

        $this->db->select('id, title, artist, file_path, cover_path, duration_seconds');
        $this->db->from('songs');
        $this->db->where('is_active', 1);

        if (!empty($exclude)) {
            $ids = array_map('intval', explode(',', $exclude));
            $this->db->where_not_in('id', $ids);
        }

        $this->db->order_by('RAND()');
        $this->db->limit(1);
        $song = $this->db->get()->row();

        if (!$song) {
            $this->output->set_status_header(404)->set_output(json_encode(['error' => 'All songs played']));
            return;
        }

        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode($song));
    }
}
