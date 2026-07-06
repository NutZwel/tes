<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Controller Song — halaman detail lagu.
 *
 * Menampilkan informasi lagu, lirik (jika ada), dan lagu
 * serupa dari genre yang sama.
 */
class Song extends CI_Controller {

    public function __construct()
    {
        parent::__construct();
        $this->load->model('Song_model');
        $this->load->model('Listen_history_model');
    }

    /**
     * Tampilkan halaman detail lagu.
     *
     * Menampilkan metadata lagu, lirik dari database,
     * serta daftar lagu serupa (genre sama, maks 6).
     *
     * @param int $songId
     */
    public function index($songId = 0)
    {
        $songId = (int) $songId;
        if ($songId <= 0) {
            show_404();
            return;
        }

        $song = $this->Song_model->get_by_id($songId);
        if (!$song || !$song->is_active) {
            show_404();
            return;
        }

        // Ambil lirik dari database lokal
        $this->db->where('song_id', $songId);
        $lyrics = $this->db->get('lyrics')->row();

        // Cari lagu serupa dari genre yang sama
        $similar = [];
        if ($song->genre_id) {
            $this->db->select('id, title, artist, duration_seconds, cover_path');
            $this->db->from('songs');
            $this->db->where('genre_id', $song->genre_id);
            $this->db->where('id !=', $song->id);
            $this->db->where('is_active', 1);
            $this->db->order_by('created_at', 'DESC');
            $this->db->limit(6);
            $similar = $this->db->get()->result();
        }

        $data['song']    = $song;
        $data['lyrics']  = $lyrics ? $lyrics->content : null;
        $data['similar'] = $similar;
        $data['title']   = html_escape($song->title) . ' — ' . html_escape($song->artist) . ' — Laufey';
        $data['main_view'] = 'song/detail';

        $this->load->view('templates/layout', $data);
    }
}
