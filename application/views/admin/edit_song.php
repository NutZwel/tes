<section class="py-5">
  <div class="container">

    <!-- Header -->
    <header class="d-flex flex-wrap align-items-center gap-3 mb-5">
      <div>
        <h1 style="font-family:var(--font-display);font-size:var(--text-2xl);font-weight:300;color:var(--color-ink);" class="m-0">Edit Song</h1>
        <p class="mb-0" style="font-size:var(--text-sm);color:var(--color-muted);">Update song details</p>
      </div>
      <a href="<?= base_url('admin/songs') ?>" class="btn btn-outline-light ms-auto">Back to Songs</a>
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
    <?= form_open_multipart('admin/edit_song/'.$song->id, ['class' => '']) ?>

      <div class="card border-secondary" style="background:var(--color-paper-2);">
        <div class="card-body p-4 p-lg-5">

          <div class="row g-4">

            <!-- Song Title -->
            <div class="col-12">
              <label for="title" class="form-label" style="color:var(--color-ink);">Song Title <span class="text-danger">*</span></label>
              <input type="text" name="title" id="title"
                     class="form-control" style="background:var(--color-paper);color:var(--color-ink);border-color:var(--color-rule);<?= form_error('title') ? ' border-color:#dc3545;' : '' ?>"
                     value="<?= html_escape(set_value('title', $song->title)) ?>"
                     required maxlength="100" placeholder="Enter song title">
              <?= form_error('title', '<div class="invalid-feedback">', '</div>') ?>
            </div>

            <!-- Artist -->
            <div class="col-12">
              <label for="artist" class="form-label" style="color:var(--color-ink);">Artist <span class="text-danger">*</span></label>
              <input type="text" name="artist" id="artist"
                     class="form-control" style="background:var(--color-paper);color:var(--color-ink);border-color:var(--color-rule);<?= form_error('artist') ? ' border-color:#dc3545;' : '' ?>"
                     value="<?= html_escape(set_value('artist', $song->artist)) ?>"
                     required maxlength="100" placeholder="Enter artist name">
              <?= form_error('artist', '<div class="invalid-feedback">', '</div>') ?>
            </div>

            <!-- Genre -->
            <div class="col-md-6">
              <label for="genre_id" class="form-label" style="color:var(--color-ink);">Genre</label>
              <select name="genre_id" id="genre_id"
                      class="form-select" style="background:var(--color-paper);color:var(--color-ink);border-color:var(--color-rule);<?= form_error('genre_id') ? ' border-color:#dc3545;' : '' ?>">
                <option value="">— Select Genre —</option>
                <?php foreach ($genres as $g): ?>
                <option value="<?= $g->id ?>" <?= (int) set_value('genre_id', $song->genre_id) === (int) $g->id ? 'selected' : '' ?>><?= html_escape($g->name) ?></option>
                <?php endforeach; ?>
              </select>
              <?= form_error('genre_id', '<div class="invalid-feedback">', '</div>') ?>
            </div>

            <!-- Duration -->
            <div class="col-md-6">
              <label for="duration_seconds" class="form-label" style="color:var(--color-ink);">Duration (seconds)</label>
              <input type="number" name="duration_seconds" id="duration_seconds"
                     class="form-control" style="background:var(--color-paper);color:var(--color-ink);border-color:var(--color-rule);<?= form_error('duration_seconds') ? ' border-color:#dc3545;' : '' ?>"
                     value="<?= html_escape(set_value('duration_seconds', $song->duration_seconds)) ?>"
                     min="0" max="9999" placeholder="e.g. 240">
              <?= form_error('duration_seconds', '<div class="invalid-feedback">', '</div>') ?>
            </div>

            <!-- Description -->
            <div class="col-12">
              <label for="description" class="form-label" style="color:var(--color-ink);">Song Description</label>
              <textarea name="description" id="description"
                        class="form-control" style="background:var(--color-paper);color:var(--color-ink);border-color:var(--color-rule);"
                        rows="3" maxlength="1000" placeholder="About this song..."><?= html_escape(set_value('description', $song->description ?: '')) ?></textarea>
              <?= form_error('description', '<div class="invalid-feedback">', '</div>') ?>
            </div>

            <!-- Artist Bio -->
            <div class="col-12">
              <label for="artist_bio" class="form-label" style="color:var(--color-ink);">Artist Bio</label>
              <textarea name="artist_bio" id="artist_bio"
                        class="form-control" style="background:var(--color-paper);color:var(--color-ink);border-color:var(--color-rule);"
                        rows="3" maxlength="2000" placeholder="About the artist..."><?= html_escape(set_value('artist_bio', $song->artist_bio ?: '')) ?></textarea>
              <?= form_error('artist_bio', '<div class="invalid-feedback">', '</div>') ?>
            </div>

            <!-- Audio Upload -->
            <div class="col-12">
              <label class="form-label" style="color:var(--color-ink);">Audio File <span class="text-secondary" style="font-size:var(--text-xs);">(current: <?= html_escape($song->file_path ?: 'No file') ?>)</span></label>
              <label for="audio_file" class="upload-box" style="cursor:pointer;">
                <input type="file" name="audio_file" id="audio_file" accept=".mp3,.wav,.ogg,.flac,.aac" hidden>
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" style="color:var(--color-muted);">
                  <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
                  <polyline points="17 8 12 3 7 8"/>
                  <line x1="12" y1="3" x2="12" y2="15"/>
                </svg>
                <span class="upload-box__text" style="color:var(--color-ink-2);">Click to upload new audio file</span>
                <span class="upload-box__hint" style="color:var(--color-muted);">MP3, WAV, OGG, FLAC or AAC (leave empty to keep current)</span>
              </label>
              <?= form_error('audio_file', '<div class="invalid-feedback">', '</div>') ?>
            </div>

            <!-- Cover Upload -->
            <div class="col-12">
              <label class="form-label" style="color:var(--color-ink);">
                Cover Image
                <?php if ($song->cover_path && cover_available($song->cover_path)): ?>
                <img src="<?= cover_url($song->cover_path) ?>" alt="" style="width:28px;height:28px;border-radius:4px;object-fit:cover;vertical-align:middle;margin-left:8px;">
                <?php endif; ?>
                <span class="text-secondary" style="font-size:var(--text-xs);">(<?= html_escape($song->cover_path ?: 'No cover') ?>)</span>
              </label>
              <label for="cover_file" class="upload-box" style="cursor:pointer;">
                <input type="file" name="cover_file" id="cover_file" accept="image/png,image/jpeg,image/webp" hidden>
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" style="color:var(--color-muted);">
                  <rect x="3" y="3" width="18" height="18" rx="2" ry="2"/>
                  <circle cx="8.5" cy="8.5" r="1.5"/>
                  <polyline points="21 15 16 10 5 21"/>
                </svg>
                <span class="upload-box__text" style="color:var(--color-ink-2);">Click to upload new cover image</span>
                <span class="upload-box__hint" style="color:var(--color-muted);">PNG, JPG or WebP (leave empty to keep current)</span>
              </label>
              <?= form_error('cover_file', '<div class="invalid-feedback">', '</div>') ?>
            </div>

          </div>

          <div class="d-flex gap-2 mt-4 pt-3 border-top border-secondary">
            <button type="submit" class="btn btn-primary">Save Changes</button>
            <a href="<?= base_url('admin/songs') ?>" class="btn btn-outline-light">Cancel</a>
          </div>

        </div>
      </div>

    <?= form_close() ?>
  </div>
</section>
