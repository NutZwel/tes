<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Model Favorites — mengelola lagu favorit user.
 *
 * Menyediakan CRUD untuk tabel `favorites` dengan perlindungan
 * duplikasi dan filter hanya lagu aktif.
 */
class Favorites_model extends CI_Model {

    /**
     * Ambil daftar lagu favorit user, diurutkan dari yang terbaru difavoritkan.
     *
     * @param int $userId
     * @param int $limit  Maksimal jumlah lagu
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
        // Hanya tampilkan lagu yang masih aktif
        $this->db->where('songs.is_active', 1);
        $this->db->order_by('favorites.created_at', 'DESC');
        $this->db->limit($limit);
        return $this->db->get()->result();
    }

    /**
     * Cek apakah suatu lagu sudah difavoritkan user.
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
     * Tambah lagu ke favorit. Jika sudah ada, tidak melakukan apa-apa.
     *
     * @param int $userId
     * @param int $songId
     * @return bool
     */
    public function add($userId, $songId)
    {
        // Hindari duplikasi — cek dulu sebelum insert
        if ($this->is_favorited($userId, $songId)) return true;
        return $this->db->insert('favorites', [
            'user_id' => $userId,
            'song_id' => $songId,
        ]);
    }

    /**
     * Hapus lagu dari favorit.
     *
     * @param int $userId
     * @param int $songId
     * @return bool  true jika ada baris yang terhapus
     */
    public function remove($userId, $songId)
    {
        $this->db->where('user_id', $userId)
                 ->where('song_id', $songId)
                 ->delete('favorites');
        return $this->db->affected_rows() > 0;
    }

    /**
     * Hitung total favorit milik user.
     *
     * @param int $userId
     * @return int
     */
    public function count_by_user($userId)
    {
        return $this->db->where('user_id', $userId)->count_all_results('favorites');
    }
}
