<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Download_logs_model extends CI_Model {

    /**
     * Count guest downloads by IP for a specific date.
     *
     * @param string $ip      Visitor IP address
     * @param string $date    Date string (Y-m-d)
     * @return int
     */
    public function count_by_ip_date($ip, $date)
    {
        return (int) $this->db->where('ip_address', $ip)
                              ->where('download_date', $date)
                              ->where('user_id', null)
                              ->count_all_results('download_logs');
    }

    /**
     * Log a download attempt.
     *
     * @param int|null    $userId
     * @param string      $ip
     * @param int         $songId
     * @return int        Inserted log ID
     */
    public function log_download($userId, $ip, $songId)
    {
        $this->db->insert('download_logs', [
            'user_id'       => $userId,
            'ip_address'    => $ip,
            'song_id'       => (int) $songId,
            'download_date' => date('Y-m-d'),
        ]);
        return (int) $this->db->insert_id();
    }

    /**
     * Get download count for a registered user today.
     *
     * @param int $userId
     * @return int
     */
    public function count_by_user_today($userId)
    {
        return (int) $this->db->where('user_id', $userId)
                              ->where('download_date', date('Y-m-d'))
                              ->count_all_results('download_logs');
    }
}
