<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Favorites_model extends CI_Model {

    /**
     * Get all favorite songs for a user.
     *
     * @param int $userId
     * @param int $limit
     * @return array
     */
    public function get_by_user($userId, $limit = 20)
    {
        $this->db->select('
            songs.id, songs.title, songs.artist, songs.duration_seconds,
            songs.cover_path, genres.name AS genre_name,
            favorites.created_at AS favorited_at
        ');
        $this->db->from('favorites');
        $this->db->join('songs', 'songs.id = favorites.song_id');
        $this->db->join('genres', 'genres.id = songs.genre_id', 'left');
        $this->db->where('favorites.user_id', $userId);
        $this->db->where('songs.is_active', 1);
        $this->db->order_by('favorites.created_at', 'DESC');
        $this->db->limit($limit);
        return $this->db->get()->result();
    }

    /**
     * Check if a song is favorited by the user.
     *
     * @param int $userId
     * @param int $songId
     * @return bool
     */
    public function is_favorited($userId, $songId)
    {
        return $this->db->where('user_id', $userId)
                        ->where('song_id', $songId)
                        ->count_all_results('favorites') > 0;
    }

    /**
     * Add a song to favorites.
     *
     * @param int $userId
     * @param int $songId
     * @return bool
     */
    public function add($userId, $songId)
    {
        if ($this->is_favorited($userId, $songId)) return true;
        return $this->db->insert('favorites', [
            'user_id' => $userId,
            'song_id' => $songId,
        ]);
    }

    /**
     * Remove a song from favorites.
     *
     * @param int $userId
     * @param int $songId
     * @return bool
     */
    public function remove($userId, $songId)
    {
        $this->db->where('user_id', $userId)
                 ->where('song_id', $songId)
                 ->delete('favorites');
        return $this->db->affected_rows() > 0;
    }

    /**
     * Get total count of favorites for a user.
     *
     * @param int $userId
     * @return int
     */
    public function count_by_user($userId)
    {
        return $this->db->where('user_id', $userId)->count_all_results('favorites');
    }
}
