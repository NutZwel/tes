<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Controller Welcome — halaman selamat datang default CI3.
 *
 * Controller default yang ditampilkan ketika aplikasi pertama
 * kali diakses. Berguna untuk verifikasi bahwa aplikasi berjalan.
 */
class Welcome extends CI_Controller {

    /**
     * Tampilkan halaman welcome bawaan CodeIgniter.
     */
    public function index()
    {
        $this->load->view('welcome_message');
    }
}
