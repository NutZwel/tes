<?php
defined('BASEPATH') OR exit('No direct script access allowed');

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
     * Profile page — redirect to login if not authenticated.
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

        // Ensure theme info is in session
        $this->session->set_userdata([
            'theme_style'   => $prefs->theme ?? 'dark',
            'theme_color'   => $prefs->theme_color ?? 'blue',
            'theme_bg_css' => $prefs->theme_bg_css ?? '',
        ]);

        $this->load->view('templates/layout', $data);
    }

    /**
     * Update profile fields (username, display_name, email, bio, avatar).
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

        // Avatar upload
        if (!empty($_FILES['avatar']['name'])) {
            $avatarPath = $this->upload_avatar($userId);
            if ($avatarPath) {
                $update['avatar_path'] = $avatarPath;
            }
        }

        // Remove avatar
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
     * Change password.
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
     * Update user preferences via AJAX (instant theme/accent changes).
     */
    public function save_pref_ajax()
    {
        $userId = $this->require_auth();

        $field = $this->input->post('field', TRUE);
        $value = $this->input->post('value', TRUE);

        $allowedFields = ['theme', 'theme_color', 'theme_bg_css', 'autoplay', 'show_activity', 'email_notifs', 'language'];
        if (!in_array($field, $allowedFields)) {
            $this->output->set_status_header(400)->set_output(json_encode(['error' => 'Invalid field']));
            return;
        }

        // Ensure user_prefs row exists
        $exists = $this->db->where('user_id', $userId)->get('user_prefs')->row();
        if (!$exists) {
            $this->db->insert('user_prefs', ['user_id' => $userId]);
        }

        $this->db->where('user_id', $userId)->update('user_prefs', [$field => $value]);

        // Sync theme info to session for layout
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
     * Update user preferences.
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

        // Background CSS (gradient/pattern presets or custom)
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

        // Store theme info in session for layout
        $this->session->set_userdata([
            'theme_style'   => $prefs['theme'],
            'theme_color'   => $prefs['theme_color'],
            'theme_bg_css' => $prefs['theme_bg_css'] ?? '',
        ]);

        $this->session->set_flashdata('profile_success', TRUE);
        redirect('user');
    }

    /**
     * Export user data as JSON download.
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
        unset($export['profile']->password_hash);

        $this->output
            ->set_content_type('application/json')
            ->set_header('Content-Disposition: attachment; filename="laufey-data-'.$userId.'.json"')
            ->set_output(json_encode($export, JSON_PRETTY_PRINT));
    }

    /**
     * Sign out all other sessions.
     */
    public function logout_all()
    {
        $userId = $this->require_auth();
        // CI3 session driver — regenerate invalidates old sessions
        $this->session->sess_regenerate(TRUE);
        $this->session->set_flashdata('profile_success', TRUE);
        redirect('user');
    }

    /* ──────────────────────────────────────────
       Helpers
       ────────────────────────────────────────── */

    private function require_auth(): int
    {
        $userId = (int) $this->session->userdata('user_id');
        if ($userId <= 0) {
            redirect('login');
            exit;
        }
        return $userId;
    }

    private function get_prefs(int $userId): object
    {
        $prefs = $this->db->where('user_id', $userId)->get('user_prefs')->row();
        if (!$prefs) {
            $this->db->insert('user_prefs', ['user_id' => $userId]);
            $prefs = $this->db->where('user_id', $userId)->get('user_prefs')->row();
        }
        return $prefs;
    }

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
     * Custom form validation: username must be unique (excluding current user).
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
