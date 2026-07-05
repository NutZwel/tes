<style>
  .song-card .play-overlay { opacity: 0; transition: opacity 0.2s ease; background: rgba(0,0,0,0.15) !important; backdrop-filter: blur(6px); -webkit-backdrop-filter: blur(6px); }
  .song-card:hover .play-overlay { opacity: 1 !important; }
</style>
<section class="container py-4" data-bs-theme="dark">
    <header class="d-flex align-items-center justify-content-between mb-4">
      <h1 class="h3 mb-0"><?= !empty($search_query) ? 'Search: ' . html_escape($search_query) : 'Catalog' ?></h1>
      <p class="text-body-secondary small mb-0"><?= $total_songs ?> track<?= $total_songs !== 1 ? 's' : '' ?></p>
    </header>

    <?php if (!empty($search_query) && !empty($public_playlists)): ?>
    <!-- Public Playlists Results -->
    <div class="mb-5">
      <h2 class="h5 fw-light mb-3" style="font-family:var(--font-display);color:var(--color-ink);">Public Playlists</h2>
      <div class="row row-cols-2 row-cols-sm-3 row-cols-lg-4 row-cols-xl-5 g-3">
        <?php foreach ($public_playlists as $pp): ?>
        <div class="col">
          <a href="<?= base_url('playlist/' . $pp->id) ?>" class="card border-secondary h-100 text-decoration-none transition-card">
            <div class="card-body d-flex flex-column align-items-center text-center gap-2 p-3">
              <div class="d-inline-flex align-items-center justify-content-center rounded-3" style="width:56px;height:56px;background-color:rgba(var(--bs-primary-rgb),0.12);">
                <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="text-primary"><path d="M9 18V5l12-2v13"/><circle cx="6" cy="18" r="3"/><circle cx="18" cy="16" r="3"/></svg>
              </div>
              <div class="min-w-0 w-100">
                <h3 class="card-title h6 mb-1 text-truncate fw-semibold" style="font-family:var(--font-display);color:var(--color-ink);"><?= html_escape($pp->name) ?></h3>
                <?php if ($pp->description): ?>
                <p class="card-text small text-body-secondary text-truncate mb-0"><?= html_escape($pp->description) ?></p>
                <?php endif; ?>
              </div>
            </div>
          </a>
        </div>
        <?php endforeach; ?>
      </div>
    </div>
    <?php endif; ?>

    <?php if (empty($songs)): ?>

      <div class="text-center py-5">
        <div class="mb-3 text-body-secondary" aria-hidden="true">
          <svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1" stroke-linecap="round" stroke-linejoin="round">
            <circle cx="12" cy="12" r="10"/><path d="M16 16s-1.5-2-4-2-4 2-4 2"/><line x1="9" y1="9" x2="9.01" y2="9"/><line x1="15" y1="9" x2="15.01" y2="9"/>
          </svg>
        </div>
        <p class="text-body-secondary mb-3"><?= !empty($search_query) ? 'No tracks found for "' . html_escape($search_query) . '"' : 'No tracks yet — check back soon.' ?></p>
        <a href="<?= base_url('catalog') ?>" class="btn btn-outline-secondary"><?= !empty($search_query) ? 'Browse All' : 'Browse Home' ?></a>
      </div>

    <?php else: ?>

      <div class="row row-cols-2 row-cols-md-3 row-cols-lg-4 g-3">
        <?php foreach ($songs as $song): ?>
        <div class="col">
          <article class="card bg-body-tertiary border-secondary-subtle h-100 song-card" data-song-id="<?= $song->id ?>">
            <div class="position-relative overflow-hidden" style="aspect-ratio: 1;" data-play-now="<?= $song->id ?>">
              <?php if ($song->cover_path && cover_available($song->cover_path)): ?>
                <img src="<?= cover_url($song->cover_path) ?>"
                     alt="<?= html_escape($song->title) ?>"
                     class="w-100 h-100"
                     style="object-fit: cover;"
                     loading="lazy"
                     width="280" height="280">
              <?php else: ?>
                <div class="d-flex align-items-center justify-content-center w-100 h-100">
                  <span class="display-5 fw-bold text-body-secondary"><?= mb_strtoupper(mb_substr($song->title, 0, 1)) ?></span>
                </div>
              <?php endif; ?>
              <div class="position-absolute top-0 start-0 w-100 h-100 d-flex align-items-center justify-content-center text-white play-overlay" style="cursor: pointer;">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                  <polygon points="8,5 19,12 8,19"/>
                </svg>
              </div>
              <?php if ($song->duration_seconds): ?>
                <span class="position-absolute bottom-0 end-0 m-1 small bg-dark bg-opacity-75 text-light px-1 rounded"><?= gmdate('i:s', $song->duration_seconds) ?></span>
              <?php endif; ?>
            </div>
            <div class="card-body">
              <div class="d-flex justify-content-between align-items-start gap-2">
                <div class="overflow-hidden">
                  <a href="<?= base_url('song/' . $song->id) ?>" class="text-decoration-none" style="color: var(--color-ink);">
                    <h3 class="card-title h6 text-truncate mb-0" style="font-family: var(--font-display); font-weight: 600; color: var(--color-ink);"><?= html_escape($song->title) ?></h3>
                  </a>
                  <p class="card-text small text-body-secondary text-truncate mb-0"><?= html_escape($song->artist) ?></p>
                </div>
                <div class="dropdown flex-shrink-0">
                  <button class="btn btn-sm text-body-secondary border-0" aria-label="More options" type="button" data-song-id="<?= $song->id ?>" data-bs-toggle="dropdown" aria-expanded="false">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><circle cx="12" cy="5" r="2"/><circle cx="12" cy="12" r="2"/><circle cx="12" cy="19" r="2"/></svg>
                  </button>
                  <ul class="dropdown-menu dropdown-menu-dark" role="menu">
                    <li><button class="dropdown-item" data-action="queue" data-song-id="<?= $song->id ?>" type="button" role="menuitem">
                      <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" class="me-2"><polyline points="17 1 21 5 17 9"/><path d="M3 11V9a4 4 0 0 1 4-4h14"/><polyline points="7 23 3 19 7 15"/><path d="M21 13v2a4 4 0 0 1-4 4H3"/></svg>
                      Add to Queue
                    </button></li>
                    <li><button class="dropdown-item" data-action="playlist" data-song-id="<?= $song->id ?>" type="button" role="menuitem">
                      <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" class="me-2"><line x1="8" y1="6" x2="21" y2="6"/><line x1="8" y1="12" x2="21" y2="12"/><line x1="8" y1="18" x2="21" y2="18"/><line x1="3" y1="6" x2="3.01" y2="6"/><line x1="3" y1="12" x2="3.01" y2="12"/><line x1="3" y1="18" x2="3.01" y2="18"/></svg>
                      Add to Playlist
                    </button></li>
                    <li><button class="dropdown-item" data-action="favorite" data-song-id="<?= $song->id ?>" type="button" role="menuitem">
                      <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" class="me-2"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/></svg>
                      Add to Favorites
                    </button></li>
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item" href="<?= base_url('download/' . $song->id) ?>">
                      <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" class="me-2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                      Download
                    </a></li>
                  </ul>
                </div>
              </div>
              <?php if ($song->genre_name): ?>
                <span class="badge bg-primary bg-opacity-10 text-primary mt-1"><?= html_escape($song->genre_name) ?></span>
              <?php endif; ?>
            </div>
          </article>
        </div>
        <?php endforeach; ?>
      </div>

      <?php if ($total_pages > 1): ?>
      <nav aria-label="Catalog pages" class="mt-4 d-flex align-items-center justify-content-between">
        <?php if ($current_page > 1): ?>
          <a href="<?= base_url('catalog/index/' . ($current_page - 1)) ?><?= !empty($search_query) ? '?q=' . urlencode($search_query) : '' ?>" class="btn btn-outline-secondary btn-sm d-inline-flex align-items-center gap-1">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="15 18 9 12 15 6"/></svg>
            Previous
          </a>
        <?php else: ?>
          <span class="btn btn-outline-secondary btn-sm d-inline-flex align-items-center gap-1 disabled" aria-disabled="true">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="15 18 9 12 15 6"/></svg>
            Previous
          </span>
        <?php endif; ?>

        <span class="text-body-secondary small">Page <?= $current_page ?> of <?= $total_pages ?></span>

        <?php if ($current_page < $total_pages): ?>
          <a href="<?= base_url('catalog/index/' . ($current_page + 1)) ?><?= !empty($search_query) ? '?q=' . urlencode($search_query) : '' ?>" class="btn btn-outline-secondary btn-sm d-inline-flex align-items-center gap-1">
            Next
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"/></svg>
          </a>
        <?php else: ?>
          <span class="btn btn-outline-secondary btn-sm d-inline-flex align-items-center gap-1 disabled" aria-disabled="true">
            Next
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"/></svg>
          </span>
        <?php endif; ?>
      </nav>
      <?php endif; ?>

    <?php endif; ?>
</section>
