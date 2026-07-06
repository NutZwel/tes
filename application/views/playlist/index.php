<!-- ────────────────────────────────────────────────
     VIEW: playlist/index.php
     Menampilkan daftar playlist milik user dalam grid card.
     Setiap card menampilkan ikon playlist, nama, dan jumlah lagu.
     Jika belum ada playlist, tampilkan empty state dengan tombol
     "Create Playlist". Menerima $playlists dari controller.
     ──────────────────────────────────────────────── -->
<style>
  .transition-card { transition: background-color .15s ease, border-color .15s ease, box-shadow .15s ease; }
  .transition-card:hover { background-color: rgba(var(--bs-secondary-rgb),0.08); border-color: var(--bs-primary); box-shadow: 0 0 0 1px rgba(var(--bs-primary-rgb),0.3); }
</style>
<section class="py-4" id="my-playlists">
  <div class="container">
    <header class="d-flex align-items-center justify-content-between mb-4">
      <h2 class="h3 fw-semibold mb-0">My Playlists</h2>
      <a href="<?= base_url('playlist/create') ?>" class="btn btn-primary btn-sm d-inline-flex align-items-center gap-1">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
        Create New
      </a>
    </header>

    <?php if (!empty($playlists)): ?>
    <div class="row row-cols-2 row-cols-sm-3 row-cols-lg-4 row-cols-xl-5 g-3">
      <?php foreach ($playlists as $pl): ?>
      <div class="col">
        <a href="<?= base_url('playlist/' . $pl->id) ?>" class="card border-secondary h-100 text-decoration-none transition-card">
          <div class="card-body d-flex flex-column align-items-center text-center gap-2 p-3">
            <div class="d-inline-flex align-items-center justify-content-center rounded-3" style="width:56px;height:56px;background-color:rgba(var(--bs-primary-rgb),0.12);">
              <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="text-primary"><path d="M9 18V5l12-2v13"/><circle cx="6" cy="18" r="3"/><circle cx="18" cy="16" r="3"/></svg>
            </div>
            <div class="min-w-0 w-100">
              <h3 class="card-title h6 mb-1 text-truncate fw-semibold" style="font-family:var(--font-display);color:var(--color-ink)"><?= html_escape($pl->name) ?></h3>
              <p class="card-text small text-body-secondary mb-0"><?= (int) $pl->song_count ?> track<?= $pl->song_count !== 1 ? 's' : '' ?></p>
            </div>
          </div>
        </a>
      </div>
      <?php endforeach; ?>
    </div>
    <?php else: ?>
    <!-- Empty state — belum ada playlist -->
    <div class="text-center py-5">
      <div class="mb-3">
        <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1" stroke-linecap="round" stroke-linejoin="round" class="text-body-tertiary"><path d="M9 18V5l12-2v13"/><circle cx="6" cy="18" r="3"/><circle cx="18" cy="16" r="3"/></svg>
      </div>
      <p class="text-body-secondary mb-3">No playlists yet. Create your first one!</p>
      <a href="<?= base_url('playlist/create') ?>" class="btn btn-primary">Create Playlist</a>
    </div>
    <?php endif; ?>
  </div>
</section>
