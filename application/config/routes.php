<?php
defined('BASEPATH') OR exit('No direct script access allowed');

$route['default_controller'] = 'dashboard';
$route['404_override'] = '';
$route['translate_uri_dashes'] = FALSE;

// Plural-to-singular route mappings
$route['playlist'] = 'playlist/index';
$route['playlist/(:num)'] = 'playlist/view/$1';
$route['playlist/get_playlists_json'] = 'playlist/get_playlists_json';
$route['playlists'] = 'playlist';
$route['playlists/(:any)'] = 'playlist/$1';
$route['favorites'] = 'favorites';
$route['favorites/(:any)'] = 'favorites/$1';
$route['downloads'] = 'download/page';
$route['downloads/page'] = 'download/page';
$route['downloads/(:num)'] = 'download/index/$1';
$route['catalog'] = 'catalog';
$route['catalog/page/(:num)'] = 'catalog/index/$1';
$route['dashboard/continue_listening'] = 'dashboard/continue_listening';
$route['song/(:num)'] = 'song/index/$1';
$route['player/stream/(:num)'] = 'player/stream/$1';
$route['player/info/(:num)'] = 'player/info/$1';
$route['player/random'] = 'player/random';
$route['player/lyrics/(:num)'] = 'player/lyrics/$1';

// Auth
$route['login'] = 'auth/login';
$route['register'] = 'auth/register';
$route['logout'] = 'auth/logout';

// User profile & settings
$route['profile'] = 'user';
$route['profile/(:any)'] = 'user/$1';
$route['user/(:any)'] = 'user/$1';

// Admin
$route['admin'] = 'admin/index';
$route['admin/(:any)'] = 'admin/$1';
