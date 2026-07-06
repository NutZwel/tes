<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Controller Admin_Login — halaman diagnostik dan login admin terpisah.
 *
 * Digunakan saat setup awal untuk memeriksa koneksi database,
 * keberadaan tabel users, dan validasi password admin default.
 * Setelah semua berfungsi, admin bisa login langsung dari sini.
 */
class Admin_login extends CI_Controller {

    /**
     * Halaman login admin dengan diagnostik database.
     *
     * Menampilkan status koneksi DB, keberadaan tabel users,
     * dan status user admin. Juga menerima POST untuk login
     * dan redirect ke dashboard admin jika role = admin.
     */
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

        // Cek keberadaan user admin
        if ($dbOk) {
            $admin = $this->db->where('username', 'admin')->get('users')->row();
            if ($admin) {
                $msg .= 'Admin user exists (role: ' . $admin->role . '). ';
                // Test password default untuk verifikasi seed berhasil
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

        // Handle form login
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
