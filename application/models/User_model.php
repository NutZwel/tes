<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Model User — mengelola data pengguna (users) dan sesi aktif.
 *
 * Mencakup registrasi, verifikasi login, pengecekan duplikasi
 * username/email, serta deteksi user aktif dari tabel ci_sessions.
 */
class User_model extends CI_Model {

    /**
     * Buat user baru dengan password terenkripsi bcrypt.
     *
     * @param array $data  Keys: username, email, password, display_name (opsional)
     * @return int|false   ID user yang baru dibuat, atau false jika gagal
     */
    public function create(array $data)
    {
        $insert = [
            'username'      => $data['username'],
            'email'         => $data['email'],
            // Simpan hash, bukan plain text — bcrypt default cost sudah cukup
            'password_hash' => password_hash($data['password'], PASSWORD_BCRYPT),
            'display_name'  => $data['display_name'] ?? $data['username'],
        ];
        $this->db->insert('users', $insert);
        return $this->db->affected_rows() ? (int) $this->db->insert_id() : false;
    }

    /**
     * Verifikasi kredensial login (username/email + password).
     *
     * Menerima input username ATAU email pada satu field,
     * lalu mencocokkan dengan bcrypt.
     *
     * @param string $username_or_email
     * @param string $password           Password plain-text
     * @return object|null               Baris user jika cocok, null jika gagal
     */
    public function verify($username_or_email, $password)
    {
        // Hanya user aktif yang bisa login
        $this->db->where('is_active', 1);
        $this->db->group_start();
        $this->db->where('username', $username_or_email);
        $this->db->or_where('email', $username_or_email);
        $this->db->group_end();
        $user = $this->db->get('users')->row();

        // Verifikasi hash password
        if ($user && password_verify($password, $user->password_hash)) {
            return $user;
        }
        return null;
    }

    /**
     * Ambil data user berdasarkan ID.
     *
     * @param int  $id
     * @param bool $checkActive  Saring hanya user aktif (default true)
     * @return object|null
     */
    public function get_by_id($id, $checkActive = true)
    {
        $this->db->where('id', $id);
        if ($checkActive) $this->db->where('is_active', 1);
        return $this->db->get('users')->row();
    }

    /**
     * Cek apakah username sudah dipakai user lain.
     *
     * @param string $username
     * @return bool
     */
    public function username_exists($username)
    {
        return $this->db->where('username', $username)->count_all_results('users') > 0;
    }

    /**
     * Cek apakah email sudah terdaftar.
     *
     * @param string $email
     * @return bool
     */
    public function email_exists($email)
    {
        return $this->db->where('email', $email)->count_all_results('users') > 0;
    }

    /**
     * Ambil semua user diurutkan dari yang terbaru.
     *
     * @return array
     */
    public function get_all()
    {
        $this->db->select('id, username, email, display_name, role, is_active, created_at, updated_at');
        $this->db->from('users');
        $this->db->order_by('created_at', 'DESC');
        return $this->db->get()->result();
    }

    /**
     * Dapatkan ID user yang sedang aktif dari tabel sesi CI (dalam 30 menit terakhir).
     *
     * Berguna untuk menampilkan daftar user online di dashboard admin.
     *
     * @return array  Array unique user ID
     */
    public function get_active_ids()
    {
        $this->db->select('data');
        $this->db->from('ci_sessions');
        $this->db->where('timestamp >=', time() - 1800); // 30 menit
        $rows = $this->db->get()->result();
        $ids = [];
        foreach ($rows as $r) {
            $data = $this->_unserialize_ci_session($r->data);
            if (!empty($data['user_id'])) {
                $ids[] = (int) $data['user_id'];
            }
        }
        return array_unique($ids);
    }

    /**
     * Parse manual data sesi CI3 dari format serialized-nya.
     *
     * CI3 menyimpan session sebagai string key|serialized_value,
     * bukan JSON — perlu parsing manual.
     *
     * @param string $data
     * @return array
     */
    private function _unserialize_ci_session($data)
    {
        $result = [];
        $parts = explode('|', $data);
        for ($i = 0; $i < count($parts) - 1; $i += 2) {
            $key = $parts[$i];
            $value = @unserialize($parts[$i + 1]);
            $result[$key] = $value;
        }
        return $result;
    }
}
