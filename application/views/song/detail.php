<!-- ────────────────────────────────────────────────
     VIEW: song/detail.php
     Halaman detail lengkap untuk satu lagu.
     Kolom kiri menampilkan cover art (atau inisial fallback)
     dan deskripsi "About This Song". Kolom kanan menampilkan
     judul, artis, badge genre/durasi, tombol aksi (play, queue,
     download, menu lainnya), bio artis, dan daftar lagu serupa.
     ──────────────────────────────────────────────── -->

<section class="py-5" id="song-detail" data-bs-theme="dark">
  <div class="container">
    <div class="row g-4">

      <!-- ═══ KOLOM KIRI: Cover + About ═══ -->
      <div class="col-lg-5">

        <?php if ($song->cover_path && cover_available($song->cover_path)): ?>
          <!-- Cover image yang bisa diklik untuk memulai pemutaran -->
          <div data-play-now="<?= $song->id ?>" style="cursor:pointer;">
            <img src="<?= cover_url($song->cover_path) ?>"
                 alt="<?= html_escape($song->title) ?>"
                 class="w-100 rounded-3 shadow"
                 loading="lazy"
                 width="300" height="300"
                 style="aspect-ratio:1;object-fit:cover;pointer-events:none;">
          </div>
        <?php else: ?>
          <!-- Placeholder fallback ketika tidak ada cover: huruf pertama judul -->
          <div class="d-flex align-items-center justify-content-center bg-body-tertiary rounded-3"
               style="aspect-ratio:1;max-width:100%;cursor:pointer;" data-play-now="<?= $song->id ?>">
            <span class="display-1 fw-bold text-body-secondary" style="pointer-events:none;">
              <?= mb_strtoupper(mb_substr($song->title, 0, 1)) ?>
            </span>
          </div>
        <?php endif; ?>

        <?php if ($song->description): ?>
          <div class="mt-4">
            <h2 class="h5 fw-semibold mb-3" style="font-family:var(--font-display)">About This Song</h2>
            <p class="small text-body-secondary lh-lg mb-0">
              <?= nl2br(html_escape($song->description)) ?>
            </p>
          </div>
        <?php endif; ?>

      </div>

      <!-- ═══ KOLOM KANAN: Info + Aksi + Detail ═══ -->
      <div class="col-lg-7">

        <h1 class="fw-light mb-1" style="font-family: var(--font-display);"><?= html_escape($song->title) ?></h1>
        <p class="fs-4 text-body-secondary mb-2"><?= html_escape($song->artist) ?></p>

        <div class="d-flex gap-2 mb-3">
          <?php if ($song->genre_name): ?>
            <span class="badge bg-primary bg-opacity-10 text-primary">
              <?= html_escape($song->genre_name) ?>
            </span>
          <?php endif; ?>
          <?php if ($song->duration_seconds): ?>
            <!-- Format durasi mm:ss dari nilai seconds -->
            <span class="badge bg-primary bg-opacity-10 text-primary">
              <?= gmdate('i:s', $song->duration_seconds) ?>
            </span>
          <?php endif; ?>
        </div>

        <div class="d-flex gap-2 align-items-center mb-4">
          <button class="btn btn-primary rounded-pill" data-play-now="<?= $song->id ?>">Play Now</button>
          <button class="btn btn-outline-light rounded-pill" data-queue-now="<?= $song->id ?>">Add to Queue</button>
          <!-- Link download langsung — controller menangani batas guest -->
          <a href="<?= base_url('download/' . $song->id) ?>" class="btn btn-outline-light rounded-pill d-inline-flex align-items-center gap-1">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
            Download
          </a>
          <!-- Overflow menu untuk aksi tambahan -->
          <div class="dropdown">
            <button class="btn btn-outline-light rounded-pill" type="button"
                    data-bs-toggle="dropdown" aria-expanded="false" aria-label="More options">
              <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor">
                <circle cx="12" cy="5" r="2"/><circle cx="12" cy="12" r="2"/><circle cx="12" cy="19" r="2"/>
              </svg>
            </button>
            <ul class="dropdown-menu dropdown-menu-dark">
              <li><button class="dropdown-item" data-action="queue" data-song-id="<?= $song->id ?>" type="button">Add to Queue</button></li>
              <li><button class="dropdown-item" data-action="playlist" data-song-id="<?= $song->id ?>" type="button">Add to Playlist</button></li>
              <li><button class="dropdown-item" data-action="favorite" data-song-id="<?= $song->id ?>" type="button">Add to Favorites</button></li>
              <li><hr class="dropdown-divider"></li>
              <li><a class="dropdown-item" href="<?= base_url('download/' . $song->id) ?>">Download</a></li>
            </ul>
          </div>
        </div>

        <?php if ($song->artist_bio): ?>
          <div class="card bg-body-tertiary border-secondary p-3 mb-4">
            <h2 class="h5 fw-semibold mb-3" style="font-family:var(--font-display)">About <?= html_escape($song->artist) ?></h2>
            <p class="small text-body-secondary lh-lg mb-0">
              <?= nl2br(html_escape($song->artist_bio)) ?>
            </p>
          </div>
        <?php endif; ?>

        <?php if (!empty($similar)): ?>
          <div class="mt-4">
            <h2 class="h5 fw-semibold mb-3" style="font-family:var(--font-display)">Similar Songs</h2>
            <div class="list-group list-group-flush">
              <!-- Lagu serupa (genre sama, mengecualikan lagu saat ini), masing-masing mengarah ke halaman detail sendiri -->
              <?php foreach ($similar as $s): ?>
              <a href="<?= base_url('song/' . $s->id) ?>"
                 class="list-group-item bg-transparent text-light border-secondary d-flex align-items-center gap-3"
                 data-song-id="<?= $s->id ?>">
                <div class="flex-shrink-0">
                  <?php if ($s->cover_path && cover_available($s->cover_path)): ?>
                    <img src="<?= cover_url($s->cover_path) ?>"
                         alt="<?= html_escape($s->title) ?>"
                         class="rounded-2" loading="lazy" width="48" height="48"
                         style="object-fit:cover;">
                  <?php else: ?>
                    <!-- Inisial fallback untuk lagu serupa tanpa cover -->
                    <div class="d-flex align-items-center justify-content-center bg-body-tertiary rounded-2"
                         style="width:48px;height:48px;">
                      <span class="fw-bold text-body-secondary">
                        <?= mb_strtoupper(mb_substr($s->title, 0, 1)) ?>
                      </span>
                    </div>
                  <?php endif; ?>
                </div>
                <div class="d-flex flex-column">
                  <span class="fw-medium" style="font-family:var(--font-display)"><?= html_escape($s->title) ?></span>
                  <span class="small text-body-secondary"><?= html_escape($s->artist) ?></span>
                </div>
                <?php if ($s->duration_seconds): ?>
                  <span class="small text-body-secondary ms-auto">
                    <?= gmdate('i:s', $s->duration_seconds) ?>
                  </span>
                <?php endif; ?>
              </a>
              <?php endforeach; ?>
            </div>
          </div>
        <?php endif; ?>

      </div>

    </div>
  </div>
</section>
