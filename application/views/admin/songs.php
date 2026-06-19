<section class="py-4">
  <div class="container">

    <header class="d-flex align-items-baseline gap-3 mb-4">
      <h1 class="fw-light mb-0" style="color:var(--color-ink);font-family:var(--font-display);">Manage Songs</h1>
      <a href="<?= base_url('admin/add_song') ?>" class="btn btn-primary ms-auto">Add New</a>
      <a href="<?= base_url('admin') ?>" class="btn btn-outline-light">Back</a>
    </header>

    <div class="card border-secondary" style="background:var(--color-paper-2);">
      <div class="table-responsive">
        <table class="table align-middle mb-0" style="color:var(--color-ink);background:transparent !important;">
          <thead>
            <tr style="background:transparent !important;">
              <th class="fw-medium small" style="color:var(--color-muted);border-color:var(--color-rule);background:transparent !important;">ID</th>
              <th class="fw-medium small" style="color:var(--color-muted);border-color:var(--color-rule);background:transparent !important;">Title</th>
              <th class="fw-medium small" style="color:var(--color-muted);border-color:var(--color-rule);background:transparent !important;">Artist</th>
              <th class="fw-medium small" style="color:var(--color-muted);border-color:var(--color-rule);background:transparent !important;">Genre</th>
              <th class="fw-medium small" style="color:var(--color-muted);border-color:var(--color-rule);background:transparent !important;">Duration</th>
              <th class="fw-medium small" style="color:var(--color-muted);border-color:var(--color-rule);background:transparent !important;">Active</th>
              <th class="fw-medium small" style="color:var(--color-muted);border-color:var(--color-rule);background:transparent !important;">Created</th>
              <th class="fw-medium small" style="color:var(--color-muted);border-color:var(--color-rule);background:transparent !important;">Action</th>
            </tr>
          </thead>
          <tbody style="background:transparent !important;">
            <?php foreach ($songs as $s): ?>
            <tr style="background:transparent !important;">
              <td style="color:var(--color-ink);border-color:var(--color-rule);background:transparent !important;"><?= $s->id ?></td>
              <td class="fw-medium" style="color:var(--color-ink);border-color:var(--color-rule);background:transparent !important;"><?= html_escape($s->title) ?></td>
              <td style="color:var(--color-ink-2);border-color:var(--color-rule);background:transparent !important;"><?= html_escape($s->artist) ?></td>
              <td style="color:var(--color-ink-2);border-color:var(--color-rule);background:transparent !important;"><?= html_escape($s->genre_name ?: '—') ?></td>
              <td style="color:var(--color-muted);border-color:var(--color-rule);background:transparent !important;"><?= $s->duration_seconds ? gmdate('i:s', $s->duration_seconds) : '—' ?></td>
              <td style="border-color:var(--color-rule);background:transparent !important;">
                <span class="d-inline-block rounded-circle" style="width:8px;height:8px;background:<?= $s->is_active ? '#4ade80' : 'var(--color-neutral)' ?>;"></span>
              </td>
              <td class="small" style="color:var(--color-muted);border-color:var(--color-rule);background:transparent !important;"><?= date('M j, Y', strtotime($s->created_at)) ?></td>
              <td style="border-color:var(--color-rule);background:transparent !important;">
                <div class="d-flex gap-1">
                  <a href="<?= base_url('admin/edit_song/'.$s->id) ?>" class="btn btn-sm btn-outline-primary">Edit</a>
                  <a href="<?= base_url('admin/delete_song/'.$s->id) ?>" class="btn btn-sm btn-outline-danger" data-confirm="delete-song">Delete</a>
                </div>
              </td>
            </tr>
            <?php endforeach; ?>
            <?php if (empty($songs)): ?>
            <tr style="background:transparent !important;"><td colspan="8" class="text-center py-5" style="color:var(--color-muted);border-color:var(--color-rule);background:transparent !important;">No songs found.</td></tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>

  </div>
</section>
