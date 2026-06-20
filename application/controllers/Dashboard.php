<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Dashboard extends CI_Controller {

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

            // Filter: ensure all songs actually exist and are active
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

            // Fresh catalog preview for registered view too
            $data['reg_preview_songs'] = $this->Song_model->get_paginated(1, 8);

            $data['main_view'] = 'dashboard/registered_full';
        } else {
            $data['main_view'] = 'dashboard/main';
        }

        $data['title'] = 'Laufey — Music Player & Downloader';
        $this->load->view('templates/layout', $data);
    }

    /**
     * Return Continue Listening cards HTML (for JS auto-refresh).
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

        // Move or add currently playing song to the front
        if ($currentSongId > 0) {
            $moved = false;
            foreach ($recentClean as $i => $s) {
                if ((int) $s->id === $currentSongId) {
                    // Found in list — move to front
                    $item = array_splice($recentClean, $i, 1);
                    array_unshift($recentClean, $item[0]);
                    $moved = true;
                    break;
                }
            }
            if (!$moved) {
                // Not in list — fetch from DB and prepend
                $currentSong = $this->Song_model->get_by_id($currentSongId);
                if ($currentSong && $currentSong->is_active && $currentSong->file_path) {
                    array_unshift($recentClean, $currentSong);
                }
            }
        }

        // Ensure max 6 items
        $recentClean = array_slice($recentClean, 0, 6);

        if (empty($recentClean)) { return; }

        $data['recent_listens'] = $recentClean;
        $this->load->view('dashboard/_continue_listening', $data);
    }
}
