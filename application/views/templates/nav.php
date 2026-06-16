<?php
$activeSegment = ltrim((string) ($this->uri->segment(1) ?: 'dashboard'), '/');
// Map URLs to nav sections — covers direct routes AND PJAX initial state
$navSection = match (true) {
  $activeSegment === 'catalog' || $activeSegment === 'catalog/index' => 'catalog',
  $activeSegment === 'playlist' || $activeSegment === 'playlists' => 'playlist',
  $activeSegment === 'downloads' || $activeSegment === 'downloads/page' => 'downloads',
  $activeSegment === 'user' || $activeSegment === 'profile'      => 'user',
  $activeSegment === 'admin'                    => 'admin',
  default                                       => 'dashboard',
};
$isLoggedIn = isset($isLoggedIn) ? $isLoggedIn : false;
$displayName = $isLoggedIn ? ($this->session->userdata('display_name') ?: $this->session->userdata('username')) : '';
$avatarPath = $isLoggedIn ? ($this->session->userdata('avatar_path') ?: '') : '';
$username = $isLoggedIn ? $this->session->userdata('username') : '';
$userRole = $isLoggedIn ? $this->session->userdata('role') : '';
?>

<?php if ($isLoggedIn): ?>
<!-- ═══ Sidebar ═══ -->
<div class="offcanvas offcanvas-start bg-dark text-light" tabindex="-1" id="sidebar" aria-label="User menu">
  <div class="offcanvas-header border-bottom border-secondary">
    <a href="<?= base_url('user') ?>" class="d-flex align-items-center text-decoration-none text-light gap-2">
      <?php if ($avatarPath && file_exists(FCPATH . $avatarPath)): ?>
        <img src="<?= base_url($avatarPath) ?>" alt="" class="rounded-2" width="40" height="40" style="object-fit:cover;">
      <?php else: ?>
        <span class="d-inline-flex align-items-center justify-content-center rounded-2 bg-secondary text-primary" style="width:40px;height:40px;font-weight:600;"><?= mb_strtoupper(mb_substr($displayName ?: $username, 0, 1)) ?></span>
      <?php endif; ?>
      <div>
        <div class="fw-semibold text-light small"><?= html_escape($displayName) ?></div>
        <div class="text-secondary small">@<?= html_escape($username) ?></div>
      </div>
    </a>
    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas"></button>
  </div>
  <div class="offcanvas-body p-3">
    <div class="d-flex flex-column gap-1">
      <a href="<?= base_url() ?>" class="nav__btn nav-pjax <?= $navSection === 'dashboard' ? 'nav__btn--active' : '' ?>" data-pjax-nav>
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="me-2"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>Home
      </a>
      <a href="<?= base_url('catalog') ?>" class="nav__btn nav-pjax <?= $navSection === 'catalog' ? 'nav__btn--active' : '' ?>" data-pjax-nav>
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="me-2"><circle cx="12" cy="12" r="10"/><polygon points="10 8 16 12 10 16 10 8"/></svg>Catalog
      </a>
      <a href="<?= base_url('playlist') ?>" class="nav__btn nav-pjax <?= $navSection === 'playlist' ? 'nav__btn--active' : '' ?>" data-pjax-nav>
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="me-2"><path d="M9 18V5l12-2v13"/><circle cx="6" cy="18" r="3"/><circle cx="18" cy="16" r="3"/></svg>My Playlist
      </a>
      <a href="<?= base_url('user') ?>" class="nav__btn nav-pjax <?= $navSection === 'user' ? 'nav__btn--active' : '' ?>" data-pjax-nav>
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="me-2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>Profile
      </a>
      <a href="<?= base_url('downloads') ?>" class="nav__btn nav-pjax <?= $navSection === 'download' ? 'nav__btn--active' : '' ?>" data-pjax-nav>
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="me-2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>Download
      </a>
      <?php if ($userRole === 'admin'): ?>
      <a href="<?= base_url('admin') ?>" class="nav__btn nav-pjax <?= $navSection === 'admin' ? 'nav__btn--active' : '' ?>" data-pjax-nav>
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="me-2"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg>Admin Panel
      </a>
      <?php endif; ?>
      <hr class="text-secondary my-2">
      <a href="<?= base_url('logout') ?>" class="nav__btn nav__btn--outline" style="color: oklch(65% 0.20 25); border-color: oklch(65% 0.20 25);">Sign Out</a>
    </div>
  </div>
</div>
<?php endif; ?>

<!-- ═══ Main Navbar ═══ -->
<nav class="navbar navbar-expand navbar-dark bg-dark border-bottom border-secondary sticky-top py-2">
  <div class="container">
    <?php if ($isLoggedIn): ?>
    <div class="d-flex align-items-center gap-2 flex-grow-1">
      <button class="btn btn-sm btn-outline-secondary border-0 me-1" type="button" data-bs-toggle="offcanvas" data-bs-target="#sidebar">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="18" x2="21" y2="18"/></svg>
      </button>
      <div class="flex-grow-1 d-flex justify-content-center">
        <form class="d-flex w-100" style="max-width:400px;" action="<?= base_url('catalog') ?>" method="get" role="search">
          <div class="input-group" style="border-radius:var(--radius-pill);overflow:hidden;background:var(--color-paper-2);border:1px solid var(--color-rule);">
            <input type="search" name="q" class="form-control" placeholder="Search songs..." aria-label="Search" style="background:transparent;border:none;border-radius:var(--radius-pill) 0 0 var(--radius-pill);padding:var(--space-xs) var(--space-sm);color:var(--color-ink);box-shadow:none;">
            <button class="btn" type="submit" style="background:transparent;border:none;border-radius:0 var(--radius-pill) var(--radius-pill) 0;border-left:1px solid var(--color-rule);padding:var(--space-xs) var(--space-sm);color:var(--color-muted);">
              <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
            </button>
          </div>
        </form>
      </div>
      <a href="<?= base_url() ?>" class="navbar-brand ms-2 me-0 p-0 flex-shrink-0">
        <img src="<?= base_url('public/images/logo.png') ?>" alt="Laufey" height="32">
      </a>
    </div>
    <?php else: ?>
    <a href="<?= base_url() ?>" class="navbar-brand me-3 p-0">
      <img src="<?= base_url('public/images/logo.png') ?>" alt="Laufey" height="32">
    </a>
    <div class="d-flex align-items-center gap-2 flex-grow-1">
      <a href="<?= base_url() ?>" class="nav__btn <?= $navSection === 'dashboard' ? 'nav__btn--active' : '' ?>" data-pjax-nav>Dashboard</a>
      <a href="<?= base_url('catalog') ?>" class="nav__btn <?= $navSection === 'catalog' ? 'nav__btn--active' : '' ?>" data-pjax-nav>Catalog</a>
      <div class="flex-grow-1 d-flex justify-content-center mx-2">
        <form class="d-flex w-100" style="max-width:400px;" action="<?= base_url('catalog') ?>" method="get" role="search">
          <div class="input-group" style="border-radius:var(--radius-pill);overflow:hidden;background:var(--color-paper-2);border:1px solid var(--color-rule);">
            <input type="search" name="q" class="form-control" placeholder="Search songs..." aria-label="Search" style="background:transparent;border:none;border-radius:var(--radius-pill) 0 0 var(--radius-pill);padding:var(--space-xs) var(--space-sm);color:var(--color-ink);box-shadow:none;">
            <button class="btn" type="submit" style="background:transparent;border:none;border-radius:0 var(--radius-pill) var(--radius-pill) 0;border-left:1px solid var(--color-rule);padding:var(--space-xs) var(--space-sm);color:var(--color-muted);">
              <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
            </button>
          </div>
        </form>
      </div>
      <a href="<?= base_url('login') ?>" class="nav__btn nav__btn--outline">Login</a>
      <a href="<?= base_url('register') ?>" class="nav__btn nav__btn--accent">Sign Up</a>
    </div>
    <?php endif; ?>
  </div>
</nav>
<div style="height:1px;"></div>
