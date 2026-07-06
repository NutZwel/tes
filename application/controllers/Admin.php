<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Controller Admin — panel manajemen untuk administrator.
 *
 * Mencakup CRUD lagu (tambah/edit/hapus), manajemen user
 * (aktivasi/non-aktivasi), dan dashboard statistik.
 * Semua method dilindungi oleh _require_admin().
 */
class Admin extends CI_Controller {

    public function __construct()
    {
        parent::__construct();
        $this->load->model('User_model');
        $this->load->model('Song_model');
        $this->load->library(['form_validation', 'upload']);
    }

    /**
     * Periksa apakah user saat ini adalah admin.
     * Redirect ke login jika bukan.
     */
    private function _require_admin()
    {
        $role = $this->session->userdata('role');
        $userId = (int) $this->session->userdata('user_id');
        if ($userId <= 0 || $role !== 'admin') {
            redirect('login');
            exit;
        }
    }

    /**
     * Dashboard admin — menampilkan daftar user, statistik,
     * dan informasi session aktif.
     */
    public function index()
    {
        $this->_require_admin();

        $data['users']      = $this->User_model->get_all();
        $data['active_ids'] = $this->User_model->get_active_ids();
        $data['total_users'] = count($data['users']);
        $data['active_users'] = count($data['active_ids']);
        $data['total_songs'] = $this->Song_model->count_all();
        $data['admin_user']  = $this->User_model->get_by_id($this->session->userdata('user_id'));

        $data['title']     = 'Admin Dashboard — Laufey';
        $data['main_view'] = 'admin/index';
        $this->load->view('templates/layout', $data);
    }

    /**
     * Halaman tambah lagu baru.
     *
     * Mendukung unggah file audio dan cover, serta pembuatan
     * genre baru secara inline. File audio wajib diisi.
     */
    public function add_song()
    {
        $this->_require_admin();

        $data['genres'] = $this->db->get('genres')->result();

        if ($this->input->method() === 'post') {
            $this->form_validation->set_rules('title', 'Title', 'required|trim|max_length[100]');
            $this->form_validation->set_rules('artist', 'Artist', 'required|trim|max_length[100]');
            $this->form_validation->set_rules('genre_id', 'Genre', 'integer');
            $this->form_validation->set_rules('duration_seconds', 'Duration', 'integer');
            $this->form_validation->set_rules('new_genre', 'New Genre', 'trim|max_length[50]|is_unique[genres.name]');

            if ($this->form_validation->run()) {
                // Jika ada genre baru yang diisi, buat dulu
                $genreId = (int) $this->input->post('genre_id') ?: NULL;
                $newGenre = $this->input->post('new_genre', TRUE);
                if (!empty($newGenre)) {
                    $this->db->insert('genres', ['name' => $newGenre]);
                    $genreId = (int) $this->db->insert_id();
                }

                $insert = [
                    'title'            => $this->input->post('title', TRUE),
                    'artist'           => $this->input->post('artist', TRUE),
                    'genre_id'         => $genreId,
                    'duration_seconds' => (int) $this->input->post('duration_seconds') ?: NULL,
                    'description'      => $this->input->post('description', TRUE),
                    'artist_bio'       => $this->input->post('artist_bio', TRUE),
                    'file_path'        => '',
                ];

                // Proses upload file audio
                if (!empty($_FILES['audio_file']['name'])) {
                    $audioPath = $this->_upload_file('audio_file', 'protected_uploads/audio/');
                    if ($audioPath) $insert['file_path'] = $audioPath;
                }

                // Proses upload cover
                if (!empty($_FILES['cover_file']['name'])) {
                    $coverPath = $this->_upload_file('cover_file', 'protected_uploads/covers/');
                    if ($coverPath) $insert['cover_path'] = $coverPath;
                }

                // File audio wajib ada
                if (!empty($insert['file_path'])) {
                    $this->db->insert('songs', $insert);
                    $this->session->set_flashdata('success', 'Song added successfully!');
                    redirect('admin/add_song');
                } else {
                    $data['error'] = 'Audio file is required.';
                }
            }
        }

        $data['title']     = 'Add Song — Admin';
        $data['main_view'] = 'admin/add_song';
        $this->load->view('templates/layout', $data);
    }

    /**
     * Halaman daftar lagu (untuk dikelola admin).
     *
     * Menampilkan semua lagu termasuk yang tidak aktif.
     */
    public function songs()
    {
        $this->_require_admin();
        $this->db->select('songs.*, genres.name AS genre_name');
        $this->db->from('songs');
        $this->db->join('genres', 'genres.id = songs.genre_id', 'left');
        $this->db->order_by('songs.created_at', 'DESC');
        $data['songs'] = $this->db->get()->result();

        $data['title']     = 'Manage Songs — Admin';
        $data['main_view'] = 'admin/songs';
        $this->load->view('templates/layout', $data);
    }

    /**
     * Hapus lagu secara permanen (hard delete).
     *
     * Juga menghapus file audio lokal jika ada (bukan URL remote).
     * Data terkait (lyrics, favorites, dll) terhapus via kaskade FK.
     *
     * @param int $id
     */
    public function delete_song($id = 0)
    {
        $this->_require_admin();
        $song = $this->Song_model->get_by_id((int) $id, false); // include inactive juga
        if (!$song) {
            show_404();
            return;
        }

        // Hapus file audio lokal jika ada
        $filePath = $song->file_path;
        if (!empty($filePath) && stripos($filePath, 'http') !== 0) {
            $fullPath = FCPATH . $filePath;
            if (file_exists($fullPath)) {
                @unlink($fullPath);
            }
        }

        // Hard delete — kaskade ke lyrics, favorites, listen_history, playlist_songs
        $this->db->where('id', (int) $id)->delete('songs');
        $this->session->set_flashdata('success', 'Song deleted permanently!');
        redirect('admin/songs');
    }

    /**
     * Aktifkan/non-aktifkan user (toggle).
     *
     * @param int $id
     */
    public function toggle_user($id = 0)
    {
        $this->_require_admin();
        $user = $this->User_model->get_by_id((int) $id, false);
        if ($user) {
            $newStatus = $user->is_active ? 0 : 1;
            $this->db->where('id', (int) $id)->update('users', ['is_active' => $newStatus]);
        }
        redirect('admin');
    }

    /**
     * Halaman edit lagu — mengubah metadata dan/atau file.
     *
     * @param int $id
     */
    public function edit_song($id = 0)
    {
        $this->_require_admin();
        $this->load->model('Song_model');

        $song = $this->Song_model->get_by_id((int) $id);
        if (!$song) {
            show_404();
            return;
        }

        $data['song']   = $song;
        $data['genres'] = $this->db->get('genres')->result();

        if ($this->input->method() === 'post') {
            $this->form_validation->set_rules('title', 'Title', 'required|trim|max_length[100]');
            $this->form_validation->set_rules('artist', 'Artist', 'required|trim|max_length[100]');
            $this->form_validation->set_rules('genre_id', 'Genre', 'integer');
            $this->form_validation->set_rules('duration_seconds', 'Duration', 'integer');

            if ($this->form_validation->run()) {
                $update = [
                    'title'            => $this->input->post('title', TRUE),
                    'artist'           => $this->input->post('artist', TRUE),
                    'genre_id'         => (int) $this->input->post('genre_id') ?: NULL,
                    'duration_seconds' => (int) $this->input->post('duration_seconds') ?: NULL,
                    'description'      => $this->input->post('description', TRUE),
                    'artist_bio'       => $this->input->post('artist_bio', TRUE),
                ];

                // Upload file audio baru jika diganti
                if (!empty($_FILES['audio_file']['name'])) {
                    $audioPath = $this->_upload_file('audio_file', 'protected_uploads/audio/');
                    if ($audioPath) $update['file_path'] = $audioPath;
                }
                // Upload cover baru jika diganti
                if (!empty($_FILES['cover_file']['name'])) {
                    $coverPath = $this->_upload_file('cover_file', 'protected_uploads/covers/');
                    if ($coverPath) $update['cover_path'] = $coverPath;
                }

                $this->db->where('id', $song->id)->update('songs', $update);
                $this->session->set_flashdata('success', 'Song updated!');
                redirect('admin/songs');
            }
        }

        $data['title']     = 'Edit Song — Admin';
        $data['main_view'] = 'admin/edit_song';
        $this->load->view('templates/layout', $data);
    }

    // ── Helper ──

    /**
     * Upload file ke direktori yang ditentukan.
     *
     * Membuat direktori jika belum ada, lalu mengembalikan
     * path relatif dari file yang berhasil diupload.
     *
     * @param string $field Nama field di form
     * @param string $dir   Direktori tujuan (relatif terhadap FCPATH)
     * @return string|null  Path relatif file, atau null jika gagal
     */
    private function _upload_file($field, $dir)
    {
        $fullPath = FCPATH . $dir;
        if (!is_dir($fullPath)) {
            mkdir($fullPath, 0755, TRUE);
        }

        $config = [
            'upload_path'   => $fullPath,
            'allowed_types' => 'mp3|wav|ogg|flac|aac|jpg|jpeg|png|webp|gif',
            'max_size'      => 50000,
            'file_name'     => $field . '_' . time() . '_' . mt_rand(100, 999),
        ];

        $this->upload->initialize($config);

        if ($this->upload->do_upload($field)) {
            $data = $this->upload->data();
            return $dir . $data['file_name'];
        }

        log_message('error', 'Upload failed for ' . $field . ': ' . $this->upload->display_errors('', ''));
        return NULL;
    }
}
