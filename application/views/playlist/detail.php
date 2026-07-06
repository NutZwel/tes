<!-- ────────────────────────────────────────────────
     VIEW: playlist/detail.php
     Halaman detail playlist dengan hero gradient (banner opsional),
     cover art, metadata (nama, deskripsi, jumlah lagu, total durasi),
     tombol aksi (play all, edit, delete), dan tabel daftar lagu.
     Setiap baris lagu menampilkan nomor, cover, judul, artis, genre,
     durasi, dan dropdown menu untuk queue/playlist/favorite/remove.
     Mendukung empty state ketika tidak ada lagu di playlist.
     ──────────────────────────────────────────────── -->
<section class="text-light" id="pl-detail">
  <style>
    .pl-gradient {
      background: linear-gradient(135deg, #1e3a2e 0%, #1a1a2e 40%, #121212 100%);
    }
    .pl-success { animation: fadeOut 3s forwards; }
    @keyframes fadeOut { 0%{opacity:1} 80%{opacity:1} 100%{opacity:0;height:0;padding:0;margin:0} }
    .pl-banner {
      position: relative;
      overflow: hidden;
    }
    .pl-banner::before {
      content: '';
      position: absolute; inset: 0;
      background-size: cover;
      background-position: center;
      opacity: 0.3;
      pointer-events: none;
    }
    .pl-cover-placeholder {
      background: linear-gradient(135deg, #383838, #121212);
    }
    .track-row {
      transition: background-color .2s ease;
    }
    .track-row:hover {
      background-color: rgba(255,255,255,.06);
    }
    .track-row .track-play {
      display: none !important;
    }
    .track-row:hover .track-number {
      display: none !important;
    }
    .track-row:hover .track-play {
      display: inline-flex !important;
    }
  </style>

  <?php if ($this->session->flashdata('pl_success')): ?>
  <div style="padding:12px 16px;background:color-mix(in oklch, oklch(65% 0.20 145) 10%, transparent);border:1px solid oklch(65% 0.20 145 / .3);border-radius:8px;color:oklch(72% 0.18 145);font-size:var(--text-sm);margin-bottom:16px;text-align:center;">
    <?= $this->session->flashdata('pl_success') ?>
  </div>
  <?php endif; ?>

  <!-- HERO -->
  <div class="pl-gradient<?= $playlist->banner_path ? ' pl-banner' : '' ?>"
       <?php if ($playlist->banner_path): ?>style="position:relative;"<?php endif; ?>>
    <?php if ($playlist->banner_path): ?>
    <div style="position:absolute;inset:0;background:url('<?= base_url($playlist->banner_path) ?>') center/cover;opacity:0.25;pointer-events:none;"></div>
    <?php endif; ?>
    <div class="container py-5" style="position:relative;z-index:1;">
      <div class="row g-4 align-items-end">
        <div class="col-auto">
          <div class="rounded-3 overflow-hidden shadow-lg" style="width:200px;height:200px;min-width:200px;background:#282828;">
            <?php if ($playlist->cover_path && file_exists(FCPATH . $playlist->cover_path)): ?>
              <img src="<?= base_url($playlist->cover_path) ?>" alt="<?= html_escape($playlist->name) ?>" class="img-fluid w-100 h-100" style="object-fit:cover;">
            <?php else: ?>
              <div class="d-flex align-items-center justify-content-center h-100 pl-cover-placeholder">
                <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1" class="text-secondary"><path d="M9 18V5l12-2v13"/><circle cx="6" cy="18" r="3"/><circle cx="18" cy="16" r="3"/></svg>
              </div>
            <?php endif; ?>
          </div>
        </div>
        <div class="col">
          <p class="text-uppercase small fw-bold mb-2" style="letter-spacing:.08em;color:#b3b3b3;">Playlist</p>
          <h1 class="display-5 fw-bold mb-2" style="font-family:var(--font-display)"><?= html_escape($playlist->name) ?></h1>
          <?php if ($playlist->description): ?>
            <p class="mb-2" style="color:#b3b3b3;"><?= html_escape($playlist->description) ?></p>
          <?php endif; ?>
          <div class="d-flex flex-wrap align-items-center gap-2 small" style="color:#b3b3b3;">
            <span class="fw-bold text-white"><?= count($playlist->songs) ?> songs</span>
            <?php if ($total_duration > 0): ?>
              <span>·</span>
              <span><?= gmdate('H:i', $total_duration) ?> hr <?= gmdate('i:s', $total_duration % 3600) ?></span>
            <?php endif; ?>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- TOMBOL AKSI -->
  <div class="container py-3">
    <div class="d-flex gap-3 align-items-center">
      <button class="btn btn-success rounded-circle d-flex align-items-center justify-content-center p-0 shadow" style="width:56px;height:56px;" data-play-list="<?= $playlist->id ?>">
        <svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor"><polygon points="8,5 19,12 8,19"/></svg>
      </button>
      <a href="<?= base_url('playlist/edit/' . $playlist->id) ?>" class="btn btn-outline-light rounded-pill px-4" title="Edit playlist">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="me-2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
        Edit
      </a>
      <a href="<?= base_url('playlist/delete/' . $playlist->id) ?>" class="btn btn-outline-danger rounded-pill px-3" data-confirm="delete-playlist" title="Delete playlist">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
      </a>
    </div>
  </div>

  <!-- TABEL LAGU -->
  <div class="container pb-5" style="min-height:50vh;">
    <?php if (!empty($playlist->songs)): ?>
    <div class="table-responsive">
      <table class="table table-dark table-hover align-middle mb-0">
        <thead>
          <tr class="text-secondary small text-uppercase" style="border-bottom:1px solid rgba(255,255,255,.1);">
            <th scope="col" style="width:40px">#</th>
            <th scope="col">Title</th>
            <th scope="col" class="d-none d-md-table-cell">Artist</th>
            <th scope="col" class="d-none d-lg-table-cell">Genre</th>
            <th scope="col" style="width:60px">
              <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
            </th>
            <th scope="col" style="width:40px"></th>
          </tr>
        </thead>
        <tbody>
          <?php $i = 0; ?>
          <?php foreach ($playlist->songs as $song): ?>
          <?php $i++; ?>
          <tr class="track-row" data-song-id="<?= $song->id ?>" style="border-bottom:1px solid rgba(255,255,255,.05);">
            <td class="align-middle text-secondary" style="width:40px;">
              <span class="track-number"><?= $i ?></span>
              <button class="track-play btn btn-link text-white p-0 border-0 align-items-center justify-content-center" data-play-now="<?= $song->id ?>" title="Play">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><polygon points="8,5 19,12 8,19"/></svg>
              </button>
            </td>
            <td>
              <div class="d-flex align-items-center gap-2">
                <div>
                  <?php if ($song->cover_path && cover_available($song->cover_path)): ?>
                    <img src="<?= cover_url($song->cover_path) ?>" alt="" width="40" height="40" class="rounded">
                  <?php else: ?>
                    <div class="bg-secondary d-inline-flex align-items-center justify-content-center rounded" style="width:40px;height:40px;">
                      <span class="small fw-bold text-secondary"><?= mb_strtoupper(mb_substr($song->title, 0, 1)) ?></span>
                    </div>
                  <?php endif; ?>
                </div>
                <a href="<?= base_url('song/' . $song->id) ?>" class="text-white text-decoration-none fw-semibold" style="font-family:var(--font-display)"><?= html_escape($song->title) ?></a>
              </div>
            </td>
            <td class="d-none d-md-table-cell text-secondary"><?= html_escape($song->artist) ?></td>
            <td class="d-none d-lg-table-cell text-secondary"><?= html_escape($song->genre_name ?: '—') ?></td>
            <td class="text-secondary"><?= $song->duration_seconds ? gmdate('i:s', $song->duration_seconds) : '—' ?></td>
            <td>
              <!-- Dropdown aksi per lagu di dalam playlist -->
              <div class="dropdown" style="position:static;">
                <button class="btn btn-link text-secondary p-0" data-bs-toggle="dropdown" aria-label="More" type="button">
                  <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><circle cx="12" cy="5" r="2"/><circle cx="12" cy="12" r="2"/><circle cx="12" cy="19" r="2"/></svg>
                </button>
                <ul class="dropdown-menu dropdown-menu-dark" style="position:absolute;inset:auto auto 0 0;transform:none;">
                  <li><button class="dropdown-item" data-action="queue" data-song-id="<?= $song->id ?>" type="button">Add to Queue</button></li>
                  <li><button class="dropdown-item" data-action="playlist" data-song-id="<?= $song->id ?>" type="button">Add to Playlist</button></li>
                  <li><button class="dropdown-item" data-action="favorite" data-song-id="<?= $song->id ?>" type="button">Add to Favorites</button></li>
                  <li><hr class="dropdown-divider"></li>
                  <li><button class="dropdown-item text-danger" data-action="remove" data-song-id="<?= $song->id ?>" type="button">Remove from Playlist</button></li>
                </ul>
              </div>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
    <?php else: ?>
    <!-- Empty state — playlist kosong -->
    <div class="text-center py-5">
      <div class="mb-4">
        <svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="0.5" stroke-linecap="round" stroke-linejoin="round" class="text-secondary"><path d="M9 18V5l12-2v13"/><circle cx="6" cy="18" r="3"/><circle cx="18" cy="16" r="3"/></svg>
      </div>
      <h4 class="fw-bold mb-2">This playlist is empty</h4>
      <p class="text-secondary mb-4">Add songs from the catalog to get started.</p>
      <a href="<?= base_url('catalog') ?>" class="btn btn-primary rounded-pill px-4">Browse Catalog</a>
    </div>
    <?php endif; ?>
  </div>
</section>
