<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Controller Download — melayani unduhan lagu dengan pembatasan akses.
 *
 * Menerapkan kebijakan:
 * - Guest: maksimal 1 unduhan per hari per alamat IP
 * - User terdaftar: tanpa batas
 * - Admin: tanpa batas
 *
 * File dikirim melalui force_download() dengan path absolut dari database,
 * bukan URL langsung (mencegah akses file tanpa otorisasi).
 */
class Download extends CI_Controller {

    public function __construct()
    {
        parent::__construct();
        $this->load->model('Song_model');
        $this->load->model('Download_logs_model');
    }

    /**
     * Proses unduhan file lagu berdasarkan ID.
     *
     * Memeriksa otorisasi (guest vs terdaftar), mencatat log,
     * lalu mengirim file ke browser via force_download().
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

        $userId = (int) $this->session->userdata('user_id');
        $isGuest = ($userId <= 0);

        // Batasi guest: hanya 1 unduhan per IP per hari
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

        // Resolve path absolut ke file audio
        $filePath = FCPATH . $song->file_path;
        if (!file_exists($filePath)) {
            log_message('error', 'Download file not found: ' . $filePath);
            show_404();
            return;
        }

        // Catat aktivitas download
        $clientIp = $isGuest ? $this->input->ip_address() : '';
        $this->Download_logs_model->log_download(
            $isGuest ? null : $userId,
            $clientIp,
            $songId
        );

        // Kirim file ke browser menggunakan path server absolut
        $this->load->helper('download');
        force_download($filePath, null);
    }

    /**
     * Halaman unduhan — menampilkan daftar lagu yang bisa di-download.
     *
     * Sama seperti katalog tetapi dengan tombol download dan
     * info pembatasan untuk guest.
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
