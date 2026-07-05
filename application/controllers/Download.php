<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Download extends CI_Controller {

    public function __construct()
    {
        parent::__construct();
        $this->load->model('Song_model');
        $this->load->model('Download_logs_model');
    }

    /**
     * Serve a file for download.
     *
     * Guest (not logged in): max 1 download/day per IP.
     * Registered user: unlimited.
     * Admin: unlimited.
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

        $userId = (int) $this->session->userdata('user_id');
        $isGuest = ($userId <= 0);

        // Enforce guest limit: 1 download per IP per day
        if ($isGuest) {
            $ip = $this->input->ip_address();
            $today = date('Y-m-d');
            $count = $this->Download_logs_model->count_by_ip_date($ip, $today);
            if ($count >= 1) {
                $this->session->set_flashdata('dl_error', 'Guest download limit reached: 1 download per day. Create a free account for unlimited downloads.');
                redirect('song/' . $songId);
                return;
            }
        }

        // Resolve absolute file path
        $filePath = FCPATH . $song->file_path;
        if (!file_exists($filePath)) {
            log_message('error', 'Download file not found: ' . $filePath);
            show_404();
            return;
        }

        // Log the download
        $clientIp = $isGuest ? $this->input->ip_address() : '';
        $this->Download_logs_model->log_download(
            $isGuest ? null : $userId,
            $clientIp,
            $songId
        );

        // Serve the file via force_download using absolute server path
        $this->load->helper('download');
        force_download($filePath, null);
    }

    /**
     * Download page — show songs with download links.
     */
    public function page()
    {
        $this->load->model('Song_model');
        $page = max(1, (int) $this->input->get('page'));
        $search = $this->input->get('q', TRUE);
        $perPage = 24;

        if (!empty($search)) {
            $songs = $this->Song_model->search($search, $page, $perPage);
            $total = $this->Song_model->count_search($search);
        } else {
            $songs = $this->Song_model->get_paginated($page, $perPage);
            $total = $this->Song_model->count_all();
        }

        $data = [
            'songs'        => $songs,
            'total_songs'  => $total,
            'current_page' => $page,
            'total_pages'  => max(1, ceil($total / $perPage)),
            'search_query' => $search,
            'title'        => 'Download — Laufey',
            'main_view'    => 'download/page',
        ];

        $this->load->view('templates/layout', $data);
    }
}
