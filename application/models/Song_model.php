<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Model Song — mengelola data lagu dari tabel `songs`.
 *
 * Menyediakan operasi baca untuk katalog publik (paginasi, pencarian)
 * dan detail lagu tunggal. Semua query hanya menampilkan lagu aktif (is_active = 1).
 */
class Song_model extends CI_Model {

    /**
     * Ambil daftar lagu aktif dengan paginasi, lengkap dengan nama genre.
     *
     * @param int $page      Halaman saat ini (1-based)
     * @param int $per_page  Jumlah item per halaman
     * @return array         Array objek lagu
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
     * Hitung total lagu aktif — digunakan untuk membangun paginasi.
     *
     * @return int
     */
    public function count_all()
    {
        $this->db->where('is_active', 1);
        return $this->db->count_all_results('songs');
    }

    /**
     * Ambil satu lagu berdasarkan ID beserta informasi genre-nya.
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
     * Cari lagu berdasarkan judul atau nama artis (LIKE).
     * Hasil diurutkan berdasarkan lagu terbaru.
     *
     * @param string $query    Kata kunci pencarian
     * @param int    $page     Halaman saat ini
     * @param int    $per_page Jumlah per halaman
     * @return array
     */
    public function search($query, $page = 1, $per_page = 24)
    {
        $offset = max(0, ($page - 1) * $per_page);

        $this->db->select('
            songs.id, songs.title, songs.artist,
            songs.duration_seconds, songs.cover_path,
            genres.name AS genre_name
        ');
        $this->db->from('songs');
        $this->db->join('genres', 'genres.id = songs.genre_id', 'left');
        $this->db->where('songs.is_active', 1);
        // Grup OR agar pencarian cocok dengan judul ATAU artis
        $this->db->group_start();
        $this->db->like('songs.title', $query);
        $this->db->or_like('songs.artist', $query);
        $this->db->group_end();
        $this->db->order_by('songs.created_at', 'DESC');
        $this->db->limit($per_page, $offset);

        return $this->db->get()->result();
    }

    /**
     * Hitung jumlah hasil pencarian (tanpa paginasi).
     *
     * @param string $query
     * @return int
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
