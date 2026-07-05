<section class="py-5">
  <div class="container">

    <!-- Header -->
    <header class="d-flex flex-wrap align-items-center gap-3 mb-5">
      <div>
        <h1 style="font-family:var(--font-display);font-size:var(--text-2xl);font-weight:300;" class="text-body m-0">Edit Playlist</h1>
        <p class="text-body-secondary mb-0" style="font-size:var(--text-sm);">Update your playlist details</p>
      </div>
      <a href="<?= base_url('playlist/' . $playlist->id) ?>" class="btn btn-outline-light ms-auto">Back to Playlist</a>
    </header>

    <!-- Success Flash -->
    <?php if ($this->session->flashdata('pl_success')): ?>
    <div class="alert alert-success d-flex align-items-center gap-2 alert-dismissible fade show" role="alert">
      <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="flex-shrink-0"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
      <span><?= $this->session->flashdata('pl_success') ?></span>
      <button type="button" class="btn-close btn-close-white" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    <?php endif; ?>

    <!-- Validation Errors -->
    <?php if (validation_errors()): ?>
    <div class="alert alert-danger d-flex align-items-center gap-2 alert-dismissible fade show" role="alert">
      <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="flex-shrink-0"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>
      <span>Please fix the errors below.</span>
      <button type="button" class="btn-close btn-close-white" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    <?php endif; ?>

    <!-- Form -->
    <?= form_open_multipart('playlist/edit/'.$playlist->id, ['class' => '']) ?>

      <div class="card bg-body-tertiary border-secondary">
        <div class="card-body p-4 p-lg-5">

          <div class="row g-4">

            <!-- Playlist Name -->
            <div class="col-12">
              <label for="name" class="form-label text-body">Playlist Name <span class="text-danger">*</span></label>
              <input type="text" name="name" id="name"
                     class="form-control bg-dark text-light border-secondary<?= form_error('name') ? ' is-invalid' : '' ?>"
                     value="<?= html_escape(set_value('name', $playlist->name)) ?>"
                     required maxlength="100" placeholder="Enter playlist name">
              <?= form_error('name', '<div class="invalid-feedback">', '</div>') ?>
            </div>

            <!-- Description -->
            <div class="col-12">
              <label for="description" class="form-label text-body">Description</label>
              <textarea name="description" id="description"
                        class="form-control bg-dark text-light border-secondary<?= form_error('description') ? ' is-invalid' : '' ?>"
                        rows="3" maxlength="500" placeholder="Describe your playlist..."><?= html_escape(set_value('description', $playlist->description)) ?></textarea>
              <?= form_error('description', '<div class="invalid-feedback">', '</div>') ?>
            </div>

            <!-- Cover Image -->
            <div class="col-12">
              <label class="form-label text-body">Cover Image</label>

              <?php if ($playlist->cover_path): ?>
              <div class="d-flex align-items-center gap-3 mb-3">
                <img src="<?= base_url($playlist->cover_path) ?>" alt="" width="64" height="64"
                     style="border-radius:var(--radius-md);object-fit:cover;border:1px solid var(--color-rule);">
                <label class="form-check-label small text-secondary d-flex align-items-center gap-2">
                  <input type="checkbox" name="remove_cover" value="1" class="form-check-input">
                  Remove current cover
                </label>
              </div>
              <?php endif; ?>

              <label for="cover_file" class="upload-box">
                <input type="file" name="cover_file" id="cover_file" accept="image/png,image/jpeg,image/webp,image/gif" hidden>
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                  <rect x="3" y="3" width="18" height="18" rx="2" ry="2"/>
                  <circle cx="8.5" cy="8.5" r="1.5"/>
                  <polyline points="21 15 16 10 5 21"/>
                </svg>
                <span class="upload-box__text">Click to upload cover image</span>
                <span class="upload-box__hint">PNG, JPG, WebP or GIF</span>
              </label>
              <?= form_error('cover_file', '<div class="invalid-feedback">', '</div>') ?>
            </div>

            <!-- Banner Image (new) -->
            <div class="col-12">
              <label class="form-label text-body">Banner Image (shown behind playlist)</label>

              <?php if ($playlist->banner_path): ?>
              <div class="d-flex align-items-center gap-3 mb-3">
                <img src="<?= base_url($playlist->banner_path) ?>" alt="" width="120" height="64"
                     style="border-radius:var(--radius-md);object-fit:cover;border:1px solid var(--color-rule);">
                <label class="form-check-label small text-secondary d-flex align-items-center gap-2">
                  <input type="checkbox" name="remove_banner" value="1" class="form-check-input">
                  Remove current banner
                </label>
              </div>
              <?php endif; ?>

              <label for="banner_file" class="upload-box">
                <input type="file" name="banner_file" id="banner_file" accept="image/png,image/jpeg,image/webp,image/gif" hidden>
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                  <rect x="3" y="3" width="18" height="18" rx="2" ry="2"/>
                  <circle cx="8.5" cy="8.5" r="1.5"/>
                  <polyline points="21 15 16 10 5 21"/>
                </svg>
                <span class="upload-box__text">Click to upload banner image</span>
                <span class="upload-box__hint">PNG, JPG, WebP or GIF — ideal 1200x400px</span>
              </label>
            </div>

          </div>

          <div class="d-flex gap-2 mt-4 pt-3 border-top border-secondary">
            <button type="submit" class="btn btn-primary">Save Changes</button>
            <a href="<?= base_url('playlist/' . $playlist->id) ?>" class="btn btn-outline-light">Cancel</a>
          </div>

        </div>
      </div>

    <?= form_close() ?>
  </div>
</section>
