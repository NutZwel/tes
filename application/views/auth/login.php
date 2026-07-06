<!-- ────────────────────────────────────────────────
     VIEW: auth/login.php
     Halaman form login. Menampilkan card terpusat dengan:
       - Ikon header, judul "Welcome back", subtitle
       - Alert error dari server (hasil login gagal)
       - Alert error validasi
       - Field username/email dan password
       - Tombol submit dengan loading state
       - Link footnote ke halaman registrasi
     ──────────────────────────────────────────────── -->

<section class="page page--auth">
  <div class="page__inner">
    <div class="auth-card">
      <div class="auth-card__header">
        <div class="auth-card__icon" aria-hidden="true">
          <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"/><polyline points="10 17 15 12 10 7"/><line x1="15" y1="12" x2="3" y2="12"/></svg>
        </div>
        <h1 class="auth-card__title">Welcome back</h1>
        <p class="auth-card__sub">Sign in to continue listening</p>
      </div>

      <!-- Banner error server-side (misal "Invalid credentials") muncul hanya jika di-set oleh controller -->
      <?php if (!empty($error)): ?>
        <div class="alert alert--error" role="alert">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
          <span><?= html_escape($error) ?></span>
        </div>
      <?php endif; ?>

      <!-- Banner error validasi CI3 -->
      <?php if (validation_errors()): ?>
        <div class="alert alert--error" role="alert">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
          <span>Please fix the errors below.</span>
        </div>
      <?php endif; ?>

      <?= form_open('auth/login', ['class' => 'auth-form', 'novalidate' => 'novalidate']) ?>
        <div class="auth-form__field<?= form_error('identity') ? ' has-error' : '' ?>">
          <label for="identity" class="auth-form__label">Username or Email</label>
          <input type="text" id="identity" name="identity" class="auth-form__input"
                 value="<?= set_value('identity') ?>"
                 required autocomplete="username"
                 placeholder="your@email.com"
                 autofocus>
          <?= form_error('identity', '<span class="auth-form__error">', '</span>') ?>
        </div>

        <div class="auth-form__field<?= form_error('password') ? ' has-error' : '' ?>">
          <label for="password" class="auth-form__label">Password</label>
          <input type="password" id="password" name="password" class="auth-form__input"
                 required autocomplete="current-password"
                 placeholder="Enter your password">
          <?= form_error('password', '<span class="auth-form__error">', '</span>') ?>
        </div>

        <button type="submit" class="btn btn--accent btn--full auth-form__submit">
          <span class="btn__label">Sign In</span>
        </button>
      <?= form_close() ?>

      <p class="auth-card__footnote">
        Don't have an account?
        <a href="<?= base_url('register') ?>">Create one</a>
      </p>
    </div>
  </div>
</section>
