# CHANGELOG — Laufey Music Player & Downloader

> Semua perubahan, masalah yang ditemukan, dan cara perbaikannya.
> File ini dibuat untuk dokumentasi tim.

---

## 1. Navbar — Dashboard, Catalog, My Playlist, Login/Sign Up

### Masalah
- Navbar menggunakan link biasa tanpa styling tombol.
- Tidak ada tombol Login untuk user yang sudah punya akun.
- Tombol Sign Up tertulis "Login" di dashboard guest.

### Perbaikan
- **File:** `application/views/templates/nav.php`
- Semua link navbar diubah menjadi `.nav__btn` — tombol persegi dengan `border-radius: 8px`.
- Guest navbar: Logo kiri → Dashboard | Catalog → Login | Sign Up.
- Logged-in navbar: Garis tiga kiri (sidebar toggle) → Search tengah → Logo kanan.
- Sidebar berisi: Avatar profil, Home, Catalog, My Playlist, Sign Out.
- Tombol Login dan Sign Up pakai class `.nav__btn--auth` dan `.nav__btn--accent`.

### CSS baru
- **File:** `public/css/tokens.css`
- `.nav__btn`, `.nav__btn--active`, `.nav__btn--auth`, `.nav__btn--accent`
- `.nav__sidebar-toggle`, `.sidebar`, `.sidebar__menu`
- `.nav--logged-in .nav__inner` (grid: `auto 1fr auto`)

---

## 2. Dashboard — Hapus "Why Create Account?" + Favorites Lengkap

### Masalah
- Dashboard registered masih menampilkan section "Why create a free account?".
- Favorites hanya menampilkan 6 lagu (terbatas).

### Perbaikan
- **File:** `application/views/dashboard/registered_full.php` (baru)
- Dibuat view khusus yang hanya menampilkan: Continue Listening, Your Playlists, Favorite Songs, Trending, Made For You, Discover More.
- **File:** `application/controllers/Dashboard.php`
- `$this->Favorites_model->get_by_user($userId)` tanpa limit (semua favorites).
- View default diubah ke `dashboard/registered_full`.

---

## 3. Login & Register — Connect ke Database

### Masalah
- `Database Error` saat akses pertama.
- File `error_exception.php` dan `error_404.php` tidak ada.
- `Dashboard.php` baris 24: `$this->load->model('User_model')->get_by_id()` salah.

### Perbaikan
- **File:** `application/views/errors/html/error_exception.php` (baru)
- **File:** `application/views/errors/html/error_404.php` (baru)
- **File:** `application/controllers/Dashboard.php` — diubah jadi:
  ```php
  $this->load->model('User_model');
  $data['dashboard_user'] = $this->User_model->get_by_id($userId);
  ```
- **Database:** `laufey_db` dengan tabel dari `schema.sql`.

---

## 4. Playlist Controller + Route

### Masalah
- Route `/playlist` tidak terdaftar (hanya `/playlists`).
- Controller Playlist tidak ada.

### Perbaikan
- **File:** `application/controllers/Playlist.php` (baru) — method: index, view, create, delete, add_song, remove_song, add_song_ajax.
- **File:** `application/config/routes.php` — tambah:
  ```php
  $route['playlist'] = 'playlist/index';
  $route['playlist/(:num)'] = 'playlist/index/$1';
  $route['playlists'] = 'playlist';
  $route['playlists/(:any)'] = 'playlist/$1';
  ```
- **File:** `application/models/Playlist_model.php` — tambah method `count_by_user()`.
- **File:** `application/models/Listen_history_model.php` — tambah `count_by_user()`, `get_by_user()`.

---

## 5. Profile User — Edit Profile, Security, Preferences, Account

### Masalah
- Tidak ada halaman profile user.
- Tidak ada form edit profil, ganti password, preferences.

### Perbaikan
- **File:** `application/controllers/User.php` (baru) — method: index, update_profile, change_password, update_preferences, export_data, logout_all.
- **File:** `application/views/user/profile.php` (baru) — 4 tab:
  - Edit Profile (username, display_name, email, bio, avatar upload)
  - Security (ganti password)
  - Preferences (autoplay, show_activity, email_notifs, theme, language)
  - Account (Export Data, Sign Out All, Back to Dashboard, Sign Out, Delete Account)
- **CSS:** `.profile-hero`, `.profile-section`, `.profile-tabs`, `.profile-form`, `.profile-form__switch`, `.profile-danger`.
- **Routes:** `/profile` → `user`, `/user/(:any)` → `user/$1`.

---

## 6. Bottom Music Player — Play, Pause, Skip, Seek, Queue, Loop

### Masalah
- Tidak ada player bar di bawah.
- Audio tidak bisa seek/geser ke menit tertentu.
- Queue tidak muncul di menu.
- Loop mode tidak ada.
- Cover lagu tidak berubah saat next/prev.

### Perbaikan
- **File:** `application/views/templates/player.php` (baru) — HTML player bar:
  - Info lagu (cover + title + artist) — klik ke detail lagu.
  - Controls: prev, play/pause, next.
  - Progress bar: `click + drag to seek`.
  - Volume slider + mute toggle.
  - Loop button (3 mode).
  - 3-dot menu: Up Next, Add Current to Queue, Add to Playlist, Add to Favorites.
- **File:** `application/controllers/Player.php` — method:
  - `stream($songId)` — streaming file audio.
  - `info($songId)` — JSON info lagu.
  - `random()` — lagu random untuk shuffle.
  - `_stream_file()` — support HTTP Range (206 Partial Content) untuk seek.

### HTTP Range (206 Partial Content) — Kunci Seeker Bisa Jalan
- **Masalah:** `file_get_contents()` baca full file → browser ga bisa seek.
- **Solusi:** `_stream_file()` baca header `HTTP_RANGE`, kirim byte range yang diminta dengan header `206 Partial Content`.
- Browser bisa minta byte dari menit X, server kirim hanya byte itu → lagu pindah ke menit yang diinginkan tanpa restart.

### Seek Bar — Click & Drag
- **Masalah:** `<input type="range">` opacity 0 tidak bisa di-drag.
- **Solusi:** Manual mousedown + mousemove:
  - `mousedown` → visual update + simpan target.
  - `mousemove` → visual update terus.
  - `mouseup` → `audio.currentTime = target * duration` (SEKALI, bukan setiap move).
- **CSS:** `.player__bar-thumb` muncul saat hover.

### Queue — Add to Queue & Up Next
- **Masalah:** `renderQueueInMenu()` pakai variable `currentIndex` yang tidak terdefinisi (typo: harusnya `QUEUE_IDX`).
- **Solusi:** Ganti `currentIndex` → `QUEUE_IDX`. Panggil `renderQueueInMenu()` setiap `addToQueue()` dan setiap buka menu.
- `playSong()` replace QUEUE = [song] (reset). `addToQueue()` push ke QUEUE.

### Loop Mode — 3 Mode
- **Mode 0 (Sequential):** Next berurutan. Prev ke sebelumnya.
- **Mode 1 (Shuffle):** Next random dari QUEUE. Jika QUEUE ≤ 1, ambil random dari catalog via `player/random`.
- **Mode 2 (Repeat One):** Restart lagu yang sama terus.
- **Shuffle no-repeat:** `PLAYED_IDS` array menyimpan ID lagu yang sudah diputar. Server terima `?exclude=1,2,3` → pilih random kecuali yang sudah diputar. Jika semua habis (404) → reset.

### Pre-fetch Shuffle
- **Masalah:** Delay 30 detik saat next shuffle karena XHR.
- **Solusi:** `prefetchShuffle()` dipanggil setelah lagu mulai diputar → ambil lagu shuffle berikutnya di background. `SHUFFLE_NEXT` disimpan. Saat next, langsung pakai tanpa XHR.

---

## 7. PJAX — Navigasi Tanpa Reload (Audio Tetap Jalan)

### Masalah
- Setiap pindah halaman, `<audio>` di-destroy → lagu berhenti.
- Player bar ilang.

### Perbaikan
- **File:** `public/js/pjax.js` (baru) — intercept semua link internal.
- **File:** `application/views/templates/layout.php` — PJAX detection:
  - Normal: render full HTML (nav, content, footer, player, audio).
  - PJAX (`_pjax=1` atau `X-PJAX` header): render hanya `<div id="pjax-content">`.
- **Yang survive navigasi:** nav, sidebar, footer, player, audio (elemen `<audio>` tetap di DOM).
- **Yang diganti:** `#pjax-content` (konten halaman).
- **Fallback:** Jika PJAX gagal → `window.location.href` (full reload).
- **Nav active state:** `updateNav()` setiap PJAX selesai → update class `.nav__btn--active`, `.sidebar__link--active`.
- **Profile tabs:** `initPageScripts()` di pjax.js → bind ulang event listener setelah konten baru di-inject.

---

## 8. Cover Image — Picsum Photos

### Masalah
- Cover tidak muncul karena `file_exists(FCPATH . $cover_path)` gagal untuk URL eksternal (http).

### Perbaikan
- **File:** `application/helpers/cover_helper.php` (baru) — fungsi:
  - `cover_available($path)` → jika mulai "http" return true, else `file_exists(FCPATH . $path)`.
  - `cover_url($path)` → jika "http" return as-is, else `base_url($path)`.
- **File:** `application/config/autoload.php` — tambah `'cover'` ke `$autoload['helper']`.
- **Semua view:** ganti `file_exists(FCPATH . $xxx->cover_path)` → `cover_available($xxx->cover_path)`, `base_url($xxx->cover_path)` → `cover_url($xxx->cover_path)`.

---

## 9. Database — Schema Lengkap

### Database
- **Nama:** `laufey_db`
- **Host:** `localhost` | **User:** `root` | **Password:** `""`

### Tabel (10 + 1 seed)

| # | Tabel | Isi |
|---|-------|-----|
| 1 | `genres` | Daftar genre musik (8 seed: Classical, Jazz, Electronic, dll) |
| 2 | `songs` | Katalog lagu (title, artist, genre_id, file_path, cover_path, duration) |
| 3 | `lyrics` | Lirik per lagu (1:1 dengan songs) |
| 4 | `users` | Akun pengguna (username, email, password_hash, display_name, bio, avatar, role) |
| 5 | `user_prefs` | Preferensi user (autoplay, show_activity, email_notifs, theme, language) |
| 6 | `download_logs` | Log download (untuk limit guest 1/hari) |
| 7 | `playlists` | Playlist user |
| 8 | `playlist_songs` | Pivot playlist ↔ songs |
| 9 | `favorites` | Lagu favorit user |
| 10 | `listen_history` | Riwayat putar (untuk trending, recommendations) |

### File SQL
- **`schema.sql`** — Semua tabel + foreign key + genre seed.
- **`seed_songs.sql`** — 6 lagu sesuai file MP3 yang ada.
- **`seed_covers.sql`** — Update cover_path ke picsum.photos.

---

## 10. File-file Baru

| File | Fungsi |
|------|--------|
| `application/controllers/User.php` | Halaman profile user (edit, password, preferences, account) |
| `application/controllers/Playlist.php` | CRUD playlist |
| `application/controllers/Player.php` | Stream audio, info, random |
| `application/controllers/Favorites.php` | Add/remove favorites via AJAX |
| `application/views/templates/player.php` | Bottom music player bar |
| `application/views/dashboard/registered_full.php` | Dashboard khusus user login |
| `application/views/user/profile.php` | Halaman profile dengan 4 tab |
| `application/views/playlist/index.php` | Daftar playlist |
| `application/views/playlist/detail.php` | Detail playlist dengan lagu |
| `application/views/playlist/create.php` | Form buat playlist baru |
| `application/views/song/detail.php` | Detail lagu (cover, info, play, queue) |
| `application/views/errors/html/error_exception.php` | Template error exception |
| `application/views/errors/html/error_404.php` | Template 404 |
| `application/helpers/cover_helper.php` | Helper cover_available() + cover_url() |
| `public/js/player.js` | Player logic (queue, seek, loop, play/pause) |
| `public/js/pjax.js` | Navigasi AJAX tanpa reload |
| `schema.sql` | Lengkap dengan user_prefs + listen_history |
| `seed_songs.sql` | 6 lagu sesuai file MP3 |
| `seed_covers.sql` | Update cover ke picsum.photos |

---

## 11. Ringkasan Bug yang Pernah Terjadi

| # | Bug | Penyebab | Solusi |
|---|-----|----------|--------|
| 1 | `Call to undefined method CI_Loader::get_by_id()` | Chaining method `$this->load->model(...)->get_by_id()` | Load model dulu, lalu panggil method |
| 2 | `syntax error playlist` | Missing `]` di routes.php | `(:num)]` → `(:num)]` (tambah kurung) |
| 3 | Profile tabs tidak bisa diklik setelah PJAX | `innerHTML` tidak eksekusi `<script>` | Pindahkan init function ke pjax.js, panggil setelah content swap |
| 4 | 3-dot menu ga muncul | Dua event listener click saling tabrak | Satukan jadi 1 handler dengan closest + stopPropagation |
| 5 | Audio restart saat pindah halaman | `<audio>` di-destroy tiap navigasi | PJAX: hanya ganti konten, audio tetap di DOM |
| 6 | Seek bar restart lagu | `currentTime` di-set tiap mousemove | Set `currentTime` hanya di mouseup, visual saja selama drag |
| 7 | Seek tidak bisa sama sekali | `file_get_contents()` tanpa HTTP Range | Implement `_stream_file()` dengan 206 Partial Content |
| 8 | Volume slider thumb oversized | CSS range slider default browser | Set `width: 10px; height: 10px; margin-top: -3px` |
| 9 | Queue tidak muncul di "Up Next" | Typo variable: `currentIndex` → `QUEUE_IDX` | Ganti `currentIndex` dengan `QUEUE_IDX` |
| 10 | Cover gambar tidak muncul | `file_exists()` gagal untuk URL http | Buat `cover_helper.php`: `cover_available()`, `cover_url()` |
| 11 | Shuffle delay 30 detik | XHR async tiap next shuffle | Pre-fetch shuffle di background (`prefetchShuffle()`) |
| 12 | Debug backtrace muncul di halaman error | File `error_exception.php` tidak ada | Buat file template error yang aman |
| 13 | SessionStorage restore tidak jalan | PJAX dulu, lalu berubah ke sessionStorage | Gabung: PJAX untuk navigasi + sessionStorage fallback |
| 14 | Loop icon shuffle tidak muncul | CSS `player__btn--loop` tidak konsisten | Tambah class dan transition |

---

*Terakhir diupdate: 25 Juni 2026*
