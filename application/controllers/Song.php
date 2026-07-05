<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Song extends CI_Controller {

    public function __construct()
    {
        parent::__construct();
        $this->load->model('Song_model');
        $this->load->model('Listen_history_model');
    }

    /**
     * Show song detail page.
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

        // Get lyrics if any
        $this->db->where('song_id', $songId);
        $lyrics = $this->db->get('lyrics')->row();

        // Get similar songs (same genre)
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
