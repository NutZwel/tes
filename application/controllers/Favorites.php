<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Favorites extends CI_Controller {

    public function __construct()
    {
        parent::__construct();
        $this->load->model('Favorites_model');
        $this->load->model('Song_model');
    }

    /**
     * Show all favorited songs for the logged-in user.
     */
    public function index()
    {
        $userId = (int) $this->session->userdata('user_id');
        if ($userId <= 0) {
            redirect('login');
            return;
        }

        $data['favorites'] = $this->Favorites_model->get_by_user($userId, 200);
        $data['title']     = 'My Favorites — Laufey';
        $data['main_view'] = 'favorites/index';
        $this->load->view('templates/layout', $data);
    }

    /**
     * Add a song to favorites via AJAX.
     */
    public function add()
    {
        $userId = (int) $this->session->userdata('user_id');
        if ($userId <= 0) {
            $this->output->set_status_header(403)->set_output(json_encode(['error' => 'Please login first']));
            return;
        }

        $songId = (int) $this->input->post('song_id');
        if ($songId <= 0) {
            $this->output->set_status_header(400)->set_output(json_encode(['error' => 'Invalid song']));
            return;
        }

        $result = $this->Favorites_model->add($userId, $songId);
        $this->output->set_content_type('application/json')
                     ->set_output(json_encode(['success' => $result]));
    }
}
