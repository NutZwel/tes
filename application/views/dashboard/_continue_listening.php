<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?>
<!-- ──────────────────────────────────────────────────────────────
     View: dashboard/_continue_listening
     Partial yang merender satu kartu scroll horizontal untuk
     carousel "Continue Listening". Setiap kartu menampilkan
     cover (dengan fallback inisial), badge durasi, dan indikator
     "Now Playing" yang ditampilkan oleh JS player saat lagu
     menjadi aktif. Setiap kartu memicu playSongById() saat diklik.
     ────────────────────────────────────────────────────────────── -->
<?php foreach ($recent_listens as $song): $sid = (int) $song->id; ?>
<?php $cover = $song->cover_path && cover_available($song->cover_path) ? cover_url($song->cover_path) : null; ?>
<article class="card flex-shrink-0 overflow-hidden border-secondary continue-listening-card" style="flex:0 0 200px;scroll-snap-align:start;background:var(--color-paper-2);" data-song-id="<?= $sid ?>">
  <div class="position-relative" style="cursor:pointer;" onclick="playSongById(<?= $sid ?>);">
    <div class="position-relative overflow-hidden" style="aspect-ratio:1;background:var(--color-paper-3);">
      <?php if ($cover): ?>
        <img src="<?= $cover ?>" alt="<?= html_escape($song->title) ?>" class="w-100 h-100 object-fit-cover d-block" loading="lazy" width="200" height="200">
      <?php else: ?>
        <!-- Fallback: gradient box dengan huruf pertama ketika tidak ada cover -->
        <div class="w-100 h-100 d-flex align-items-center justify-content-center" style="background:linear-gradient(135deg,var(--color-paper-2),var(--color-paper-3))">
          <span class="display-4 fw-light text-secondary"><?= mb_strtoupper(mb_substr($song->title, 0, 1)) ?></span>
        </div>
      <?php endif; ?>
      <!-- Overlay "Now Playing" — tersembunyi secara default; dimunculkan
           oleh JS ketika lagu ini menjadi track aktif -->
      <div class="continue-listening-indicator" style="display:none;position:absolute;inset:0;background:rgba(0,0,0,0.45);z-index:5;align-items:center;justify-content:center;flex-direction:column;gap:8px;backdrop-filter:blur(4px);">
        <div class="bars-box"><span class="bar bar1"></span><span class="bar bar2"></span><span class="bar bar3"></span><span class="bar bar4"></span></div>
        <span style="font-size:10px;font-weight:600;text-transform:uppercase;letter-spacing:.06em;color:#fff;text-shadow:0 1px 4px rgba(0,0,0,.6);">Now Playing</span>
      </div>
      <?php if ($song->duration_seconds): ?>
        <span class="position-absolute bottom-0 end-0 m-1 small fw-medium px-1" style="line-height:1.4;background:rgba(0,0,0,0.65);border-radius:var(--radius-sm,4px);color:#fff;"><?= gmdate('i:s', $song->duration_seconds) ?></span>
      <?php endif; ?>
    </div>
    <div class="card-body p-2">
      <div class="d-flex justify-content-between align-items-start gap-1">
        <div class="overflow-hidden min-w-0 flex-grow-1">
          <h3 class="h6 text-truncate mb-0" style="font-family:var(--font-display);font-weight:600;color:var(--color-ink);"><?= html_escape($song->title) ?></h3>
          <p class="small text-truncate mt-1 mb-0" style="color:var(--color-muted);"><?= html_escape($song->artist) ?></p>
        </div>
        <!-- Dropdown aksi cepat -->
        <div class="dropdown flex-shrink-0">
          <button class="btn btn-sm text-secondary p-0 border-0 bg-transparent" style="line-height:1;" data-bs-toggle="dropdown" aria-label="More" type="button">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><circle cx="12" cy="5" r="2"/><circle cx="12" cy="12" r="2"/><circle cx="12" cy="19" r="2"/></svg>
          </button>
          <ul class="dropdown-menu dropdown-menu-dark">
            <li><button class="dropdown-item" data-action="queue" data-song-id="<?= $song->id ?>" type="button">Add to Queue</button></li>
            <li><button class="dropdown-item" data-action="playlist" data-song-id="<?= $song->id ?>" type="button">Add to Playlist</button></li>
            <li><button class="dropdown-item" data-action="favorite" data-song-id="<?= $song->id ?>" type="button">Add to Favorites</button></li>
          </ul>
        </div>
      </div>
    </div>
  </div>
</article>
<?php endforeach; ?>
