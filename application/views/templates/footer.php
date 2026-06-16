<footer class="footer" id="footer">
  <div class="footer__inner">
    <div class="footer__grid">
      <div class="footer__col">
        <div class="footer__brand">
          <img src="<?= base_url('public/images/logo.png') ?>" alt="Laufey" height="32">
        </div>
        <p class="footer__desc">Stream lossless, download unlimited, and curate your world. The divine library of sound, now in your hands.</p>
      </div>
      <div class="footer__col">
        <h4 class="footer__heading">Browse</h4>
        <ul class="footer__list">
          <li><a href="<?= base_url() ?>" class="footer__link">Home</a></li>
          <li><a href="<?= base_url('catalog') ?>" class="footer__link">Catalog</a></li>
          <li><a href="#" class="footer__link">Player</a></li>
          <li><a href="#" class="footer__link">Playlists</a></li>
        </ul>
      </div>
      <div class="footer__col">
        <h4 class="footer__heading">Account</h4>
        <ul class="footer__list">
          <li><a href="#" class="footer__link">Sign In</a></li>
          <li><a href="#" class="footer__link">Create Account</a></li>
          <li><a href="#" class="footer__link">Favorites</a></li>
          <li><a href="#" class="footer__link">Downloads</a></li>
        </ul>
      </div>
      <div class="footer__col">
        <h4 class="footer__heading">Info</h4>
        <ul class="footer__list">
          <li><a href="#" class="footer__link">Terms of Use</a></li>
          <li><a href="#" class="footer__link">Privacy</a></li>
          <li><a href="#" class="footer__link">Contact</a></li>
        </ul>
      </div>
    </div>
    <div class="footer__bottom">
      <p class="footer__copy">&copy; <?= date('Y') ?> Laufey. All rights reserved.</p>
      <div class="footer__socials">
        <a href="#" class="footer__social-link" aria-label="Twitter">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M22 4s-.7 2.1-2 3.4c1.6 10-9.4 17.3-18 11.6 2.2.1 4.4-.6 6-2C3 15.5.5 9.6 3 5c2.2 2.6 5.6 4.1 9 4-.9-4.2 4-6.6 7-3.8 1.1 0 3-1.2 3-1.2z"/></svg>
        </a>
        <a href="#" class="footer__social-link" aria-label="GitHub">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M9 19c-5 1.5-5-2.5-7-3m14 6v-3.87a3.37 3.37 0 0 0-.94-2.61c3.14-.35 6.44-1.54 6.44-7A5.44 5.44 0 0 0 20 4.77 5.07 5.07 0 0 0 19.91 1S18.73.65 16 2.48a13.38 13.38 0 0 0-7 0C6.27.65 5.09 1 5.09 1A5.07 5.07 0 0 0 5 4.77a5.44 5.44 0 0 0-1.5 3.78c0 5.42 3.3 6.61 6.44 7A3.37 3.37 0 0 0 9 18.13V22"/></svg>
        </a>
      </div>
    </div>
  </div>
</footer>

<?php
$isLoggedIn = isset($isLoggedIn) ? $isLoggedIn : false;
?>
<script>
(function() {
  var nav = document.getElementById('nav');
  if (nav) {
    window.addEventListener('scroll', function() {
      requestAnimationFrame(function() {
        nav.classList.toggle('is-scrolled', window.scrollY > 24);
      });
    }, { passive: true });
  }

  // ── Carousel arrows ──
  (function() {
    var track = document.querySelector('.carousel__track');
    var wrap = track ? track.parentNode : null;
    var prev = document.querySelector('.carousel__arrow--prev');
    var next = document.querySelector('.carousel__arrow--next');
    if (!track || !wrap || !prev || !next) return;

    var pos = 0;
    var gap;
    var cardWidth;

    var measure = function() {
      var card = track.querySelector('.carousel__card');
      if (!card) return;
      gap = parseFloat(getComputedStyle(track).gap) || 0;
      cardWidth = card.getBoundingClientRect().width;
    };

    var step = function() { return cardWidth + gap; };

    var maxPos = function() {
      var total = track.scrollWidth - track.clientWidth;
      return Math.max(0, total);
    };

    var go = function(dir) {
      measure();
      pos += dir * step();
      pos = Math.max(0, Math.min(pos, maxPos()));
      track.style.transform = 'translateX(-' + pos + 'px)';
      updateArrows();
    };

    var updateArrows = function() {
      prev.hidden = pos <= 0;
      next.hidden = pos >= maxPos();
    };

    prev.addEventListener('click', function() { go(-1); });
    next.addEventListener('click', function() { go(1); });
    measure();
    updateArrows();
    window.addEventListener('resize', function() {
      measure();
      track.style.transform = 'translateX(-' + Math.min(pos, maxPos()) + 'px)';
      updateArrows();
    });
  })();
  // ── End carousel arrows ──

  var toggle = document.querySelector('.nav__toggle');
  if (toggle) {
    var overlay = document.createElement('div');
    overlay.className = 'nav__mobile-overlay';
    var links =
      '<a href="<?= base_url() ?>" class="nav__link">Home</a>' +
      '<a href="<?= base_url('catalog') ?>" class="nav__link">Catalog</a>' +
      '<a href="#" class="nav__link">Search</a>';
    <?php if ($isLoggedIn): ?>
    links +=
      '<a href="#" class="nav__link">Playlist</a>' +
      '<a href="#" class="nav__link">Favorites</a>' +
      '<a href="#" class="nav__link">Downloads</a>' +
      '<a href="<?= base_url('logout') ?>" class="btn btn--text">Sign Out</a>';
    <?php else: ?>
    links +=
      '<a href="<?= base_url('login') ?>" class="btn btn--text">Sign In</a>' +
      '<a href="<?= base_url('register') ?>" class="btn btn--accent">Sign Up</a>';
    <?php endif; ?>
    overlay.innerHTML = links;
    document.getElementById('nav').appendChild(overlay);

    toggle.addEventListener('click', function() {
      var expanded = toggle.getAttribute('aria-expanded') === 'true' ? false : true;
      toggle.setAttribute('aria-expanded', expanded);
      overlay.classList.toggle('is-open', expanded);
    });
    overlay.addEventListener('click', function(e) {
      if (e.target.tagName === 'A') {
        toggle.setAttribute('aria-expanded', 'false');
        overlay.classList.remove('is-open');
      }
    });
  }
})();
  // ── Auth form loading state ──
  (function() {
    var forms = document.querySelectorAll('.auth-form');
    forms.forEach(function(form) {
      form.addEventListener('submit', function() {
        var btn = form.querySelector('.auth-form__submit');
        if (btn) btn.classList.add('is-loading');
      });
    });
  })();
</script>
