<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Controller User — profil dan preferensi pengguna.
 *
 * Mencakup manajemen profil (username, display name, bio, avatar),
 * perubahan password, preferensi tema/warna, ekspor data,
 * serta logout dari semua perangkat.
 * Semua method (kecuali helper) memerlukan autentikasi.
 */
class User extends CI_Controller {

    public function __construct()
    {
        parent::__construct();
        $this->load->model('User_model');
        $this->load->model('Favorites_model');
        $this->load->model('Playlist_model');
        $this->load->model('Listen_history_model');
        $this->load->library(['form_validation', 'upload']);
    }

    /**
     * Halaman profil — redirect ke login jika belum autentikasi.
     */
    public function index()
    {
        $userId = $this->require_auth();

        $user = $this->User_model->get_by_id($userId);
        if (!$user) {
            redirect('login');
            return;
        }

        $data['user']            = $user;
        $data['favorites_count']  = $this->Favorites_model->count_by_user($userId);
        $data['playlists_count']  = $this->Playlist_model->count_by_user($userId);
        $data['total_listens']    = $this->Listen_history_model->count_by_user($userId);
        $prefs = $this->get_prefs($userId);
        $data['prefs'] = $prefs;
        $data['title'] = 'Profile — Laufey';
        $data['main_view'] = 'user/profile';

        // Pastikan info tema ada di session untuk layout
        $this->session->set_userdata([
            'theme_style'   => $prefs->theme ?? 'dark',
            'theme_color'   => $prefs->theme_color ?? 'blue',
            'theme_bg_css' => $prefs->theme_bg_css ?? '',
        ]);

        $this->load->view('templates/layout', $data);
    }

    /**
     * Update profil: username, display_name, email, bio, avatar.
     */
    public function update_profile()
    {
        $userId = $this->require_auth();

        $this->form_validation->set_rules('username', 'Username', 'required|min_length[3]|max_length[32]|callback_valid_username');
        $this->form_validation->set_rules('display_name', 'Display Name', 'max_length[64]');
        $this->form_validation->set_rules('email', 'Email', 'required|valid_email|max_length[128]');
        $this->form_validation->set_rules('bio', 'Bio', 'max_length[280]');

        if ($this->form_validation->run() === FALSE) {
            $this->index();
            return;
        }

        $update = [
            'username'     => $this->input->post('username', TRUE),
            'display_name' => $this->input->post('display_name', TRUE) ?: NULL,
            'email'        => $this->input->post('email', TRUE),
            'bio'          => $this->input->post('bio', TRUE) ?: NULL,
        ];

        // Upload avatar jika ada file baru
        if (!empty($_FILES['avatar']['name'])) {
            $avatarPath = $this->upload_avatar($userId);
            if ($avatarPath) {
                $update['avatar_path'] = $avatarPath;
            }
        }

        // Hapus avatar jika diminta
        if ($this->input->post('remove_avatar')) {
            $update['avatar_path'] = NULL;
        }

        $this->db->where('id', $userId)->update('users', $update);
        $this->session->set_userdata([
            'display_name' => $update['display_name'] ?: $update['username'],
            'avatar_path'  => $update['avatar_path'] ?? '',
        ]);
        $this->session->set_flashdata('profile_success', TRUE);
        redirect('user');
    }

    /**
     * Ganti password — memerlukan password lama yang benar.
     */
    public function change_password()
    {
        $userId = $this->require_auth();

        $this->form_validation->set_rules('current_password', 'Current Password', 'required');
        $this->form_validation->set_rules('new_password', 'New Password', 'required|min_length[8]');
        $this->form_validation->set_rules('confirm_password', 'Confirm Password', 'required|matches[new_password]');

        if ($this->form_validation->run() === FALSE) {
            $this->index();
            return;
        }

        $user = $this->User_model->get_by_id($userId);
        // Verifikasi password lama sebelum mengizinkan perubahan
        if (!password_verify($this->input->post('current_password'), $user->password_hash)) {
            $this->session->set_flashdata('password_error', 'Current password is incorrect.');
            redirect('user');
            return;
        }

        $this->db->where('id', $userId)->update('users', [
            'password_hash' => password_hash($this->input->post('new_password'), PASSWORD_BCRYPT),
        ]);
        $this->session->set_flashdata('password_success', TRUE);
        redirect('user');
    }

    /**
     * Simpan preferensi user secara instan via AJAX.
     *
     * Mendukung field: theme, theme_color, theme_bg_css, autoplay,
     * show_activity, email_notifs, language.
     * Theme info langsung disinkronkan ke session untuk layout.
     */
    public function save_pref_ajax()
    {
        $userId = $this->require_auth();

        $field = $this->input->post('field', TRUE);
        $value = $this->input->post('value', TRUE);

        // Whitelist field yang diizinkan untuk mencegah injeksi
        $allowedFields = ['theme', 'theme_color', 'theme_bg_css', 'autoplay', 'show_activity', 'email_notifs', 'language'];
        if (!in_array($field, $allowedFields)) {
            $this->output->set_status_header(400)->set_output(json_encode(['error' => 'Invalid field']));
            return;
        }

        // Pastikan row user_prefs sudah ada
        $exists = $this->db->where('user_id', $userId)->get('user_prefs')->row();
        if (!$exists) {
            $this->db->insert('user_prefs', ['user_id' => $userId]);
        }

        $this->db->where('user_id', $userId)->update('user_prefs', [$field => $value]);

        // Sinkronkan info tema ke session agar layout langsung berubah
        if (in_array($field, ['theme', 'theme_color', 'theme_bg_css'])) {
            if ($field === 'theme_bg_css') {
                $this->session->set_userdata('theme_bg_css', $value);
            } else {
                $this->session->set_userdata($field === 'theme' ? 'theme_style' : 'theme_color', $value);
            }
        }

        $this->output->set_content_type('application/json')->set_output(json_encode(['success' => true]));
    }

    /**
     * Update preferensi user (form submit biasa).
     */
    public function update_preferences()
    {
        $userId = $this->require_auth();

        $prefs = [
            'autoplay'      => $this->input->post('autoplay') ? 1 : 0,
            'show_activity' => $this->input->post('show_activity') ? 1 : 0,
            'email_notifs'  => $this->input->post('email_notifs') ? 1 : 0,
            'theme'         => $this->input->post('theme', TRUE) ?: 'dark',
            'theme_color'   => $this->input->post('theme_color', TRUE) ?: 'blue',
            'language'      => $this->input->post('language', TRUE) ?: 'en',
        ];

        // Background CSS — prioritas: custom > preset > null
        $bgCss = $this->input->post('theme_bg_css', TRUE);
        $customCss = $this->input->post('custom_bg_css', TRUE);
        if (!empty($customCss)) {
            $prefs['theme_bg_css'] = $customCss;
        } elseif (!empty($bgCss)) {
            $prefs['theme_bg_css'] = $bgCss;
        } else {
            $prefs['theme_bg_css'] = NULL;
        }

        $this->db->where('user_id', $userId)->update('user_prefs', $prefs);

        // Simpan di session untuk layout
        $this->session->set_userdata([
            'theme_style'   => $prefs['theme'],
            'theme_color'   => $prefs['theme_color'],
            'theme_bg_css' => $prefs['theme_bg_css'] ?? '',
        ]);

        $this->session->set_flashdata('profile_success', TRUE);
        redirect('user');
    }

    /**
     * Ekspor data user sebagai file JSON (download).
     *
     * Mencakup profil, favorit, playlist, dan riwayat pemutaran.
     * Password_hash dihapus dari output.
     */
    public function export_data()
    {
        $userId = $this->require_auth();

        $export = [
            'profile'    => $this->User_model->get_by_id($userId),
            'favorites'  => $this->Favorites_model->get_by_user($userId),
            'playlists'  => $this->Playlist_model->get_by_user($userId),
            'listens'    => $this->Listen_history_model->get_by_user($userId, 500),
        ];
        // Jangan ekspos hash password
        unset($export['profile']->password_hash);

        $this->output
            ->set_content_type('application/json')
            ->set_header('Content-Disposition: attachment; filename="laufey-data-'.$userId.'.json"')
            ->set_output(json_encode($export, JSON_PRETTY_PRINT));
    }

    /**
     * Logout dari semua perangkat — regenerasi session ID.
     *
     * CI3 regenerasi session akan membatalkan semua session
     * lain yang menggunakan session ID lama.
     */
    public function logout_all()
    {
        $userId = $this->require_auth();
        $this->session->sess_regenerate(TRUE);
        $this->session->set_flashdata('profile_success', TRUE);
        redirect('user');
    }

    /* ──────────────────────────────────────────
       Helper
       ────────────────────────────────────────── */

    /**
     * Pastikan user sudah login. Redirect ke login jika belum.
     *
     * @return int User ID
     */
    private function require_auth(): int
    {
        $userId = (int) $this->session->userdata('user_id');
        if ($userId <= 0) {
            redirect('login');
            exit;
        }
        return $userId;
    }

    /**
     * Ambil preferensi user, buat baris default jika belum ada.
     *
     * @param int $userId
     * @return object
     */
    private function get_prefs(int $userId): object
    {
        $prefs = $this->db->where('user_id', $userId)->get('user_prefs')->row();
        if (!$prefs) {
            $this->db->insert('user_prefs', ['user_id' => $userId]);
            $prefs = $this->db->where('user_id', $userId)->get('user_prefs')->row();
        }
        return $prefs;
    }

    /**
     * Upload foto avatar user.
     *
     * @param int $userId
     * @return string|null Path relatif file avatar, atau null jika gagal
     */
    private function upload_avatar(int $userId): ?string
    {
        $config = [
            'upload_path'   => FCPATH . 'protected_uploads/avatars/',
            'allowed_types' => 'jpg|jpeg|png|webp|gif',
            'max_size'      => 2048,
            'file_name'     => 'avatar_' . $userId . '_' . time(),
        ];
        if (!is_dir($config['upload_path'])) {
            mkdir($config['upload_path'], 0755, TRUE);
        }
        $this->upload->initialize($config);

        if (!$this->upload->do_upload('avatar')) {
            log_message('error', 'Avatar upload failed: ' . $this->upload->display_errors('', ''));
            return NULL;
        }
        $uploadData = $this->upload->data();
        return 'protected_uploads/avatars/' . $uploadData['file_name'];
    }

    /**
     * Callback validasi: pastikan username unik (kecuali milik user sendiri).
     *
     * @param string $username
     * @return bool
     */
    public function valid_username(string $username): bool
    {
        $userId = (int) $this->session->userdata('user_id');
        $existing = $this->db->where('username', $username)
                              ->where('id !=', $userId)
                              ->get('users')
                              ->row();
        if ($existing) {
            $this->form_validation->set_message('valid_username', 'This username is already taken.');
            return FALSE;
        }
        return TRUE;
    }
}
