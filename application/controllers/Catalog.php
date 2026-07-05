<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Catalog extends CI_Controller {

    public function __construct()
    {
        parent::__construct();
        $this->load->model('Song_model');
    }

    public function index($page = 1)
    {
        $per_page = 24;
        $page = max(1, (int) $page);

        $search = $this->input->get('q') ? trim($this->input->get('q')) : '';

        if (!empty($search)) {
            $data['songs'] = $this->Song_model->search($search, $page, $per_page);
            $total = $this->Song_model->count_search($search);
            $this->load->model('Playlist_model');
            $data['public_playlists'] = $this->Playlist_model->search_public($search);
        } else {
            $data['songs'] = $this->Song_model->get_paginated($page, $per_page);
            $total = $this->Song_model->count_all();
            $data['public_playlists'] = [];
        }

        $total_pages = max(1, ceil($total / $per_page));

        $data['title'] = 'Catalog — Laufey';
        $data['main_view'] = 'catalog/grid';
        $data['current_page'] = $page;
        $data['total_pages'] = $total_pages;
        $data['total_songs'] = $total;
        $data['search_query'] = $search;

        $this->load->view('templates/layout', $data);
    }
}
