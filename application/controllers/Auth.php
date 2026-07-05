<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Auth extends CI_Controller {

    public function __construct()
    {
        parent::__construct();
        $this->load->model('User_model');
    }

    public function register()
    {
        $data['title'] = 'Register — Laufey';
        $data['error'] = '';

        if ($this->input->method() === 'post') {
            $this->form_validation->set_rules('username', 'Username', 'required|trim|min_length[3]|max_length[60]|callback__username_check');
            $this->form_validation->set_rules('email', 'Email', 'required|trim|valid_email|callback__email_check');
            $this->form_validation->set_rules('password', 'Password', 'required|min_length[6]');
            $this->form_validation->set_rules('passconf', 'Confirm Password', 'required|matches[password]');

            if ($this->form_validation->run()) {
                $userId = $this->User_model->create([
                    'username'     => $this->input->post('username', true),
                    'email'        => $this->input->post('email', true),
                    'password'     => $this->input->post('password'),
                    'display_name' => $this->input->post('username', true),
                ]);

                if ($userId) {
                    $user = $this->User_model->get_by_id($userId);
                    $this->_set_session($user);
                    redirect('/');
                } else {
                    $data['error'] = 'Registration failed — please try again.';
                }
            }
        }

        $data['main_view'] = 'auth/register';
        $this->load->view('templates/layout', $data);
    }

    public function login()
    {
        $data['title'] = 'Sign In — Laufey';
        $data['error'] = '';

        if ($this->input->method() === 'post') {
            $this->form_validation->set_rules('identity', 'Username or Email', 'required|trim');
            $this->form_validation->set_rules('password', 'Password', 'required');

            if ($this->form_validation->run()) {
                $user = $this->User_model->verify(
                    $this->input->post('identity', true),
                    $this->input->post('password')
                );

                if ($user) {
                    $this->_set_session($user);
                    redirect('/');
                } else {
                    $data['error'] = 'Invalid credentials — no match found.';
                }
            }
        }

        $data['main_view'] = 'auth/login';
        $this->load->view('templates/layout', $data);
    }

    public function logout()
    {
        $this->session->unset_userdata('user_id');
        $this->session->unset_userdata('username');
        $this->session->unset_userdata('display_name');
        $this->session->sess_regenerate(true);
        redirect('/');
    }

    // ── Validation callbacks ──

    public function _username_check($username)
    {
        if ($this->User_model->username_exists($username)) {
            $this->form_validation->set_message('_username_check', 'That username is already taken.');
            return false;
        }
        return true;
    }

    public function _email_check($email)
    {
        if ($this->User_model->email_exists($email)) {
            $this->form_validation->set_message('_email_check', 'That email is already registered.');
            return false;
        }
        return true;
    }

    // ── Session helpers ──

    private function _set_session($user)
    {
        $this->session->set_userdata([
            'user_id'      => (int) $user->id,
            'username'     => $user->username,
            'display_name' => $user->display_name ?: $user->username,
            'role'         => $user->role ?? 'user',
            'avatar_path'  => $user->avatar_path ?? '',
        ]);
        $this->session->sess_regenerate(false);
    }
}
