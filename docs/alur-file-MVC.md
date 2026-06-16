# Alur File MVC — Laufey Music Player

> Dokumentasi ini menjelaskan urutan akses file dalam arsitektur MVC (Model-View-Controller) 
> aplikasi Laufey beserta penjelasan peran masing-masing file dalam Bahasa Indonesia.

---

## Daftar Isi

1. [Entry Point — index.php](#1-entry-point--indexphp)
2. [Router & Konfigurasi Awal](#2-router--konfigurasi-awal)
3. [Lapisan Controller](#3-lapisan-controller)
4. [Lapisan Model](#4-lapisan-model)
5. [Lapisan View](#5-lapisan-view)
6. [Diagram Alur Lengkap](#6-diagram-alur-lengkap)

---

## 1. Entry Point — index.php

**Lokasi**: `/laufey/index.php`

**File PERTAMA yang diakses oleh setiap request.** Semua permintaan dari browser diarahkan ke sini melalui `.htaccess` (RewriteRule). File ini:

1. Mendefinisikan konstanta `FCPATH` (root directory project) dan `BASEPATH` (system CI3)
2. Menentukan ENVIRONMENT (`development` / `production`)
3. Me-load file bootstrap CodeIgniter (`system/core/CodeIgniter.php`)
4. DI situlah framework mengambil alih: me-load **config**, **routes**, kemudian menjalankan **controller** yang sesuai

> ✅ **Tidak perlu diubah** — hanya file system bootstrap.

---

## 2. Router & Konfigurasi Awal

Setelah index.php, framework membaca file konfigurasi dan routing secara berurutan.

### 2.1. application/config/routes.php

**Alur**: Dibaca oleh CI3 **sebelum** controller dijalankan — menentukan controller/method mana yang menangani URL.

| URL | Route | Controller::method |
|---|---|---|
| `/` | `default_controller` = `dashboard` | `Dashboard::index()` |
| `/catalog` | `$route['catalog'] = 'catalog'` | `Catalog::index()` |
| `/catalog/page/N` | `$route['catalog/page/(:num)']` | `Catalog::index($N)` |
| `/login` | `$route['login'] = 'auth/login'` | `Auth::login()` |
| `/register` | `$route['register'] = 'auth/register'` | `Auth::register()` |
| `/logout` | `$route['logout'] = 'auth/logout'` | `Auth::logout()` |
| `/playlist` | `$route['playlist'] = 'playlist/index'` | `Playlist::index()` |
| `/playlist/N` | `$route['playlist/(:num)']` | `Playlist::view($N)` |
| `/playlists/...` | `$route['playlists/(:any)']` | `Playlist::$1` |
| `/favorites` | `$route['favorites/(:any)']` | `Favorites::$1` |
| `/downloads/...` | `$route['downloads/(:any)']` | `Download::$1` |
| `/song/N` | `$route['song/(:num)']` | `Song::index($N)` |
| `/player/stream/N` | `$route['player/stream/(:num)']` | `Player::stream($N)` |
| `/player/info/N` | `$route['player/info/(:num)']` | `Player::info($N)` |
| `/player/lyrics/N` | `$route['player/lyrics/(:num)']` | `Player::lyrics($N)` |
| `/player/random` | `$route['player/random']` | `Player::random()` |
| `/profile/...` | `$route['profile/(:any)']` | `User::$1` |
| `/admin` | `$route['admin/(:any)']` | `Admin::$1` |

### 2.2. application/config/autoload.php

Dibaca setelah routes. Menentukan komponen yang di-load **setiap request**:

```php
$autoload['libraries'] = ['database', 'session', 'form_validation'];
$autoload['helper']    = ['url', 'form', 'cover'];
$autoload['model']     = [];  // tidak ada — model di-load per-controller
```

### 2.3. application/config/config.php

Konfigurasi base URL (auto-detect), session, encryption key, charset, dll.

### 2.4. application/config/database.php

Konfigurasi koneksi MySQL — host, username, password, database name (`laufey_db`).

---

## 3. Lapisan Controller

Setiap request masuk ke **satu controller**. Controller bertugas: validasi input, panggil model, kirim data ke view.

```
Urutan akses: Routes → Controller → (Model) → View
```

### 3.1. Dashboard — `/laufey/application/controllers/Dashboard.php`

**File pertama yang diakses user (halaman depan / home).**

| Method | URL | Deskripsi |
|---|---|---|
| `index()` | `/` | Cek session login. Jika guest → load `dashboard/main`. Jika user → load `dashboard/registered_full`. |
| | | Mengambil data: rekomendasi, trending, preview songs, playlist user, favorites |

**View yang dipanggil**: `dashboard/main` (guest) atau `dashboard/registered_full` (user login)
**Model yang dipanggil**: `Song_model`, `Playlist_model`, `Favorites_model`, `Listen_history_model`

---

### 3.2. Auth — `/laufey/application/controllers/Auth.php`

**Menangani login, register, logout.**

| Method | URL | Deskripsi |
|---|---|---|
| `login()` | `/login` | Validasi form (username/email + password), verifikasi via `User_model::verify()`, set session |
| `register()` | `/register` | Validasi form (username, email, password + confirm), buat user via `User_model::create()`, auto-login |
| `logout()` | `/logout` | Hapus session user, redirect ke `/` |

**Model**: `User_model`
**View**: `auth/login`, `auth/register`

---

### 3.3. Catalog — `/laufey/application/controllers/Catalog.php`

**Menampilkan daftar lagu dengan pagination dan pencarian.**

| Method | URL | Deskripsi |
|---|---|---|
| `index($page=1)` | `/catalog`, `/catalog/page/N` | Ambil lagu via `Song_model::get_paginated()`. Jika ada `?q=...`, lakukan pencarian via `Song_model::search()`. |

**Model**: `Song_model`
**View**: `catalog/grid`

---

### 3.4. Song — `/laufey/application/controllers/Song.php`

**Halaman detail satu lagu.**

| Method | URL | Deskripsi |
|---|---|---|
| `index($id)` | `/song/N` | Ambil data lagu via `Song_model::get_by_id()`, ambil lirik via `Lyrics_model`, ambil lagu serupa (genre sama). |

**Model**: `Song_model`, `Lyrics_model`
**View**: `song/detail`

---

### 3.5. Player — `/laufey/application/controllers/Player.php`

**Streaming audio dan data pendukung.**

| Method | URL | Deskripsi |
|---|---|---|
| `stream($id)` | `/player/stream/N` | **Enforce guest limit** (max 3 plays/session). Baca file audio dari `FCPATH . file_path`, kirim dengan header HTTP Range (support seeking). Log play ke `Listen_history_model`. |
| `info($id)` | `/player/info/N` | Return JSON: id, title, artist, file_path, cover_path |
| `lyrics($id)` | `/player/lyrics/N` | Cari lirik di DB via `Lyrics_model`. Jika tidak ada, fetch dari LRCLIB API, cache ke DB. |
| `random()` | `/player/random` | Return JSON: satu lagu random untuk shuffle mode |

**Model**: `Song_model`, `Lyrics_model`, `Listen_history_model`

---

### 3.6. Playlist — `/laufey/application/controllers/Playlist.php`

**CRUD playlist milik user (wajib login).**

| Method | URL | Deskripsi |
|---|---|---|
| `index()` | `/playlist` | Tampilkan semua playlist milik user yang login |
| `view($id)` | `/playlist/N` | Detail playlist: daftar lagu, total durasi |
| `create()` | `/playlist/create` | Form + simpan playlist baru |
| `edit($id)` | `/playlist/edit/N` | Form edit playlist (dengan ownership check) |
| `delete($id)` | `/playlist/delete/N` | Hapus playlist (dengan ownership check) |
| `add_song($id)` | — | Tambah lagu ke playlist (dengan ownership check!) |
| `remove_song($id)` | — | Hapus lagu dari playlist (dengan ownership check!) |
| `get_playlists_json()` | — | Return JSON daftar playlist (untuk playlist picker modal) |

**Model**: `Playlist_model`
**View**: `playlist/index`, `playlist/detail`, `playlist/create`, `playlist/edit`

---

### 3.7. Favorites — `/laufey/application/controllers/Favorites.php`

**Fitur favorit/unfavorit lagu.**

| Method | URL | Deskripsi |
|---|---|---|
| `index()` | `/favorites` | Tampilkan daftar lagu favorit user |
| `add()` | POST | Tambah lagu ke favorit |
| `remove($id)` | POST | Hapus lagu dari favorit |

**Model**: `Favorites_model`
**View**: Tidak ada sendiri — data favorit tampil di dashboard.

---

### 3.8. User — `/laufey/application/controllers/User.php`

**Profil user, preferences, export data.**

| Method | URL | Deskripsi |
|---|---|---|
| `index()` | `/user`, `/profile` | Tampilkan halaman profil (tabs: listening history, preferences, account) |
| `update_preferences()` | POST | Simpan preferensi user (autoplay, theme, bahasa, dll) |
| `export_data()` | `/user/export_data` | Download data pengguna (JSON) |
| `logout_all()` | `/user/logout_all` | Logout dari semua session |
| `save_pref_ajax()` | POST | Simpan preference via AJAX (theme/color instant save) |

**Model**: `User_model`, `Song_model`, `Playlist_model`, `Favorites_model`, `Listen_history_model`
**View**: `user/profile`

---

### 3.9. Admin — `/laufey/application/controllers/Admin.php`

**Panel admin: manajemen lagu dan user.** Hanya bisa diakses role `admin`.

| Method | URL | Deskripsi |
|---|---|---|
| `index()` | `/admin` | Dashboard admin: stats (total user, online, total lagu), daftar user |
| `add_song()` | `/admin/add_song` | Form + upload lagu baru (MP3 ke `protected_uploads/audio/`, cover ke `protected_uploads/covers/`) |
| `edit_song($id)` | `/admin/edit_song/N` | Form edit lagu |
| `delete_song($id)` | `/admin/delete_song/N` | **Hard delete** lagu + file audio + cascade ke tabel terkait |
| `songs()` | `/admin/songs` | Daftar semua lagu untuk manage |
| `toggle_user($id)` | `/admin/toggle_user/N` | Aktif/non-aktifkan user |

**Helper**: `_upload_file()` — upload dengan validasi extension + MIME via finfo_file()
**Model**: `User_model`, `Song_model`
**View**: `admin/index`, `admin/songs`, `admin/add_song`, `admin/edit_song`

---

### 3.10. Download — `/laufey/application/controllers/Download.php`

**Melayani download file audio.** File DISEMBUNYIKAN dari akses langsung — hanya bisa melalui controller ini.

| Method | URL | Deskripsi |
|---|---|---|
| `index($songId)` | `/downloads/N` | Cek limit guest (1x/hari/IP), serve via `force_download()` |

**Model**: `Song_model`, `Download_logs_model`

---

## 4. Lapisan Model

Model berkomunikasi langsung dengan database. Controller memanggil model untuk mengambil/menyimpan data.

```
Controller → Model → Database → Model → Controller
```

### 4.1. Song_model — `/laufey/application/models/Song_model.php`

| Method | Fungsi |
|---|---|
| `get_paginated($page, $per_page)` | Ambil lagu aktif dengan pagination + join genre |
| `count_all()` | Hitung total lagu aktif |
| `get_by_id($id, $checkActive)` | Ambil satu lagu by ID (bisa include yg non-aktif) |
| `search($query, $page, $per_page)` | Cari lagu by title/artist with pagination |
| `count_search($query)` | Hitung hasil pencarian |

### 4.2. User_model — `/laufey/application/models/User_model.php`

| Method | Fungsi |
|---|---|
| `create($data)` | Buat user baru (password di-bcrypt) |
| `verify($identity, $password)` | Verifikasi login (username/email + password) |
| `get_by_id($id)` | Ambil data user |
| `get_all()` | Ambil semua user |
| `get_active_ids()` | Ambil ID user yang online (berdasarkan session aktif) |
| `username_exists($username)` | Cek username sudah terpakai |
| `email_exists($email)` | Cek email sudah terdaftar |

### 4.3. Playlist_model — `/laufey/application/models/Playlist_model.php`

| Method | Fungsi |
|---|---|
| `get_by_user($userId)` | Ambil semua playlist milik user |
| `get_with_songs($id)` | Ambil playlist + lagu di dalamnya |
| `create($data)` | Buat playlist baru |
| `update($id, $data)` | Update playlist |
| `delete($id)` | Hapus playlist |
| `add_song($playlistId, $songId)` | Tambah lagu ke playlist |
| `remove_song($playlistId, $songId)` | Hapus lagu dari playlist |
| `search_public($query)` | Cari playlist publik |

### 4.4. Favorites_model — `/laufey/application/models/Favorites_model.php`

| Method | Fungsi |
|---|---|
| `get_by_user($userId)` | Ambil semua favorit user |
| `add($userId, $songId)` | Tambah favorit |
| `remove($userId, $songId)` | Hapus favorit |
| `is_favorited($userId, $songId)` | Cek apakah user sudah memfavoritkan lagu |

### 4.5. Lyrics_model — `/laufey/application/models/Lyrics_model.php`

| Method | Fungsi |
|---|---|
| `get_by_song($songId)` | Ambil lirik berdasarkan song_id |
| `save($songId, $content, $format)` | Simpan lirik (insert/replace) |

### 4.6. Listen_history_model — `/laufey/application/models/Listen_history_model.php`

| Method | Fungsi |
|---|---|
| `log_play($userId, $songId)` | Catat bahwa user memutar lagu |
| `get_recent($userId, $limit)` | Ambil history terbaru |
| `get_trending($limit)` | Ambil lagu yang paling sering diputar |
| `get_recommendations($userId, $limit)` | Rekomendasi berdasarkan genre yang sering didengar |
| `clear_for_user($userId)` | Hapus semua history user (belum dipanggil controller mana pun) |

### 4.7. Download_logs_model — `/laufey/application/models/Download_logs_model.php`

| Method | Fungsi |
|---|---|
| `count_by_ip_today($ip)` | Hitung download guest hari ini berdasarkan IP |
| `log_download($data)` | Catat download |
| `count_by_user_today($userId)` | Hitung download user hari ini (belum dipanggil controller mana pun) |

---

## 5. Lapisan View

View adalah file PHP yang berisi HTML + Bootstrap. Controller mengirim data ke view.

### Layout Utama

```
templates/layout.php
  ├── templates/nav.php (navbar + sidebar)
  ├── [main_view] (isi halaman)
  ├── templates/footer.php (footer)
  ├── templates/player.php (player bar)
  └── templates/modal.php (dialog + playlist picker)
```

Semua halaman melewati `layout.php`. File `main_view` ditentukan oleh controller.

### 5.1. templates/layout.php

**Kerangka HTML lengkap.** Berisi:
- `<head>`: meta tags, Bootstrap CDN, `tokens.css`, CSS variable tema (oklch), inline CSS theme animations (starshower, aurora, matrix, bubble)
- `<body>`: navbar, `#pjax-content` (diisi main_view), footer, player, scripts
- Script: Bootstrap JS, pjax.js, player.js, carousel.js

### 5.2. templates/nav.php

**Navigasi.** Dua mode:
- **Guest**: Dashboard, Catalog, Login, Sign Up
- **User**: Sidebar (offcanvas) + top navbar dengan search

Highlight otomatis berdasarkan halaman aktif via `$navSection` (PHP) + `updateNav()` (PJAX JS).

### 5.3. templates/footer.php

**Footer + JavaScript tambahan:**
- Logo, navigasi, icon sosial
- Carousel arrow handler (scroll kiri/kanan)
- Mobile nav toggle
- Auth form loading state

### 5.4. templates/player.php

**Bottom player bar.** Selalu ada di semua halaman (fixed bottom). Berisi:
- Info lagu (cover, title, artist) → link ke halaman detail
- Tombol: prev, play/pause, next
- Progress bar (click-to-seek)
- Volume slider
- Tombol: lyrics panel, loop/shuffle mode, menu (queue)
- **Lyrics panel** (fullscreen overlay) — menampilkan lirik LRC sync atau plain text

### 5.5. templates/modal.php

**Dialog sistem (native `<dialog>`):**
- `confirmDlg` — konfirmasi aksi (delete playlist, delete song)
- `pickerDlg` — playlist picker untuk menambah lagu ke playlist

### Daftar View Per Halaman

| View File | Controller | Deskripsi |
|---|---|---|
| `dashboard/main.php` | `Dashboard` (guest) | Hero section + register CTA |
| `dashboard/registered_full.php` | `Dashboard` (user) | Continue listening, Made For You, Discover, Playlists, Favorites, Trending |
| `catalog/grid.php` | `Catalog` | Grid lagu dengan card + pagination |
| `song/detail.php` | `Song` | Detail lagu, lirik, similar songs |
| `auth/login.php` | `Auth` | Form login |
| `auth/register.php` | `Auth` | Form register |
| `auth/admin_login.php` | — | Form login admin (legacy) |
| `user/profile.php` | `User` | Profil user dengan tabs (history, preferences, account) |
| `playlist/index.php` | `Playlist` | Daftar playlist user |
| `playlist/detail.php` | `Playlist::view()` | Detail playlist dengan daftar lagu |
| `playlist/create.php` | `Playlist::create()` | Form buat playlist |
| `playlist/edit.php` | `Playlist::edit()` | Form edit playlist |
| `admin/index.php` | `Admin` | Dashboard admin + tabel user |
| `admin/songs.php` | `Admin::songs()` | Tabel manage lagu |
| `admin/add_song.php` | `Admin::add_song()` | Form upload lagu |
| `admin/edit_song.php` | `Admin::edit_song()` | Form edit lagu |

---

## 6. Diagram Alur Lengkap

```
Browser ──GET──→ index.php
                    │
                    ▼
              Config (autoload, database, config)
                    │
                    ▼
              Router (routes.php)
                    │
                    ▼
          ┌─── Controller ───┐
          │                  │
          ▼                  ▼
        Model             View (layout.php)
          │                  │
          ▼                  ├── nav.php
      Database              ├── [main_view]
          │                 ├── footer.php
          ▼                 ├── player.php
     Return data            └── modal.php
          │
          ▼
    Controller format data
          │
          ▼
     Send ke View
          │
          ▼
     HTML ke Browser
```

### Alur Streaming Lagu

```
User klik "play" di catalog
       │
       ▼
player.js → playSongById(id) → XHR GET /player/info/N
       │
       ▼
Player::info($id) → return JSON (id, title, artist, file_path, cover_path)
       │
       ▼
player.js → audio.src = BASE + 'player/stream/' + song.id → audio.play()
       │
       ▼
Player::stream($id) → check guest limit → read file from protected_uploads/audio/
       │
       ▼
Kirim file dengan header HTTP Range → browser play audio
```

### Alur Download Lagu

```
User klik "Download" → XHR GET /downloads/N
       │
       ▼
Download::index($id) → check guest/IP limit → force_download()
       │
       ▼
File dari protected_uploads/audio/ dikirim ke browser
```

---

> **Catatan**: Semua file audio disimpan di `protected_uploads/audio/` (di luar web root). 
> Tidak ada akses langsung via URL — hanya melalui `Player::stream()` dan `Download::index()`.
> Ini adalah fitur keamanan utama aplikasi.
