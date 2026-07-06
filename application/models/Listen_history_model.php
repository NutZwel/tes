<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Model Listen_History — mencatat dan mengambil riwayat pemutaran lagu.
 *
 * Mencakup log pemutaran per user, lagu yang sedang tren secara global,
 * rekomendasi berdasarkan genre favorit, serta pembersihan riwayat.
 */
class Listen_history_model extends CI_Model {

    /**
     * Ambil lagu yang baru didengarkan user (deduplikasi per song_id).
     *
     * Menggunakan GROUP BY agar lagu yang sama tidak muncul
     * berurutan jika diputar beberapa kali.
     *
     * @param int $userId
     * @param int $limit
     * @return array
     */
    public function get_recent($userId, $limit = 10)
    {
        $sql = "
            SELECT songs.id AS id, songs.title, songs.artist, songs.duration_seconds,
                   songs.cover_path, songs.file_path, songs.is_active, genres.name AS genre_name
            FROM listen_history h
            JOIN songs ON songs.id = h.song_id
            LEFT JOIN genres ON genres.id = songs.genre_id
            WHERE h.user_id = ?
              AND songs.is_active = 1
              AND songs.id IS NOT NULL
            GROUP BY h.song_id
            ORDER BY MAX(h.played_at) DESC
            LIMIT ?
        ";
        return $this->db->query($sql, [(int) $userId, (int) $limit])->result();
    }

    /**
     * Catat event pemutaran lagu oleh user.
     *
     * @param int $userId
     * @param int $songId
     * @return int  ID entri history
     */
    public function log_play($userId, $songId)
    {
        $this->db->insert('listen_history', [
            'user_id' => $userId,
            'song_id' => $songId,
        ]);
        return (int) $this->db->insert_id();
    }

    /**
     * Hitung total pemutaran oleh user.
     *
     * @param int $userId
     * @return int
     */
    public function count_by_user($userId)
    {
        return $this->db->where('user_id', (int) $userId)->count_all_results('listen_history');
    }

    /**
     * Ambil seluruh riwayat pemutaran user.
     *
     * @param int $userId
     * @param int $limit
     * @return array
     */
    public function get_by_user($userId, $limit = 100)
    {
        $sql = "
            SELECT h.played_at, songs.id AS id, songs.title, songs.artist, songs.duration_seconds,
                   songs.cover_path, songs.file_path, songs.is_active, genres.name AS genre_name
            FROM listen_history h
            JOIN songs ON songs.id = h.song_id
            LEFT JOIN genres ON genres.id = songs.genre_id
            WHERE h.user_id = ?
              AND songs.is_active = 1
            ORDER BY h.played_at DESC
            LIMIT ?
        ";
        return $this->db->query($sql, [(int) $userId, (int) $limit])->result();
    }

    /**
     * Hapus seluruh riwayat pemutaran user.
     *
     * @param int $userId
     * @return bool
     */
    public function clear_for_user($userId)
    {
        return $this->db->where('user_id', $userId)->delete('listen_history');
    }

    /**
     * Ambil lagu yang sedang tren berdasarkan jumlah pemutaran.
     *
     * Digunakan di dashboard untuk menampilkan section "Trending".
     *
     * @param int $limit
     * @param int $days   Periode lookback dalam hari
     * @return array
     */
    public function get_trending($limit = 8, $days = 14)
    {
        $sql = "
            SELECT songs.id, songs.title, songs.artist, songs.duration_seconds,
                   songs.cover_path, songs.file_path, songs.is_active, genres.name AS genre_name,
                   COUNT(h.id) AS play_count
            FROM listen_history h
            JOIN songs ON songs.id = h.song_id
            LEFT JOIN genres ON genres.id = songs.genre_id
            WHERE h.played_at >= DATE_SUB(NOW(), INTERVAL ? DAY)
              AND songs.is_active = 1
              AND songs.id IS NOT NULL
            GROUP BY h.song_id
            ORDER BY play_count DESC
            LIMIT ?
        ";
        return $this->db->query($sql, [(int) $days, (int) $limit])->result();
    }

    /**
     * Dapatkan rekomendasi lagu untuk user berdasarkan genre favorit.
     *
     * Mengidentifikasi 3 genre yang paling sering diputar user,
     * lalu mengambil lagu dari genre tersebut (kecuali yang sudah
     * difavoritkan atau didengarkan dalam 7 hari terakhir).
     * Jika tidak ada data pemutaran, kembalikan lagu terbaru sebagai fallback.
     *
     * @param int   $userId
     * @param int   $limit
     * @return array
     */
    public function get_recommendations($userId, $limit = 8)
    {
        // Langkah 1: cari genre favorit user dari riwayat pemutaran
        $subSql = "
            SELECT g.id, COUNT(*) AS cnt
            FROM listen_history h
            JOIN songs s ON s.id = h.song_id
            JOIN genres g ON g.id = s.genre_id
            WHERE h.user_id = ?
            GROUP BY g.id
            ORDER BY cnt DESC
            LIMIT 3
        ";
        $genres = $this->db->query($subSql, [(int) $userId])->result();
        $genreIds = array_map(function($g) { return $g->id; }, $genres);

        // Fallback: jika belum ada riwayat, tampilkan lagu terbaru
        if (empty($genreIds)) {
            $this->db->select('songs.id, songs.title, songs.artist, songs.duration_seconds, songs.cover_path, songs.file_path, songs.is_active, genres.name AS genre_name');
            $this->db->from('songs');
            $this->db->join('genres', 'genres.id = songs.genre_id', 'left');
            $this->db->where('songs.is_active', 1);
            $this->db->order_by('songs.created_at', 'DESC');
            $this->db->limit($limit);
            return $this->db->get()->result();
        }

        // Langkah 2: kecualikan lagu yang sudah difavoritkan atau baru didengar
        $excludeSql = "
            SELECT song_id FROM favorites WHERE user_id = ?
            UNION
            SELECT DISTINCT song_id FROM listen_history WHERE user_id = ? AND played_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
        ";
        $excluded = $this->db->query($excludeSql, [(int) $userId, (int) $userId])->result();
        $excludeIds = array_map(function($e) { return $e->song_id; }, $excluded);

        $this->db->select('songs.id, songs.title, songs.artist, songs.duration_seconds, songs.cover_path, songs.file_path, songs.is_active, genres.name AS genre_name');
        $this->db->from('songs');
        $this->db->join('genres', 'genres.id = songs.genre_id', 'left');
        $this->db->where('songs.is_active', 1);
        $this->db->where_in('songs.genre_id', $genreIds);
        // Jangan rekomendasikan lagu yang sudah dikenal user
        if (!empty($excludeIds)) {
            $this->db->where_not_in('songs.id', $excludeIds);
        }
        $this->db->order_by('songs.created_at', 'DESC');
        $this->db->limit($limit);
        return $this->db->get()->result();
    }
}
