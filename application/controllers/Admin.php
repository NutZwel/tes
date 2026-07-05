<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Admin extends CI_Controller {

    public function __construct()
    {
        parent::__construct();
        $this->load->model('User_model');
        $this->load->model('Song_model');
        $this->load->library(['form_validation', 'upload']);
    }

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
     * Admin dashboard — user list + stats.
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
     * Admin — Add song page.
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
                // Handle new genre
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

                // Upload audio
                if (!empty($_FILES['audio_file']['name'])) {
                    $audioPath = $this->_upload_file('audio_file', 'protected_uploads/audio/');
                    if ($audioPath) $insert['file_path'] = $audioPath;
                }

                // Upload cover
                if (!empty($_FILES['cover_file']['name'])) {
                    $coverPath = $this->_upload_file('cover_file', 'protected_uploads/covers/');
                    if ($coverPath) $insert['cover_path'] = $coverPath;
                }

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
     * Admin — manage songs list.
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
     * Admin — delete song.
     */
    public function delete_song($id = 0)
    {
        $this->_require_admin();
        $song = $this->Song_model->get_by_id((int) $id, false); // include inactive too
        if (!$song) {
            show_404();
            return;
        }

        // Delete local audio file if it exists
        $filePath = $song->file_path;
        if (!empty($filePath) && stripos($filePath, 'http') !== 0) {
            $fullPath = FCPATH . $filePath;
            if (file_exists($fullPath)) {
                @unlink($fullPath);
            }
        }

        // Hard delete — cascades to lyrics, favorites, listen_history, playlist_songs
        $this->db->where('id', (int) $id)->delete('songs');
        $this->session->set_flashdata('success', 'Song deleted permanently!');
        redirect('admin/songs');
    }

    /**
     * Admin — toggle user active status.
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
     * Admin — edit song page.
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

                if (!empty($_FILES['audio_file']['name'])) {
                    $audioPath = $this->_upload_file('audio_file', 'protected_uploads/audio/');
                    if ($audioPath) $update['file_path'] = $audioPath;
                }
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

    // ── Helpers ──

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
