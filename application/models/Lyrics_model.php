<?php
defined('BASEPATH') OR exit('no direct script access allowed');

/**
 * Model Lyrics — mengelola lirik lagu di tabel `lyrics`.
 *
 * Menyediakan akses baca lirik dari database lokal dan
 * menyimpan (cache) hasil fetch dari API eksternal.
 */
class Lyrics_model extends CI_Model {

    /**
     * Ambil lirik untuk sebuah lagu dari database lokal.
     *
     * @param int $songId
     * @return object|null  Object dengan properti 'content' dan 'format'
     */
    public function get_by_song($songId)
    {
        $this->db->select('content, format');
        $this->db->from('lyrics');
        $this->db->where('song_id', $songId);
        return $this->db->get()->row();
    }

    /**
     * Simpan lirik ke database lokal (cache dari API eksternal).
     *
     * Gunakan REPLACE agar data lama terganti otomatis tanpa
     * perlu cek eksistensi terlebih dahulu.
     *
     * @param int    $songId
     * @param string $content  Teks lirik
     * @param string $format   'plain' atau 'lrc'
     * @return bool
     */
    public function cache($songId, $content, $format)
    {
        return $this->db->replace('lyrics', [
            'song_id' => $songId,
            'content' => $content,
            'format'  => $format,
        ]);
    }
}
