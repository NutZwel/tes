<!-- ────────────────────────────────────────────────
     VIEW: admin/index.php
     Dashboard admin — menampilkan statistik (total user,
     active now, total songs) dan tabel daftar user
     dengan status online, role, dan tombol enable/disable.
     Hanya dapat diakses oleh user dengan role admin.
     ──────────────────────────────────────────────── -->
<section class="py-5">
  <div class="container">

    <!-- Header -->
    <header class="d-flex flex-wrap align-items-center gap-3 mb-5">
      <div>
        <h1 style="font-family:var(--font-display);font-size:var(--text-2xl);font-weight:300;color:var(--color-ink);" class="m-0">Admin Dashboard</h1>
        <p class="mb-0" style="font-size:var(--text-sm);color:var(--color-muted);">Management panel</p>
      </div>
      <div class="ms-auto d-flex gap-2">
        <a href="<?= base_url('admin/add_song') ?>" class="btn btn-primary">Add Song</a>
        <a href="<?= base_url('admin/songs') ?>" class="btn btn-outline-light">Manage Songs</a>
        <a href="<?= base_url('user') ?>" class="btn btn-outline-light">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="vertical-align:-2px;margin-right:4px;"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>Profile
        </a>
      </div>
    </header>

    <!-- Kartu Statistik -->
    <div class="row g-4 mb-5">
      <div class="col-md-4">
        <div class="card border-secondary h-100" style="background:var(--color-paper-2);">
          <div class="card-body d-flex flex-column gap-1">
            <span class="text-uppercase small" style="letter-spacing:0.05em;color:var(--color-muted);">Total Users</span>
            <span style="font-family:var(--font-display);font-size:var(--text-3xl);font-weight:300;color:var(--color-accent);"><?= (int) $total_users ?></span>
          </div>
        </div>
      </div>
      <div class="col-md-4">
        <div class="card border-secondary h-100" style="background:var(--color-paper-2);">
          <div class="card-body d-flex flex-column gap-1">
            <span class="text-uppercase small" style="letter-spacing:0.05em;color:var(--color-muted);">Active Now</span>
            <span style="font-family:var(--font-display);font-size:var(--text-3xl);font-weight:300;color:#4ade80;"><?= (int) $active_users ?></span>
          </div>
        </div>
      </div>
      <div class="col-md-4">
        <div class="card border-secondary h-100" style="background:var(--color-paper-2);">
          <div class="card-body d-flex flex-column gap-1">
            <span class="text-uppercase small" style="letter-spacing:0.05em;color:var(--color-muted);">Total Songs</span>
            <span style="font-family:var(--font-display);font-size:var(--text-3xl);font-weight:300;color:var(--color-ink);"><?= (int) $total_songs ?></span>
          </div>
        </div>
      </div>
    </div>

    <!-- Tabel User -->
    <div class="card border-secondary" style="background:var(--color-paper-2);">
      <div class="card-header border-secondary" style="background:transparent;">
        <h2 style="font-family:var(--font-display);font-size:var(--text-lg);font-weight:300;color:var(--color-ink);" class="m-0">Registered Users</h2>
      </div>
      <div class="table-responsive">
        <table class="table align-middle mb-0" style="color:var(--color-ink);background:transparent !important;">
          <thead>
            <tr style="background:transparent !important;">
              <th class="fw-medium small" style="color:var(--color-muted);border-color:var(--color-rule);background:transparent !important;">ID</th>
              <th class="fw-medium small" style="color:var(--color-muted);border-color:var(--color-rule);background:transparent !important;">Username</th>
              <th class="fw-medium small" style="color:var(--color-muted);border-color:var(--color-rule);background:transparent !important;">Email</th>
              <th class="fw-medium small" style="color:var(--color-muted);border-color:var(--color-rule);background:transparent !important;">Role</th>
              <th class="fw-medium small" style="color:var(--color-muted);border-color:var(--color-rule);background:transparent !important;">Status</th>
              <th class="fw-medium small" style="color:var(--color-muted);border-color:var(--color-rule);background:transparent !important;">Online</th>
              <th class="fw-medium small" style="color:var(--color-muted);border-color:var(--color-rule);background:transparent !important;">Joined</th>
              <th class="fw-medium small" style="color:var(--color-muted);border-color:var(--color-rule);background:transparent !important;">Action</th>
            </tr>
          </thead>
          <tbody style="background:transparent !important;">
            <?php foreach ($users as $u): ?>
            <tr style="background:transparent !important;">
              <td style="color:var(--color-ink);border-color:var(--color-rule);background:transparent !important;"><?= $u->id ?></td>
              <td class="fw-medium" style="color:var(--color-ink);border-color:var(--color-rule);background:transparent !important;"><?= html_escape($u->username) ?><?= $u->display_name && $u->display_name !== $u->username ? ' ('.html_escape($u->display_name).')' : '' ?></td>
              <td style="color:var(--color-ink-2);border-color:var(--color-rule);background:transparent !important;"><?= html_escape($u->email) ?></td>
              <td style="border-color:var(--color-rule);background:transparent !important;">
                <span class="badge <?= $u->role === 'admin' ? 'bg-primary' : 'bg-secondary' ?>"><?= html_escape($u->role) ?></span>
              </td>
              <td style="border-color:var(--color-rule);background:transparent !important;">
                <span class="d-inline-block rounded-circle me-1" style="width:8px;height:8px;background:<?= $u->is_active ? '#4ade80' : 'var(--color-neutral)' ?>;"></span>
                <span class="small" style="color:var(--color-muted);"><?= $u->is_active ? 'Active' : 'Disabled' ?></span>
              </td>
              <td style="border-color:var(--color-rule);background:transparent !important;">
                <?php if (in_array($u->id, $active_ids)): ?>
                <span style="color:#4ade80;font-weight:600;">● Online</span>
                <?php else: ?>
                <span style="color:var(--color-muted);">&mdash;</span>
                <?php endif; ?>
              </td>
              <td class="small" style="color:var(--color-muted);border-color:var(--color-rule);background:transparent !important;"><?= date('M j, Y', strtotime($u->created_at)) ?></td>
              <td style="border-color:var(--color-rule);background:transparent !important;">
                <!-- Toggle enable/disable user — hanya untuk admin -->
                <a href="<?= base_url('admin/toggle_user/'.$u->id) ?>" class="btn btn-sm btn-outline-secondary"><?= $u->is_active ? 'Disable' : 'Enable' ?></a>
              </td>
            </tr>
            <?php endforeach; ?>
            <?php if (empty($users)): ?>
            <tr style="background:transparent !important;"><td colspan="8" class="text-center py-5" style="color:var(--color-muted);border-color:var(--color-rule);background:transparent !important;">No users registered yet.</td></tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>

  </div>
</section>
