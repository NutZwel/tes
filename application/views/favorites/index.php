<!-- ──────────────────────────────────────────────────────────────
     View: favorites/index
     Menampilkan lagu favorit user sebagai daftar flat yang bisa
     dicari. Setiap baris menampilkan thumbnail cover, judul, artis,
     durasi, dan dropdown dengan aksi queue/playlist/favorite-remove.
     Empty state dengan ikon hati mendorong user browsing katalog
     ketika belum ada favorit. Bergantung pada $favorites dari
     Favorites controller.
     ────────────────────────────────────────────────────────────── -->
<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?>
<section class="catalog">
  <div class="catalog__inner">
    <header class="catalog__head">
      <h1 class="catalog__title">My Favorites</h1>
      <p class="catalog__count"><?= count($favorites) ?> track<?= count($favorites) !== 1 ? 's' : '' ?></p>
    </header>

    <?php if (!empty($favorites)): ?>
      <div class="list-rows">
        <?php foreach ($favorites as $fav): ?>
        <div class="row-card__link" style="display:flex;align-items:center;gap:var(--space-md);padding:var(--space-xs) var(--space-sm);border-radius:var(--radius-md);text-decoration:none;color:inherit;transition:background var(--dur-short) var(--ease-out);"
             data-song-id="<?= (int) $fav->id ?>">
          <!-- Thumbnail cover dengan data-trigger play -->
          <div style="width:48px;height:48px;border-radius:var(--radius-sm);overflow:hidden;flex-shrink:0;background:var(--color-paper-3);cursor:pointer;" data-play-now="<?= (int) $fav->id ?>">
            <?php if ($fav->cover_path && cover_available($fav->cover_path)): ?>
              <img src="<?= cover_url($fav->cover_path) ?>" alt="<?= html_escape($fav->title) ?>" class="w-100 h-100" style="object-fit:cover;display:block;" loading="lazy" width="48" height="48">
            <?php else: ?>
              <div class="w-100 h-100 d-flex align-items-center justify-content-center" style="font-family:var(--font-display);font-size:var(--text-sm);color:var(--color-muted);">
                <?= mb_strtoupper(mb_substr($fav->title, 0, 1)) ?>
              </div>
            <?php endif; ?>
          </div>
          <div style="flex:1;min-width:0;">
            <a href="<?= base_url('song/' . $fav->id) ?>" style="display:block;font-size:var(--text-sm);font-weight:500;color:var(--color-ink);text-decoration:none;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;"><?= html_escape($fav->title) ?></a>
            <span style="display:block;font-size:var(--text-xs);color:var(--color-muted);white-space:nowrap;overflow:hidden;text-overflow:ellipsis;"><?= html_escape($fav->artist) ?></span>
          </div>
          <?php if ($fav->duration_seconds): ?>
            <span style="font-size:var(--text-xs);color:var(--color-neutral);white-space:nowrap;"><?= gmdate('i:s', $fav->duration_seconds) ?></span>
          <?php endif; ?>
          <!-- ─── Dropdown Aksi ───
               Opsi "Remove from Favorites" memicu toggle yang sama
               dengan menambah — JS controller menangani keduanya
               melalui nilai data-action "favorite".
          ─── -->
          <div style="flex-shrink:0;position:relative;">
            <button class="song-card__menu-btn" type="button" aria-label="More" style="display:flex;align-items:center;justify-content:center;width:28px;height:28px;background:transparent;border:none;border-radius:var(--radius-sm);color:var(--color-muted);cursor:pointer;" data-bs-toggle="dropdown" aria-expanded="false">
              <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><circle cx="12" cy="5" r="2"/><circle cx="12" cy="12" r="2"/><circle cx="12" cy="19" r="2"/></svg>
            </button>
            <ul class="dropdown-menu dropdown-menu-dark">
              <li><button class="dropdown-item" data-action="queue" data-song-id="<?= (int) $fav->id ?>" type="button">Add to Queue</button></li>
              <li><button class="dropdown-item" data-action="playlist" data-song-id="<?= (int) $fav->id ?>" type="button">Add to Playlist</button></li>
              <li><button class="dropdown-item" data-action="favorite" data-song-id="<?= (int) $fav->id ?>" type="button">Remove from Favorites</button></li>
            </ul>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
    <?php else: ?>
      <!-- ─── Empty State ───
           Ikon hati + pesan mendorong user browsing katalog
           dan mulai menambahkan favorit.
      ─── -->
      <div class="catalog__empty">
        <div class="catalog__empty-icon" aria-hidden="true">
          <svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/></svg>
        </div>
        <p class="catalog__empty-text">No favorites yet. Start adding songs you love!</p>
        <a href="<?= base_url('catalog') ?>" class="btn btn-primary rounded-pill px-4">Browse Catalog</a>
      </div>
    <?php endif; ?>
  </div>
</section>
