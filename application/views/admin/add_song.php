<!-- ────────────────────────────────────────────────
     VIEW: admin/add_song.php
     Form tambah lagu baru ke katalog.
     Memuat input untuk judul, artis, genre (dropdown + new genre),
     durasi, deskripsi, artist bio, upload file audio (wajib),
     dan upload cover (opsional). Menggunakan form_open_multipart
     untuk menangani file upload.
     ──────────────────────────────────────────────── -->
<section class="py-5">
  <div class="container">

    <!-- Header -->
    <header class="d-flex flex-wrap align-items-center gap-3 mb-5">
      <div>
        <h1 style="font-family:var(--font-display);font-size:var(--text-2xl);font-weight:300;color:var(--color-ink);" class="m-0">Add Song</h1>
        <p class="mb-0" style="font-size:var(--text-sm);color:var(--color-muted);">Add a new song to the catalog</p>
      </div>
      <a href="<?= base_url('admin') ?>" class="btn btn-outline-light ms-auto">Back to Admin</a>
    </header>

    <!-- Success Flash -->
    <?php if ($this->session->flashdata('success')): ?>
    <div class="alert alert-success d-flex align-items-center gap-2 alert-dismissible fade show" role="alert" style="background:var(--color-paper-2);color:var(--color-ink);border-color:var(--color-rule);">
      <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="flex-shrink-0"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
      <span><?= $this->session->flashdata('success') ?></span>
      <button type="button" class="btn-close btn-close-white" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    <?php endif; ?>

    <!-- Error Flash -->
    <?php if (!empty($error)): ?>
    <div class="alert alert-danger d-flex align-items-center gap-2 alert-dismissible fade show" role="alert" style="background:var(--color-paper-2);color:var(--color-ink);border-color:var(--color-rule);">
      <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="flex-shrink-0"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>
      <span><?= $error ?></span>
      <button type="button" class="btn-close btn-close-white" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    <?php endif; ?>

    <!-- Validation Errors -->
    <?php if (validation_errors()): ?>
    <div class="alert alert-danger d-flex align-items-center gap-2 alert-dismissible fade show" role="alert" style="background:var(--color-paper-2);color:var(--color-ink);border-color:var(--color-rule);">
      <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="flex-shrink-0"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
      <span>Please fix the errors below.</span>
      <button type="button" class="btn-close btn-close-white" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    <?php endif; ?>

    <!-- Form -->
    <?= form_open_multipart('admin/add_song', ['class' => '']) ?>

      <div class="card border-secondary" style="background:var(--color-paper-2);">
        <div class="card-body p-4 p-lg-5">

          <div class="row g-4">

            <!-- Judul Lagu -->
            <div class="col-12">
              <label for="title" class="form-label" style="color:var(--color-ink);">Song Title <span class="text-danger">*</span></label>
              <input type="text" name="title" id="title"
                     class="form-control" style="background:var(--color-paper);color:var(--color-ink);border-color:var(--color-rule);<?= form_error('title') ? ' border-color:#dc3545;' : '' ?>"
                     value="<?= html_escape(set_value('title')) ?>"
                     required maxlength="100" placeholder="Enter song title">
              <?= form_error('title', '<div class="invalid-feedback">', '</div>') ?>
            </div>

            <!-- Artis -->
            <div class="col-12">
              <label for="artist" class="form-label" style="color:var(--color-ink);">Artist <span class="text-danger">*</span></label>
              <input type="text" name="artist" id="artist"
                     class="form-control" style="background:var(--color-paper);color:var(--color-ink);border-color:var(--color-rule);<?= form_error('artist') ? ' border-color:#dc3545;' : '' ?>"
                     value="<?= html_escape(set_value('artist')) ?>"
                     required maxlength="100" placeholder="Enter artist name">
              <?= form_error('artist', '<div class="invalid-feedback">', '</div>') ?>
            </div>

            <!-- Genre -->
            <div class="col-md-6">
              <label for="genre_id" class="form-label" style="color:var(--color-ink);">Genre</label>
              <div style="display:flex;gap:6px;">
                <select name="genre_id" id="genre_id"
                        class="form-select" style="flex:1;background:var(--color-paper);color:var(--color-ink);border-color:var(--color-rule);<?= form_error('genre_id') ? ' border-color:#dc3545;' : '' ?>">
                  <option value="">— Select Genre —</option>
                  <?php foreach ($genres as $g): ?>
                  <option value="<?= $g->id ?>" <?= set_value('genre_id') == $g->id ? 'selected' : '' ?>><?= html_escape($g->name) ?></option>
                  <?php endforeach; ?>
                </select>
                <button type="button" class="btn btn-outline-light" style="white-space:nowrap;flex-shrink:0;" onclick="document.getElementById('genre_id').disabled=true;document.getElementById('genre_id').style.opacity='0.4';document.getElementById('new_genre_input').style.display='';this.style.display='none';">+ New</button>
              </div>
              <!-- Input genre baru — muncul saat tombol "+ New" diklik -->
              <input type="text" name="new_genre" id="new_genre_input" placeholder="Enter new genre name" style="display:none;width:100%;padding:10px 14px;border-radius:8px;border:1px solid var(--color-rule);background:var(--color-paper);color:var(--color-ink);font-size:var(--text-sm);margin-top:6px;outline:none;">
              <?= form_error('genre_id', '<div class="invalid-feedback">', '</div>') ?>
            </div>

            <!-- Durasi -->
            <div class="col-md-6">
              <label for="duration_seconds" class="form-label" style="color:var(--color-ink);">Duration (seconds)</label>
              <input type="number" name="duration_seconds" id="duration_seconds"
                     class="form-control" style="background:var(--color-paper);color:var(--color-ink);border-color:var(--color-rule);<?= form_error('duration_seconds') ? ' border-color:#dc3545;' : '' ?>"
                     value="<?= html_escape(set_value('duration_seconds')) ?>"
                     min="0" max="9999" placeholder="e.g. 240">
              <?= form_error('duration_seconds', '<div class="invalid-feedback">', '</div>') ?>
            </div>

            <!-- Deskripsi -->
            <div class="col-12">
              <label for="description" class="form-label" style="color:var(--color-ink);">Song Description</label>
              <textarea name="description" id="description"
                        class="form-control" style="background:var(--color-paper);color:var(--color-ink);border-color:var(--color-rule);"
                        rows="3" maxlength="1000" placeholder="About this song..."><?= html_escape(set_value('description')) ?></textarea>
              <?= form_error('description', '<div class="invalid-feedback">', '</div>') ?>
            </div>

            <!-- Bio Artis -->
            <div class="col-12">
              <label for="artist_bio" class="form-label" style="color:var(--color-ink);">Artist Bio</label>
              <textarea name="artist_bio" id="artist_bio"
                        class="form-control" style="background:var(--color-paper);color:var(--color-ink);border-color:var(--color-rule);"
                        rows="3" maxlength="2000" placeholder="About the artist..."><?= html_escape(set_value('artist_bio')) ?></textarea>
              <?= form_error('artist_bio', '<div class="invalid-feedback">', '</div>') ?>
            </div>

            <!-- Upload Audio -->
            <div class="col-12">
              <label class="form-label" style="color:var(--color-ink);">Audio File <span class="text-danger">*</span></label>
              <label for="audio_file" class="upload-box" style="cursor:pointer;">
                <input type="file" name="audio_file" id="audio_file" accept=".mp3,.wav,.ogg,.flac,.aac" required hidden>
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" style="color:var(--color-muted);">
                  <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
                  <polyline points="17 8 12 3 7 8"/>
                  <line x1="12" y1="3" x2="12" y2="15"/>
                </svg>
                <span class="upload-box__text" style="color:var(--color-ink-2);">Click to upload audio file</span>
                <span class="upload-box__hint" style="color:var(--color-muted);">MP3, WAV, OGG, FLAC or AAC</span>
              </label>
              <?= form_error('audio_file', '<div class="invalid-feedback">', '</div>') ?>
            </div>

            <!-- Upload Cover -->
            <div class="col-12">
              <label class="form-label" style="color:var(--color-ink);">Cover Image</label>
              <label for="cover_file" class="upload-box" style="cursor:pointer;">
                <input type="file" name="cover_file" id="cover_file" accept="image/png,image/jpeg,image/webp" hidden>
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" style="color:var(--color-muted);">
                  <rect x="3" y="3" width="18" height="18" rx="2" ry="2"/>
                  <circle cx="8.5" cy="8.5" r="1.5"/>
                  <polyline points="21 15 16 10 5 21"/>
                </svg>
                <span class="upload-box__text" style="color:var(--color-ink-2);">Click to upload cover image</span>
                <span class="upload-box__hint" style="color:var(--color-muted);">PNG, JPG or WebP (optional)</span>
              </label>
              <?= form_error('cover_file', '<div class="invalid-feedback">', '</div>') ?>
            </div>

          </div>

          <div class="d-flex gap-2 mt-4 pt-3 border-top border-secondary">
            <button type="submit" class="btn btn-primary">Upload Song</button>
            <a href="<?= base_url('admin') ?>" class="btn btn-outline-light">Cancel</a>
          </div>

        </div>
      </div>

    <?= form_close() ?>
  </div>
</section>
