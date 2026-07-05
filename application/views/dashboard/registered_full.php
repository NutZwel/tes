<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?>

<!-- ═══════════════════════════════
     ── Registered Hero ──
     ═══════════════════════════════ -->
<section class="py-5" id="rhero">
  <div class="container">
    <div class="row align-items-center gx-4 gy-3">
      <div class="col-lg-8">
        <span class="small text-secondary mb-2 d-block">
          Welcome back<?php if (isset($dashboard_user)): ?>, <?= html_escape($dashboard_user->display_name ?: $dashboard_user->username) ?><?php endif; ?>
        </span>
        <h1 class="display-5 fw-light mb-2" style="font-family:var(--font-display);letter-spacing:-0.03em">
          Continue where you left off
        </h1>
        <p class="fs-5 mb-4" style="color:var(--color-muted);max-width:48ch">
          Your library, your playlists, your favorites — all in one place.
        </p>
        <div class="d-flex gap-2 flex-wrap">
          <a href="<?= base_url('catalog') ?>" class="btn btn-primary rounded-pill px-4">Browse Catalog</a>
          <a href="<?= base_url('playlist') ?>" class="btn btn-outline-light rounded-pill px-4">My Playlists</a>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- ═══════════════════════════════
     ── Continue Listening ──
     ═══════════════════════════════ -->
<?php if (!empty($recent_listens)): ?>
<section class="pb-4 pt-2" id="continue-listening">
  <div class="container">
    <header class="d-flex align-items-baseline gap-3 mb-4">
      <h2 class="h2 fw-light mb-0" style="font-family:var(--font-display)">Continue Listening</h2>
      <a href="<?= base_url('profile/listening') ?>" class="btn btn-outline-secondary btn-sm rounded-pill ms-auto d-inline-flex align-items-center gap-1 flex-shrink-0">
        View all
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"/></svg>
      </a>
    </header>
    <div class="carousel__wrap position-relative" style="padding:0;">
      <div class="carousel__track d-flex" style="gap:16px;">
      <?php foreach ($recent_listens as $song): $sid = (int) $song->id; ?>
      <?php $cover = $song->cover_path && cover_available($song->cover_path) ? cover_url($song->cover_path) : null; ?>
      <article class="carousel__card card flex-shrink-0 overflow-hidden border-secondary" style="flex:0 0 200px;background:var(--color-paper-2);">
        <div class="position-relative" style="cursor:pointer;" onclick="playSongById(<?= $sid ?>);"  data-song-id="<?= $sid ?>">
          <div class="position-relative overflow-hidden" style="aspect-ratio:1;background:var(--color-paper-3);">
            <?php if ($cover): ?>
              <img src="<?= $cover ?>" alt="<?= html_escape($song->title) ?>" class="w-100 h-100 object-fit-cover d-block" loading="lazy" width="200" height="200">
            <?php else: ?>
              <div class="w-100 h-100 d-flex align-items-center justify-content-center" style="background:linear-gradient(135deg,var(--color-paper-2),var(--color-paper-3))">
                <span class="display-4 fw-light text-secondary"><?= mb_strtoupper(mb_substr($song->title, 0, 1)) ?></span>
              </div>
            <?php endif; ?>
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
    </div>
  </div>
</section>
<?php endif; ?>

<!-- ═══════════════════════════════
     ── Made For You / Recommendations ──
     ═══════════════════════════════ -->
<?php if (!empty($recommendations)): ?>
<section class="py-4" id="for-you">
  <div class="container">
    <header class="d-flex align-items-baseline gap-3 mb-4">
      <h2 class="h2 fw-light mb-0" style="font-family:var(--font-display)">Made For You</h2>
      <span class="badge bg-primary bg-opacity-10 text-primary rounded-pill fw-normal ms-auto">Based on your listening</span>
    </header>
    <div class="carousel__wrap position-relative" style="padding:0;">
      <div class="carousel__track d-flex" style="gap:16px;">
      <?php foreach ($recommendations as $song): $rsid = (int) $song->id; ?>
      <?php $rcover = $song->cover_path && cover_available($song->cover_path) ? cover_url($song->cover_path) : null; ?>
      <article class="carousel__card card flex-shrink-0 overflow-hidden border-secondary" style="flex:0 0 200px;background:var(--color-paper-2);">
        <div class="position-relative" style="cursor:pointer;" onclick="playSongById(<?= $rsid ?>);"  data-song-id="<?= $rsid ?>">
          <div class="position-relative overflow-hidden" style="aspect-ratio:1;background:var(--color-paper-3);">
          <?php if ($rcover): ?>
            <img src="<?= $rcover ?>" alt="<?= html_escape($song->title) ?>" class="w-100 h-100 object-fit-cover d-block" loading="lazy" width="200" height="200">
          <?php else: ?>
            <div class="w-100 h-100 d-flex align-items-center justify-content-center" style="background:linear-gradient(135deg,var(--color-paper-2),var(--color-paper-3))">
              <span class="display-4 fw-light text-secondary"><?= mb_strtoupper(mb_substr($song->title, 0, 1)) ?></span>
            </div>
          <?php endif; ?>
          <?php if ($song->genre_name): ?>
            <span class="position-absolute bottom-0 start-0 small fw-medium m-1 px-1" style="line-height:1.5;background:rgba(0,0,0,0.65);border-radius:var(--radius-sm,4px);color:#fff;"><?= html_escape($song->genre_name) ?></span>
          <?php endif; ?>
        </div>
        <div class="card-body p-2">
          <div class="d-flex justify-content-between align-items-start gap-1">
            <div class="overflow-hidden min-w-0 flex-grow-1">
              <h3 class="h6 text-truncate mb-0" style="font-family:var(--font-display);font-weight:600;color:var(--color-ink);"><?= html_escape($song->title) ?></h3>
              <p class="small text-truncate mt-1 mb-0" style="color:var(--color-muted);"><?= html_escape($song->artist) ?></p>
            </div>
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
      </article>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<?php endif; ?>

<!-- ═══════════════════════════════
     ── Discover More (Catalog Preview) ──
     ═══════════════════════════════ -->
<section class="py-4" id="preview">
  <div class="container">
    <header class="d-flex align-items-baseline gap-3 mb-4">
      <h2 class="h2 fw-light mb-0" style="font-family:var(--font-display)">Discover more</h2>
      <a href="<?= base_url('catalog') ?>" class="btn btn-outline-secondary btn-sm rounded-pill ms-auto d-inline-flex align-items-center gap-1 flex-shrink-0">
        View More
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"/></svg>
      </a>
    </header>

    <?php if (!empty($reg_preview_songs)): ?>
    <div class="carousel position-relative" role="region" aria-label="Catalog preview">
      <button class="carousel__arrow carousel__arrow--prev btn btn-outline-secondary rounded-circle border-0 position-absolute top-50 start-0 translate-middle-y z-3 d-flex align-items-center justify-content-center"
              aria-label="Previous tracks" type="button"
              style="width:48px;height:48px">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="15 18 9 12 15 6"/></svg>
      </button>
      <button class="carousel__arrow carousel__arrow--next btn btn-outline-secondary rounded-circle border-0 position-absolute top-50 end-0 translate-middle-y z-3 d-flex align-items-center justify-content-center"
              aria-label="Next tracks" type="button"
              style="width:48px;height:48px">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"/></svg>
      </button>

      <div class="carousel__wrap position-relative" style="padding:0 var(--space-xl,2.5rem);">
        <div class="carousel__track d-flex" style="gap:16px;">
          <?php foreach ($reg_preview_songs as $song):
            $cover = $song->cover_path && cover_available($song->cover_path)
              ? cover_url($song->cover_path)
              : null;
            $initial = mb_strtoupper(mb_substr($song->title, 0, 1));
          ?>
          <article class="carousel__card card flex-shrink-0 overflow-hidden border-secondary"
                   style="flex:0 0 calc((100% - 3 * 16px) / 4);min-width:0;background:var(--color-paper-2)">
            <div class="text-decoration-none text-light" data-song-id="<?= $song->id ?>">
              <div class="carousel__art position-relative overflow-hidden"
                   style="aspect-ratio:1;background:var(--color-paper-3)<?php if ($cover): ?>;--glow-img:url('<?= $cover ?>')<?php endif; ?>">
                <?php if ($cover): ?>
                  <img src="<?= $cover ?>" alt="<?= html_escape($song->title) ?>" class="carousel__img w-100 h-100 object-fit-cover" loading="lazy" width="280" height="280">
                <?php else: ?>
                  <div class="carousel__placeholder"><span class="carousel__initial"><?= $initial ?></span></div>
                <?php endif; ?>
                <div class="carousel__overlay">
                  <svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><polygon points="8,5 19,12 8,19"/></svg>
                </div>
                <?php if ($song->duration_seconds): ?>
                  <span class="carousel__duration"><?= gmdate('i:s', $song->duration_seconds) ?></span>
                <?php endif; ?>
              </div>
              <div class="carousel__body">
                <div class="d-flex justify-content-between align-items-start gap-1">
                  <div class="overflow-hidden min-w-0 flex-grow-1">
                    <h3 class="carousel__title"><?= html_escape($song->title) ?></h3>
                    <p class="carousel__artist"><?= html_escape($song->artist) ?></p>
                  </div>
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
        </div>
      </div>
    </div>
    <?php else: ?>
    <div class="text-center py-5">
      <p class="fs-5 mb-4" style="color:var(--color-muted)">No tracks yet — check back soon.</p>
      <a href="<?= base_url() ?>" class="btn btn-primary rounded-pill">Browse Home</a>
    </div>
    <?php endif; ?>
  </div>
</section>

<!-- ═══════════════════════════════
     ── Your Playlists ──
     ═══════════════════════════════ -->
<section class="py-4" id="playlists">
  <div class="container">
    <header class="d-flex align-items-baseline gap-3 mb-4">
      <h2 class="h2 fw-light mb-0" style="font-family:var(--font-display)">Your Playlists</h2>
      <a href="<?= base_url('playlist/create') ?>" class="btn btn-outline-secondary btn-sm rounded-pill ms-auto d-inline-flex align-items-center gap-1 flex-shrink-0">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
        Create New
      </a>
    </header>
    <?php if (!empty($playlists)): ?>
    <div class="row g-3">
      <?php foreach ($playlists as $pl): ?>
      <div class="col-6 col-md-4 col-lg-3 col-xl-2">
        <article class="card border-secondary h-100 overflow-hidden" style="background:var(--color-paper-2)">
          <div class="d-flex flex-column h-100">
            <a href="<?= base_url('playlist/' . $pl->id) ?>" class="text-decoration-none text-light d-flex flex-column align-items-center text-center p-4 flex-grow-1">
              <?php if (!empty($pl->cover_path) && file_exists(FCPATH . $pl->cover_path)): ?>
                <img src="<?= base_url($pl->cover_path) ?>" alt="<?= html_escape($pl->name) ?>" style="width:120px;height:120px;border-radius:10px;object-fit:cover;margin-bottom:12px;box-shadow:0 4px 12px rgba(0,0,0,.4);" width="120" height="120">
              <?php else: ?>
                <div class="d-flex align-items-center justify-content-center rounded mb-3" style="width:120px;height:120px;background:var(--color-paper-3);color:var(--color-accent,var(--bs-primary))">
                  <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M9 18V5l12-2v13"/><circle cx="6" cy="18" r="3"/><circle cx="18" cy="16" r="3"/></svg>
                </div>
              <?php endif; ?>
              <h3 class="h6 text-truncate w-100 mb-1" style="font-family:var(--font-display);font-weight:600;color:var(--color-ink);"><?= html_escape($pl->name) ?></h3>
              <p class="small text-secondary mb-0"><?= (int) $pl->song_count ?> track<?= $pl->song_count !== 1 ? 's' : '' ?></p>
            </a>
            <div class="d-flex border-top border-secondary" style="border-color:var(--color-rule)!important;">
              <a href="<?= base_url('playlist/edit/' . $pl->id) ?>" class="btn btn-sm btn-outline-light border-0 rounded-0 flex-fill py-2" title="Edit playlist">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
              </a>
              <a href="<?= base_url('playlist/delete/' . $pl->id) ?>" class="btn btn-sm btn-outline-danger border-0 rounded-0 flex-fill py-2" title="Delete playlist" onclick="return confirm('Delete this playlist?')">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
              </a>
            </div>
          </div>
        </article>
      </div>
      <?php endforeach; ?>
    </div>
    <?php else: ?>
    <div class="text-center py-5">
      <p class="fs-5 mb-4" style="color:var(--color-muted)">No playlists yet. Create your first one!</p>
    </div>
    <?php endif; ?>
  </div>
</section>

<!-- ═══════════════════════════════
     ── Favorite Songs ──
     ═══════════════════════════════ -->
<section class="py-4" id="favorites">
  <div class="container">
    <header class="d-flex align-items-baseline gap-3 mb-4">
      <h2 class="h2 fw-light mb-0" style="font-family:var(--font-display)">Favorite Songs</h2>
      <span class="small text-secondary ms-auto"><?= !empty($favorites) ? count($favorites) . ' tracks' : 'No favorites yet' ?></span>
    </header>
    <?php if (!empty($favorites)): ?>
    <div class="list-group list-group-flush">
      <?php foreach ($favorites as $fav): ?>
      <a href="#" class="list-group-item list-group-item-action d-flex align-items-center gap-3 px-0 bg-transparent border-secondary text-light" data-song-id="<?= $fav->id ?>">
        <div class="flex-shrink-0 overflow-hidden rounded" style="width:48px;height:48px;background:var(--color-paper-3)">
          <?php if ($fav->cover_path && cover_available($fav->cover_path)): ?>
            <img src="<?= cover_url($fav->cover_path) ?>" alt="<?= html_escape($fav->title) ?>" class="w-100 h-100 object-fit-cover" loading="lazy" width="48" height="48">
          <?php else: ?>
            <div class="w-100 h-100 d-flex align-items-center justify-content-center fw-bold text-secondary"><?= mb_strtoupper(mb_substr($fav->title, 0, 1)) ?></div>
          <?php endif; ?>
        </div>
        <div class="flex-grow-1 min-w-0">
          <div class="text-truncate fw-semibold" style="color:var(--color-ink);"><?= html_escape($fav->title) ?></div>
          <div class="text-truncate small" style="color:var(--color-muted);"><?= html_escape($fav->artist) ?></div>
        </div>
        <?php if ($fav->duration_seconds): ?>
        <span class="small" style="color:var(--color-muted);flex-shrink:0;"><?= gmdate('i:s', $fav->duration_seconds) ?></span>
        <?php endif; ?>
      </a>
      <?php endforeach; ?>
    </div>
    <?php else: ?>
    <div class="text-center py-5">
      <p class="fs-5 mb-4" style="color:var(--color-muted)">No favorites yet. Start adding songs you love!</p>
      <a href="<?= base_url('catalog') ?>" class="btn btn-primary rounded-pill">Browse Catalog</a>
    </div>
    <?php endif; ?>
  </div>
</section>

<!-- ═══════════════════════════════
     ── Trending ──
     ═══════════════════════════════ -->
<?php if (!empty($trending)): ?>
<section class="py-4" id="trending">
  <div class="container">
    <header class="d-flex align-items-baseline gap-3 mb-4">
      <h2 class="h2 fw-light mb-0" style="font-family:var(--font-display)">Trending Now</h2>
      <span class="badge bg-primary bg-opacity-10 text-primary rounded-pill fw-normal ms-auto">Popular this week</span>
    </header>
    <div class="carousel__wrap position-relative" style="padding:0;">
      <div class="carousel__track d-flex" style="gap:16px;">
      <?php foreach ($trending as $song): $tsid = (int) $song->id; ?>
      <?php $tcover = $song->cover_path && cover_available($song->cover_path) ? cover_url($song->cover_path) : null; ?>
      <article class="carousel__card card flex-shrink-0 overflow-hidden border-secondary" style="flex:0 0 200px;background:var(--color-paper-2);">
        <div class="position-relative" style="cursor:pointer;" onclick="playSongById(<?= $tsid ?>);"  data-song-id="<?= $tsid ?>">
          <div class="position-relative overflow-hidden" style="aspect-ratio:1;background:var(--color-paper-3);">
          <?php if ($tcover): ?>
            <img src="<?= $tcover ?>" alt="<?= html_escape($song->title) ?>" class="w-100 h-100 object-fit-cover d-block" loading="lazy" width="200" height="200">
          <?php else: ?>
            <div class="w-100 h-100 d-flex align-items-center justify-content-center" style="background:linear-gradient(135deg,var(--color-paper-2),var(--color-paper-3))">
              <span class="display-4 fw-light text-secondary"><?= mb_strtoupper(mb_substr($song->title, 0, 1)) ?></span>
            </div>
          <?php endif; ?>
          <?php if ($song->play_count): ?>
            <span class="position-absolute bottom-0 start-0 small fw-medium m-1 px-1" style="line-height:1.5;background:rgba(0,0,0,0.65);border-radius:var(--radius-sm,4px);color:#fff;"><?= (int) $song->play_count ?> plays</span>
          <?php endif; ?>
        </div>
        <div class="card-body p-2">
          <div class="d-flex justify-content-between align-items-start gap-1">
            <div class="overflow-hidden min-w-0 flex-grow-1">
              <h3 class="h6 text-truncate mb-0" style="font-family:var(--font-display);font-weight:600;color:var(--color-ink);"><?= html_escape($song->title) ?></h3>
              <p class="small text-truncate mt-1 mb-0" style="color:var(--color-muted);"><?= html_escape($song->artist) ?></p>
            </div>
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
      </article>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<?php endif; ?>
