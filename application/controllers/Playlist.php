<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Controller Playlist — mengelola CRUD playlist dan lagu di dalamnya.
 *
 * Menyediakan halaman daftar playlist, detail playlist dengan lagu,
 * pembuatan playlist baru, edit (nama, deskripsi, cover, banner),
 * serta endpoint AJAX untuk manajemen cepat dari player.
 */
class Playlist extends CI_Controller {

    public function __construct()
    {
        parent::__construct();
        $this->load->model('Playlist_model');
        $this->load->model('Song_model');
    }

    /**
     * Tampilkan daftar semua playlist milik user.
     */
    public function index()
    {
        $userId = (int) $this->session->userdata('user_id');

        if ($userId <= 0) {
            redirect('login');
            return;
        }

        $data['playlists'] = $this->Playlist_model->get_by_user($userId);
        $data['title']     = 'My Playlists — Laufey';
        $data['main_view'] = 'playlist/index';

        $this->load->view('templates/layout', $data);
    }

    /**
     * Lihat detail playlist beserta daftar lagu di dalamnya.
     *
     * Menghitung total durasi seluruh lagu untuk ditampilkan.
     *
     * @param int $playlistId
     */
    public function view($playlistId = NULL)
    {
        $userId = (int) $this->session->userdata('user_id');

        if ($userId <= 0) {
            redirect('login');
            return;
        }

        $playlistId = (int) $playlistId;
        $playlist   = $this->Playlist_model->get_with_songs($playlistId, $userId);

        if (!$playlist) {
            show_404();
            return;
        }

        // Hitung total durasi dari semua lagu
        $totalSeconds = 0;
        if (!empty($playlist->songs)) {
            foreach ($playlist->songs as $s) {
                $totalSeconds += (int) $s->duration_seconds;
            }
        }
        $data['total_duration'] = $totalSeconds;

        $data['playlist'] = $playlist;
        $data['title']    = html_escape($playlist->name) . ' — Laufey';
        $data['main_view'] = 'playlist/detail';

        $this->load->view('templates/layout', $data);
    }

    /**
     * Halaman pembuatan playlist baru.
     *
     * Validasi input: nama (required), deskripsi (opsional),
     * dan status publik. Redirect ke halaman playlist setelah sukses.
     */
    public function create()
    {
        $userId = (int) $this->session->userdata('user_id');

        if ($userId <= 0) {
            redirect('login');
            return;
        }

        $this->load->library('form_validation');
        $this->form_validation->set_rules('name', 'Playlist Name', 'required|trim|max_length[100]');
        $this->form_validation->set_rules('description', 'Description', 'max_length[500]');
        $this->form_validation->set_rules('is_public', 'Public', 'in_list[0,1]');

        if ($this->form_validation->run() === FALSE) {
            $data['title']     = 'Create Playlist — Laufey';
            $data['main_view'] = 'playlist/create';
            $this->load->view('templates/layout', $data);
            return;
        }

        $playlistId = $this->Playlist_model->create($userId, [
            'title'       => $this->input->post('name', TRUE),
            'description' => $this->input->post('description', TRUE),
            'is_public'   => $this->input->post('is_public'),
        ]);

        redirect('playlist/' . $playlistId);
    }

    /**
     * Hapus playlist milik user.
     *
     * @param int $playlistId
     */
    public function delete($playlistId = NULL)
    {
        $userId = (int) $this->session->userdata('user_id');

        if ($userId <= 0) {
            redirect('login');
            return;
        }

        $this->Playlist_model->delete((int) $playlistId, $userId);
        redirect('playlist');
    }

    /**
     * Tambah lagu ke playlist (form POST biasa).
     *
     * @param int $playlistId
     */
    public function add_song($playlistId = NULL)
    {
        $userId = (int) $this->session->userdata('user_id');

        if ($userId <= 0) {
            redirect('login');
            return;
        }

        $songId = (int) $this->input->post('song_id');
        $this->Playlist_model->add_song((int) $playlistId, $songId);
        redirect('playlist/' . $playlistId);
    }

    /**
     * AJAX: Tambah lagu ke playlist — auto-buat playlist jika nama belum ada.
     *
     * Menerima song_id dan nama playlist dari POST.
     * Jika playlist dengan nama tersebut belum ada, buat baru.
     */
    public function add_song_ajax()
    {
        $userId = (int) $this->session->userdata('user_id');
        if ($userId <= 0) {
            $this->output->set_status_header(403)->set_output(json_encode(['error' => 'Please login']));
            return;
        }

        $songId = (int) $this->input->post('song_id');
        $name   = $this->input->post('name', TRUE);

        if ($songId <= 0 || empty($name)) {
            $this->output->set_status_header(400)->set_output(json_encode(['error' => 'Invalid params']));
            return;
        }

        // Cari playlist yang sudah ada dengan nama tersebut
        $existing = $this->db->where('user_id', $userId)
                              ->where('name', $name)
                              ->get('playlists')
                              ->row();

        if ($existing) {
            $playlistId = (int) $existing->id;
        } else {
            // Buat playlist baru jika belum ada
            $playlistId = $this->Playlist_model->create($userId, [
                'title'       => $name,
                'description' => '',
                'is_public'   => 0,
            ]);
        }

        $this->Playlist_model->add_song($playlistId, $songId);
        $this->output->set_content_type('application/json')
                     ->set_output(json_encode(['success' => true, 'playlist_id' => $playlistId]));
    }

    /**
     * AJAX: Ambil daftar playlist user sebagai JSON.
     *
     * Digunakan oleh dropdown "Add to Playlist" di player.
     */
    public function get_playlists_json()
    {
        $userId = (int) $this->session->userdata('user_id');
        if ($userId <= 0) {
            $this->output->set_status_header(403)
                         ->set_content_type('application/json')
                         ->set_output(json_encode(['error' => 'Please login']));
            return;
        }

        $playlists = $this->Playlist_model->get_by_user($userId);
        $this->output->set_content_type('application/json')
                     ->set_output(json_encode($playlists));
    }

    /**
     * Hapus lagu dari playlist (form POST biasa).
     *
     * @param int $playlistId
     */
    public function remove_song($playlistId = NULL)
    {
        $userId = (int) $this->session->userdata('user_id');
        if ($userId <= 0) { redirect('login'); return; }
        $songId = (int) $this->input->post('song_id');
        $this->Playlist_model->remove_song((int) $playlistId, $songId);
        redirect('playlist/' . $playlistId);
    }

    /**
     * Edit playlist — nama, deskripsi, cover, dan banner.
     *
     * Mendukung unggah file cover/banner serta penghapusan
     * cover/banner yang sudah ada.
     *
     * @param int $playlistId
     */
    public function edit($playlistId = NULL)
    {
        $userId = (int) $this->session->userdata('user_id');
        if ($userId <= 0) { redirect('login'); return; }

        $playlistId = (int) $playlistId;
        $playlist = $this->Playlist_model->get_with_songs($playlistId, $userId);
        if (!$playlist) { show_404(); return; }

        $this->load->library('form_validation');

        if ($this->input->method() === 'post') {
            $this->form_validation->set_rules('name', 'Playlist Name', 'required|trim|max_length[100]');
            $this->form_validation->set_rules('description', 'Description', 'max_length[500]');

            if ($this->form_validation->run()) {
                $update = [
                    'name'        => $this->input->post('name', TRUE),
                    'description' => $this->input->post('description', TRUE),
                ];

                // Upload cover image
                if (!empty($_FILES['cover_file']['name'])) {
                    $this->load->library('upload');
                    $dir = 'protected_uploads/covers/';
                    $fullPath = FCPATH . $dir;
                    if (!is_dir($fullPath)) mkdir($fullPath, 0755, TRUE);
                    $config = [
                        'upload_path'   => $fullPath,
                        'allowed_types' => 'jpg|jpeg|png|webp|gif',
                        'max_size'      => 2048,
                        'file_name'     => 'pl_' . $playlistId . '_' . time(),
                    ];
                    $this->upload->initialize($config);
                    if ($this->upload->do_upload('cover_file')) {
                        $u = $this->upload->data();
                        $update['cover_path'] = $dir . $u['file_name'];
                    }
                }

                // Upload banner image
                if (!empty($_FILES['banner_file']['name'])) {
                    $this->load->library('upload');
                    $dir = 'protected_uploads/covers/';
                    $fullPath = FCPATH . $dir;
                    if (!is_dir($fullPath)) mkdir($fullPath, 0755, TRUE);
                    $config = [
                        'upload_path'   => $fullPath,
                        'allowed_types' => 'jpg|jpeg|png|webp|gif',
                        'max_size'      => 4096,
                        'file_name'     => 'pl_banner_' . $playlistId . '_' . time(),
                    ];
                    $this->upload->initialize($config);
                    if ($this->upload->do_upload('banner_file')) {
                        $u = $this->upload->data();
                        $update['banner_path'] = $dir . $u['file_name'];
                    }
                }

                // Hapus cover/banner jika diminta
                if ($this->input->post('remove_cover')) {
                    $update['cover_path'] = NULL;
                }

                if ($this->input->post('remove_banner')) {
                    $update['banner_path'] = NULL;
                }

                $this->db->where('id', $playlistId)->update('playlists', $update);
                $this->session->set_flashdata('pl_success', 'Playlist updated!');
                redirect('playlist/' . $playlistId);
            }
        }

        $data['playlist']  = $playlist;
        $data['title']     = 'Edit Playlist — Laufey';
        $data['main_view'] = 'playlist/edit';
        $this->load->view('templates/layout', $data);
    }
}
