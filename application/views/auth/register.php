<section class="page page--auth">
  <div class="page__inner">
    <div class="auth-card">

      <div class="auth-card__header">
        <div class="auth-card__icon" aria-hidden="true">
          <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="8.5" cy="7" r="4"/><polyline points="17 11 19 13 23 9"/></svg>
        </div>
        <h1 class="auth-card__title">Create your account</h1>
        <p class="auth-card__sub">Free. Unlimited. Zero ads. Forever.</p>
      </div>

      <?php if (!empty($error)): ?>
        <div class="alert alert--error" role="alert">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>
          <span><?= html_escape($error) ?></span>
        </div>
      <?php endif; ?>

      <?php if (validation_errors()): ?>
        <div class="alert alert--error" role="alert">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
          <span>Please fix the errors below.</span>
        </div>
      <?php endif; ?>

      <?= form_open('auth/register', ['class' => 'auth-form', 'novalidate' => 'novalidate']) ?>

        <div class="auth-form__field<?= form_error('username') ? ' has-error' : '' ?>">
          <label for="username" class="auth-form__label">Username</label>
          <input type="text" id="username" name="username" class="auth-form__input"
                 value="<?= set_value('username') ?>"
                 required minlength="3" maxlength="60"
                 autocomplete="username"
                 placeholder="Choose a username">
          <?= form_error('username', '<span class="auth-form__error">', '</span>') ?>
        </div>

        <div class="auth-form__field<?= form_error('email') ? ' has-error' : '' ?>">
          <label for="email" class="auth-form__label">Email</label>
          <input type="email" id="email" name="email" class="auth-form__input"
                 value="<?= set_value('email') ?>"
                 required autocomplete="email"
                 placeholder="your@email.com">
          <?= form_error('email', '<span class="auth-form__error">', '</span>') ?>
        </div>

        <div class="auth-form__field-group">
          <div class="auth-form__field<?= form_error('password') ? ' has-error' : '' ?>">
            <label for="password" class="auth-form__label">Password</label>
            <input type="password" id="password" name="password" class="auth-form__input"
                   required minlength="6" autocomplete="new-password"
                   placeholder="Min 6 characters">
            <?= form_error('password', '<span class="auth-form__error">', '</span>') ?>
          </div>

          <div class="auth-form__field<?= form_error('passconf') ? ' has-error' : '' ?>">
            <label for="passconf" class="auth-form__label">Confirm</label>
            <input type="password" id="passconf" name="passconf" class="auth-form__input"
                   required autocomplete="new-password"
                   placeholder="Repeat password">
            <?= form_error('passconf', '<span class="auth-form__error">', '</span>') ?>
          </div>
        </div>

        <button type="submit" class="btn btn--accent btn--full auth-form__submit">
          <span class="btn__label">Create Account</span>
        </button>

      <?= form_close() ?>

      <p class="auth-card__footnote">
        Already have an account?
        <a href="<?= base_url('login') ?>">Sign in</a>
      </p>

    </div>
  </div>
</section>
