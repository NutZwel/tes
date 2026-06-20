<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Song_model extends CI_Model {

    /**
     * Get paginated list of active songs with genre name.
     *
     * @param int $page      Current page number (1-based)
     * @param int $per_page  Items per page
     * @return array         Array of song objects
     */
    public function get_paginated($page = 1, $per_page = 24)
    {
        $offset = max(0, ($page - 1) * $per_page);

        $this->db->select('
            songs.id,
            songs.title,
            songs.artist,
            songs.duration_seconds,
            songs.cover_path,
            songs.created_at,
            genres.name AS genre_name
        ');
        $this->db->from('songs');
        $this->db->join('genres', 'genres.id = songs.genre_id', 'left');
        $this->db->where('songs.is_active', 1);
        $this->db->order_by('songs.created_at', 'DESC');
        $this->db->limit($per_page, $offset);

        return $this->db->get()->result();
    }

    /**
     * Get total count of active songs (for pagination).
     *
     * @return int
     */
    public function count_all()
    {
        $this->db->where('is_active', 1);
        return $this->db->count_all_results('songs');
    }

    /**
     * Get a single song by ID with genre.
     *
     * @param int $id
     * @return object|null
     */
    public function get_by_id($id)
    {
        $this->db->select('
            songs.*,
            genres.name AS genre_name
        ');
        $this->db->from('songs');
        $this->db->join('genres', 'genres.id = songs.genre_id', 'left');
        $this->db->where('songs.id', $id);
        $this->db->where('songs.is_active', 1);

        return $this->db->get()->row();
    }

    /**
     * Search songs by title or artist.
     */
    public function search($query, $page = 1, $per_page = 24)
    {
        $offset = max(0, ($page - 1) * $per_page);
        $like = '%' . $this->db->escape_like_str($query) . '%';

        $this->db->select('
            songs.id, songs.title, songs.artist,
            songs.duration_seconds, songs.cover_path,
            genres.name AS genre_name
        ');
        $this->db->from('songs');
        $this->db->join('genres', 'genres.id = songs.genre_id', 'left');
        $this->db->where('songs.is_active', 1);
        $this->db->group_start();
        $this->db->like('songs.title', $query);
        $this->db->or_like('songs.artist', $query);
        $this->db->group_end();
        $this->db->order_by('songs.created_at', 'DESC');
        $this->db->limit($per_page, $offset);

        return $this->db->get()->result();
    }

    /**
     * Count search results.
     */
    public function count_search($query)
    {
        $this->db->from('songs');
        $this->db->where('songs.is_active', 1);
        $this->db->group_start();
        $this->db->like('songs.title', $query);
        $this->db->or_like('songs.artist', $query);
        $this->db->group_end();
        return $this->db->count_all_results();
    }
}
