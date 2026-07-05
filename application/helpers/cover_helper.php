<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Cek apakah cover path bisa diakses.
 * Untuk URL eksternal (http) langsung return true.
 * Untuk lokal cek file_exists.
 */
function cover_available($cover_path)
{
    if (empty($cover_path)) return false;
    if (strpos($cover_path, 'http') === 0) return true;
    return file_exists(FCPATH . $cover_path);
}

/**
 * Get cover URL.
 */
function cover_url($cover_path)
{
    if (empty($cover_path)) return null;
    if (strpos($cover_path, 'http') === 0) return $cover_path;
    return base_url($cover_path);
}
