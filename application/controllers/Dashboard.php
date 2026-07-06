<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Controller Dashboard — halaman utama aplikasi.
 *
 * Menampilkan konten berbeda untuk guest (halaman statis)
 * dan user terdaftar (rekomendasi, trending, continue listening, dll).
 * Juga menyediakan endpoint AJAX untuk refresh Continue Listening.
 */
class Dashboard extends CI_Controller {

    /**
     * Halaman utama — pilih tampilan berdasarkan status login.
     */
    public function index()
    {
        $this->load->model('Song_model');
        $data['preview_songs'] = $this->Song_model->get_paginated(1, 8);

        $userId = (int) $this->session->userdata('user_id');
        $data['is_logged_in'] = $userId > 0;

        if ($userId > 0) {
            $this->load->model('Listen_history_model');
            $this->load->model('Playlist_model');
            $this->load->model('Favorites_model');

            // Filter: pastikan semua lagu benar-benar exist dan aktif
            $recentRaw = $this->Listen_history_model->get_recent($userId, 20);
            $recentClean = [];
            foreach ($recentRaw as $s) {
                if ($s->id && $s->is_active && $s->file_path && count($recentClean) < 6) {
                    $recentClean[] = $s;
                }
            }
            $data['recent_listens']  = $recentClean;
            $data['playlists']       = $this->Playlist_model->get_by_user($userId);
            $data['favorites']       = $this->Favorites_model->get_by_user($userId);
            $data['trending']        = $this->Listen_history_model->get_trending(8);
            $data['recommendations'] = $this->Listen_history_model->get_recommendations($userId, 8);
            $this->load->model('User_model');
            $data['dashboard_user']  = $this->User_model->get_by_id($userId);

            // Preview fresh untuk registered view juga
            $data['reg_preview_songs'] = $this->Song_model->get_paginated(1, 8);

            $data['main_view'] = 'dashboard/registered_full';
        } else {
            $data['main_view'] = 'dashboard/main';
        }

        $data['title'] = 'Laufey — Music Player & Downloader';
        $this->load->view('templates/layout', $data);
    }

    /**
     * Return HTML kartu Continue Listening (untuk auto-refresh JS).
     *
     * Memindahkan lagu yang sedang diputar ke posisi pertama.
     * Menerima parameter current_id untuk identifikasi lagu aktif.
     */
    public function continue_listening()
    {
        $userId = (int) $this->session->userdata('user_id');
        if ($userId <= 0) { return; }

        $currentSongId = (int) $this->input->get('current_id', true);

        $this->load->model('Listen_history_model');
        $this->load->model('Song_model');
        $recentRaw = $this->Listen_history_model->get_recent($userId, 20);
        $recentClean = [];
        foreach ($recentRaw as $s) {
            if ($s->id && $s->is_active && $s->file_path && count($recentClean) < 6) {
                $recentClean[] = $s;
            }
        }

        // Pindahkan atau tambahkan lagu yang sedang diputar ke posisi depan
        if ($currentSongId > 0) {
            $moved = false;
            foreach ($recentClean as $i => $s) {
                if ((int) $s->id === $currentSongId) {
                    // Lagu ada di daftar — pindahkan ke depan
                    $item = array_splice($recentClean, $i, 1);
                    array_unshift($recentClean, $item[0]);
                    $moved = true;
                    break;
                }
            }
            if (!$moved) {
                // Lagu tidak ada di daftar — ambil dari DB lalu tambahkan di depan
                $currentSong = $this->Song_model->get_by_id($currentSongId);
                if ($currentSong && $currentSong->is_active && $currentSong->file_path) {
                    array_unshift($recentClean, $currentSong);
                }
            }
        }

        // Pastikan maksimal 6 item
        $recentClean = array_slice($recentClean, 0, 6);

        if (empty($recentClean)) { return; }

        $data['recent_listens'] = $recentClean;
        $this->load->view('dashboard/_continue_listening', $data);
    }
}
