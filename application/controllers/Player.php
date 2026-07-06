<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Controller Player — menangani streaming lagu, lirik, dan kontrol pemutaran.
 *
 * Endpoint AJAX yang digunakan oleh player.js untuk:
 * - Stream audio dengan dukungan HTTP Range (seeking)
 * - Mengambil info lagu (JSON)
 * - Mengambil lirik (dari DB lokal atau LRCLIB API)
 * - Mendapatkan lagu acak untuk mode shuffle
 *
 * Menerapkan batas 3 pemutaran per sesi untuk guest.
 */
class Player extends CI_Controller {

    const MAX_GUEST_PLAYS = 3;

    /**
     * Periksa batas pemutaran guest: maksimal 3 lagu per sesi.
     *
     * @return bool true jika diizinkan, false jika melebihi batas
     */
    private function _check_guest_limit(): bool
    {
        $userId = (int) $this->session->userdata('user_id');
        // User terdaftar tidak memiliki batas pemutaran
        if ($userId > 0) {
            return true;
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

        // Increment counter di session
        $this->session->set_userdata('guest_plays', $playCount + 1);
        return true;
    }

    /**
     * Stream audio berdasarkan ID lagu.
     *
     * Mengambil path file dari database, bukan URL langsung.
     * Mendukung file lokal dan remote (HTTP/HTTPS).
     * Mencatat riwayat pemutaran untuk user terdaftar.
     *
     * @param int $songId
     */
    public function stream($songId = 0)
    {
        $this->load->model('Song_model');
        $song = $this->Song_model->get_by_id((int) $songId);

        if (!$song || !$song->is_active) {
            show_404();
            return;
        }

        // Guest limit sudah diperiksa di info() — tidak perlu periksa ulang
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

        // Catat pemutaran hanya untuk user yang login
        $userId = (int) $this->session->userdata('user_id');
        if ($userId > 0) {
            $this->load->model('Listen_history_model');
            $this->Listen_history_model->log_play($userId, $song->id);
        }

        // Stream dengan dukungan HTTP Range agar user bisa seek
        $this->_stream_file($filePath);
    }

    /**
     * Stream file dengan dukungan HTTP Range (206 Partial Content).
     *
     * Melepas session lock sebelum streaming agar request AJAX lain
     * tidak terblokir. Menggunakan buffer 512KB untuk range request.
     *
     * @param string $filePath Path absolut ke file audio
     */
    private function _stream_file($filePath)
    {
        $fileSize = filesize($filePath);
        $mimeType = mime_content_type($filePath);

        $start = 0;
        $end = $fileSize - 1;
        $isRange = false;

        // Parse header Range untuk partial content
        if (isset($_SERVER['HTTP_RANGE'])) {
            preg_match('/bytes=(\d+)-(\d*)/', $_SERVER['HTTP_RANGE'], $matches);
            $start = intval($matches[1]);
            $end = !empty($matches[2]) ? intval($matches[2]) : ($fileSize - 1);
            $isRange = true;
        }

        // Validasi range
        if ($start > $end || $start >= $fileSize) {
            header('HTTP/1.1 416 Requested Range Not Satisfiable');
            header('Content-Range: bytes */' . $fileSize);
            exit;
        }

        // ═══ Lepas session lock agar request AJAX lain tidak terblokir ═══
        session_write_close();

        // Header umum
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

            // Baca dan kirim byte yang diminta
            $fp = fopen($filePath, 'rb');
            if (!$fp) { return; }
            fseek($fp, $start);
            $left = $end - $start + 1;
            $chunkSize = 524288; // 512KB per iterasi
            while ($left > 0 && !feof($fp)) {
                $read = min($chunkSize, $left);
                echo fread($fp, $read);
                $left -= $read;
                flush();
            }
            fclose($fp);
        } else {
            // Full file: readfile() sudah efisien untuk kasus ini
            header('Content-Length: ' . $fileSize);
            readfile($filePath);
        }
        exit;
    }

    /**
     * Return info lagu sebagai JSON (dipanggil oleh player.js).
     *
     * Guest limit diperiksa di sini SEBELUM info dikembalikan,
     * sehingga guest tetap bisa membuka halaman tetapi tidak bisa memutar.
     *
     * @param int $songId
     */
    public function info($songId = 0)
    {
        // Periksa batas guest SEBELUM mengembalikan info
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
     * Request GET ke LRCLIB API — coba cURL dulu, fallback file_get_contents.
     *
     * @param string $url URL API LRCLIB
     * @return string|null Respons JSON atau null jika gagal
     */
    private function _lrclib_get($url)
    {
        // Prioritaskan cURL karena lebih andal untuk koneksi HTTPS
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

        // Fallback: file_get_contents dengan stream context
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
     * Ambil lirik lagu sebagai JSON.
     *
     * Strategi pencarian bertingkat (fallback chain):
     * 1. Cek database lokal
     * 2. LRCLIB API — exact match (/api/get)
     * 3. LRCLIB API — search (/api/search)
     * 4. Coba dengan nama artis utama (sebelum feat./&)
     * 5. Coba dengan tambahan "feat." di judul lagu
     *
     * Setiap hasil dari API disimpan (cache) ke database lokal.
     *
     * @param int $songId
     */
    public function lyrics($songId = 0)
    {
        $songId = (int) $songId;
        if ($songId <= 0) {
            $this->output->set_status_header(400)->set_output(json_encode(['error' => 'Invalid song ID']));
            return;
        }

        // Langkah 1: cek database lokal dulu
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

        // Langkah 2: fetch dari LRCLIB API
        $this->load->model('Song_model');
        $song = $this->Song_model->get_by_id($songId);
        if (!$song) {
            $this->output->set_content_type('application/json')->set_output(json_encode(['content' => null]));
            return;
        }

        // Coba exact match via /api/get
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

        // Fallback 1: coba search endpoint
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

        // Fallback 2: coba dengan nama artis utama (sebelum "feat.", "ft.", atau "&")
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

        // Fallback 3: coba tambahkan "feat." ke judul lagu
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

    /**
     * Simpan lirik ke database lokal (cache).
     *
     * @param int    $songId
     * @param string $content
     * @param string $format
     */
    private function _cache_lyrics($songId, $content, $format)
    {
        $this->load->model('Lyrics_model');
        $this->Lyrics_model->cache($songId, $content, $format);
    }

    /**
     * Kirim response JSON berisi lirik.
     *
     * @param string $content
     * @param string $format
     */
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
     * Ambil satu lagu acak sebagai JSON (untuk mode shuffle).
     *
     * Menerima parameter ?exclude=id1,id2,id3 untuk mengecualikan
     * lagu yang sudah diputar. Mengembalikan 404 jika semua lagu
     * sudah di-exclude (client akan mereset dan mencoba ulang).
     */
    public function random()
    {
        $this->load->model('Song_model');
        $exclude = $this->input->get('exclude');

        $this->db->select('id, title, artist, file_path, cover_path, duration_seconds');
        $this->db->from('songs');
        $this->db->where('is_active', 1);

        // Jangan pilih lagu yang sudah ada di daftar exclude
        if (!empty($exclude)) {
            $ids = array_map('intval', explode(',', $exclude));
            $this->db->where_not_in('id', $ids);
        }

        // ORDER BY RAND() — sederhana untuk dataset kecil
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
