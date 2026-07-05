<section class="py-4" id="create-playlist">
  <div class="container">
    <header class="d-flex align-items-baseline gap-3 mb-4">
      <h2 class="h2 fw-light mb-0" style="font-family:'Cormorant',serif;">Create New Playlist</h2>
      <a href="<?= base_url('playlist') ?>" class="btn btn-outline-secondary btn-sm ms-auto">Cancel</a>
    </header>

    <div class="card" style="max-width:520px;">
      <div class="card-body">
        <?php if (validation_errors()): ?>
        <div class="alert alert-danger d-flex align-items-center gap-2">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>
          <?= validation_errors('', '') ?>
        </div>
        <?php endif; ?>

        <?= form_open('playlist/create', ['class' => 'd-flex flex-column gap-3']) ?>
          <div>
            <label for="pl-name" class="form-label">Playlist Name</label>
            <input type="text" name="name" id="pl-name" class="form-control" value="<?= html_escape(set_value('name')) ?>" required maxlength="100" placeholder="My Awesome Playlist">
          </div>
          <div>
            <label for="pl-desc" class="form-label">Description</label>
            <textarea name="description" id="pl-desc" class="form-control" maxlength="500" rows="3" placeholder="Describe your playlist..."><?= html_escape(set_value('description')) ?></textarea>
          </div>
          <div class="d-flex justify-content-between align-items-center card p-3">
            <div>
              <span class="fw-semibold small">Public Playlist</span>
              <div class="form-text">Anyone can view this playlist.</div>
            </div>
            <div class="form-check form-switch">
              <input type="checkbox" class="form-check-input" name="is_public" value="1" id="pl-public">
            </div>
          </div>
          <div class="d-flex gap-2">
            <button type="submit" class="btn btn-primary">Create Playlist</button>
            <a href="<?= base_url('playlist') ?>" class="btn btn-outline-secondary">Cancel</a>
          </div>
        <?= form_close() ?>
      </div>
    </div>
  </div>
</section>
