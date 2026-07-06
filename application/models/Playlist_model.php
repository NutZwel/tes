<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Model Playlist — mengelola playlist dan relasi lagu di dalamnya.
 *
 * Mencakup CRUD playlist milik user tertentu, manajemen lagu
 * dalam playlist (tambah/hapus), serta pencarian playlist publik.
 */
class Playlist_model extends CI_Model {

    /**
     * Ambil semua playlist milik user, lengkap dengan jumlah lagu.
     *
     * Subquery COUNT dihitungan langsung agar tidak perlu
     * looping terpisah untuk setiap playlist.
     *
     * @param int $userId
     * @return array
     */
    public function get_by_user($userId)
    {
        $this->db->select('
            playlists.*,
            (SELECT COUNT(*) FROM playlist_songs WHERE playlist_songs.playlist_id = playlists.id) AS song_count
        ');
        $this->db->where('playlists.user_id', $userId);
        $this->db->order_by('playlists.updated_at', 'DESC');
        return $this->db->get('playlists')->result();
    }

    /**
     * Ambil satu playlist beserta daftar lagu di dalamnya.
     *
     * Query dua tahap: ambil data playlist dulu (sekaligus cek kepemilikan),
     * baru JOIN ke tabel playlist_songs.
     *
     * @param int $playlistId
     * @param int $userId      Untuk verifikasi kepemilikan
     * @return object|null
     */
    public function get_with_songs($playlistId, $userId)
    {
        $playlist = $this->db->where('id', $playlistId)
                             ->where('user_id', $userId)
                             ->get('playlists')
                             ->row();
        if (!$playlist) return null;

        $this->db->select('
            songs.id, songs.title, songs.artist, songs.duration_seconds,
            songs.cover_path, genres.name AS genre_name
        ');
        $this->db->from('playlist_songs');
        $this->db->join('songs', 'songs.id = playlist_songs.song_id');
        $this->db->join('genres', 'genres.id = songs.genre_id', 'left');
        $this->db->where('playlist_songs.playlist_id', $playlistId);
        $this->db->where('songs.is_active', 1);
        // Urutkan berdasarkan posisi yang disimpan user
        $this->db->order_by('playlist_songs.position', 'ASC');
        $playlist->songs = $this->db->get()->result();

        return $playlist;
    }

    /**
     * Buat playlist baru untuk seorang user.
     *
     * @param int   $userId
     * @param array $data    Keys: title, description, is_public
     * @return int           ID playlist yang baru dibuat
     */
    public function create($userId, array $data)
    {
        $insert = [
            'user_id'     => $userId,
            'name'        => $data['title'],   // maps ke kolom 'name' di skema
            'description' => $data['description'] ?? '',
            'is_public'   => !empty($data['is_public']) ? 1 : 0,
        ];
        $this->db->insert('playlists', $insert);
        return (int) $this->db->insert_id();
    }

    /**
     * Tambah lagu ke dalam playlist.
     *
     * Jika lagu sudah ada, lewati (return true) agar tidak terjadi duplikasi.
     *
     * @param int $playlistId
     * @param int $songId
     * @param int $position  Posisi urutan (opsional)
     * @return bool
     */
    public function add_song($playlistId, $songId, $position = 0)
    {
        // Cegah duplikasi: cek apakah lagu sudah ada di playlist
        $exists = $this->db->where('playlist_id', $playlistId)
                           ->where('song_id', $songId)
                           ->get('playlist_songs')
                           ->row();
        if ($exists) return true; // sudah ada, tidak perlu diinsert lagi

        return $this->db->insert('playlist_songs', [
            'playlist_id' => $playlistId,
            'song_id'     => $songId,
            'position'    => $position,
        ]);
    }

    /**
     * Hapus lagu dari playlist.
     *
     * @param int $playlistId
     * @param int $songId
     * @return bool  true jika ada baris yang terhapus
     */
    public function remove_song($playlistId, $songId)
    {
        $this->db->where('playlist_id', $playlistId)
                 ->where('song_id', $songId)
                 ->delete('playlist_songs');
        return $this->db->affected_rows() > 0;
    }

    /**
     * Hitung jumlah playlist milik user.
     *
     * @param int $userId
     * @return int
     */
    public function count_by_user($userId)
    {
        return $this->db->where('user_id', $userId)->count_all_results('playlists');
    }

    /**
     * Hapus playlist (hanya jika user adalah pemiliknya).
     *
     * @param int $playlistId
     * @param int $userId
     * @return bool
     */
    public function delete($playlistId, $userId)
    {
        $this->db->where('id', $playlistId)->where('user_id', $userId)->delete('playlists');
        return $this->db->affected_rows() > 0;
    }

    /**
     * Cari playlist publik berdasarkan nama (maks 10 hasil).
     *
     * @param string $query
     * @return array
     */
    public function search_public($query)
    {
        $like = '%' . $this->db->escape_like_str($query) . '%';
        $this->db->select('id, name, description, cover_path');
        $this->db->from('playlists');
        $this->db->where('is_public', 1);
        $this->db->like('name', $query);
        $this->db->limit(10);
        return $this->db->get()->result();
    }
}
