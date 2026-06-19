<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class User_model extends CI_Model {

    /**
     * Create a new user with bcrypt password hash.
     *
     * @param array $data  Keys: username, email, password, display_name (optional)
     * @return int|false   Inserted user ID, or false on failure
     */
    public function create(array $data)
    {
        $insert = [
            'username'      => $data['username'],
            'email'         => $data['email'],
            'password_hash' => password_hash($data['password'], PASSWORD_BCRYPT),
            'display_name'  => $data['display_name'] ?? $data['username'],
        ];
        $this->db->insert('users', $insert);
        return $this->db->affected_rows() ? (int) $this->db->insert_id() : false;
    }

    /**
     * Verify login credentials.
     *
     * @param string $username_or_email
     * @param string $password           Plain-text password
     * @return object|null               User row on success, null on failure
     */
    public function verify($username_or_email, $password)
    {
        $this->db->where('is_active', 1);
        $this->db->group_start();
        $this->db->where('username', $username_or_email);
        $this->db->or_where('email', $username_or_email);
        $this->db->group_end();
        $user = $this->db->get('users')->row();

        if ($user && password_verify($password, $user->password_hash)) {
            return $user;
        }
        return null;
    }

    /**
     * Get a user by their ID.
     *
     * @param int $id
     * @return object|null
     */
    public function get_by_id($id, $checkActive = true)
    {
        $this->db->where('id', $id);
        if ($checkActive) $this->db->where('is_active', 1);
        return $this->db->get('users')->row();
    }

    /**
     * Check if a username is already taken.
     *
     * @param string $username
     * @return bool
     */
    public function username_exists($username)
    {
        return $this->db->where('username', $username)->count_all_results('users') > 0;
    }

    /**
     * Check if an email is already taken.
     *
     * @param string $email
     * @return bool
     */
    public function email_exists($email)
    {
        return $this->db->where('email', $email)->count_all_results('users') > 0;
    }

    /**
     * Get all users ordered by latest.
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
     * Get active (logged-in) user IDs from CI sessions.
     *
     * @return array
     */
    public function get_active_ids()
    {
        $this->db->select('data');
        $this->db->from('ci_sessions');
        $this->db->where('timestamp >=', time() - 1800);
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
