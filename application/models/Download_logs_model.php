<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Model Download_Logs — mencatat dan memeriksa aktivitas download.
 *
 * Digunakan untuk membatasi download guest (1x/hari per IP)
 * dan melacak riwayat download user terdaftar.
 */
class Download_logs_model extends CI_Model {

    /**
     * Hitung jumlah download guest berdasarkan IP dan tanggal tertentu.
     *
     * Hanya menghitung log yang tidak memiliki user_id (guest).
     *
     * @param string $ip      Alamat IP pengunjung
     * @param string $date    Tanggal dalam format Y-m-d
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
     * Catat aktivitas download ke database.
     *
     * Untuk guest (user_id = null), IP address dicatat sebagai identitas.
     * Untuk user terdaftar, IP boleh dikosongkan.
     *
     * @param int|null    $userId
     * @param string      $ip
     * @param int         $songId
     * @return int        ID log yang baru dibuat
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
     * Hitung jumlah download user terdaftar pada hari ini.
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
