<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Listen_history_model extends CI_Model {

    /**
     * Get recently played tracks for a user.
     * Deduplicates consecutive plays of the same song.
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
     * Log a play event.
     *
     * @param int $userId
     * @param int $songId
     * @return int  Inserted history entry ID
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
     * Count total listens for a user.
     *
     * @param int $userId
     * @return int
     */
    public function count_by_user($userId)
    {
        return $this->db->where('user_id', (int) $userId)->count_all_results('listen_history');
    }

    /**
     * Get all listen history entries for a user.
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
     * Clear listen history for a user.
     *
     * @param int $userId
     * @return bool
     */
    public function clear_for_user($userId)
    {
        return $this->db->where('user_id', $userId)->delete('listen_history');
    }

    /**
     * Get trending songs based on total play count across all users.
     *
     * @param int $limit
     * @param int $days   Lookback period
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
     * Get song recommendations for a user based on their most-played genres.
     *
     * @param int   $userId
     * @param int   $limit
     * @return array
     */
    public function get_recommendations($userId, $limit = 8)
    {
        // Find genres the user listens to most
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

        if (empty($genreIds)) {
            // Fallback: latest songs
            $this->db->select('songs.id, songs.title, songs.artist, songs.duration_seconds, songs.cover_path, songs.file_path, songs.is_active, genres.name AS genre_name');
            $this->db->from('songs');
            $this->db->join('genres', 'genres.id = songs.genre_id', 'left');
            $this->db->where('songs.is_active', 1);
            $this->db->order_by('songs.created_at', 'DESC');
            $this->db->limit($limit);
            return $this->db->get()->result();
        }

        // Exclude already-favorited or recently-listened songs
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
        if (!empty($excludeIds)) {
            $this->db->where_not_in('songs.id', $excludeIds);
        }
        $this->db->order_by('songs.created_at', 'DESC');
        $this->db->limit($limit);
        return $this->db->get()->result();
    }
}
