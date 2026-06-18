<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Admin_login extends CI_Controller {

    public function index()
    {
        // Cek koneksi database
        $dbOk = false;
        $msg = '';
        $msgType = 'info';

        try {
            $this->db->query('SELECT 1');
            $dbOk = true;
            $msg = 'Database connected. ';
        } catch (Exception $e) {
            $msg = 'Database error: ' . $e->getMessage();
            $msgType = 'error';
        }

        // Cek apakah tabel users ada
        if ($dbOk) {
            $tableCheck = $this->db->query("SHOW TABLES LIKE 'users'")->row();
            if (!$tableCheck) {
                $msg .= 'Table "users" not found! Import schema.sql first.';
                $msgType = 'error';
                $dbOk = false;
            } else {
                $msg .= 'Table "users" found. ';
            }
        }

        // Cek admin user
        if ($dbOk) {
            $admin = $this->db->where('username', 'admin')->get('users')->row();
            if ($admin) {
                $msg .= 'Admin user exists (role: ' . $admin->role . '). ';
                // Test password
                if (password_verify('admin123', $admin->password_hash)) {
                    $msg .= 'Password "admin123" is CORRECT.';
                    $msgType = 'success';
                } else {
                    $msg .= 'But password "admin123" does NOT match stored hash! Need to recreate admin.';
                    $msgType = 'error';
                }
            } else {
                $msg .= 'Admin user not found. ';
                $msgType = 'error';
            }
        }

        // Handle login POST
        if ($this->input->method() === 'post') {
            $identity = $this->input->post('identity', true);
            $password = $this->input->post('password');

            $this->load->model('User_model');
            $user = $this->User_model->verify($identity, $password);

            if ($user) {
                $this->session->set_userdata([
                    'user_id'      => (int) $user->id,
                    'username'     => $user->username,
                    'display_name' => $user->display_name ?: $user->username,
                    'role'         => $user->role ?? 'user',
                ]);
                $this->session->sess_regenerate(false);

                if ($user->role === 'admin') {
                    redirect('admin');
                } else {
                    redirect('/');
                }
            } else {
                $error = 'Invalid credentials. Check username/password.';
            }
        }

        $data['msg'] = $msg;
        $data['msg_type'] = $msgType;
        $data['error'] = $error ?? '';
        $this->load->view('auth/admin_login', $data);
    }
}
