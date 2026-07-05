/**
 * Laufey — PJAX Navigation (Bootstrap compatible)
 */
(function () {
  'use strict';

  var content = document.getElementById('pjax-content');
  if (!content) return;

  function isInternal(href) {
    if (!href || href === '#' || href.indexOf('javascript:') === 0 || href.indexOf('mailto:') === 0) return false;
    if (href.indexOf('http') === 0 && href.indexOf(BASE) !== 0) return false;
    if (href.indexOf('/logout') > -1 || href.indexOf('/download') > -1) return false;
    if (href.indexOf('#') > -1) return false;
    return true;
  }

  function load(url) {
    if (url.indexOf('/delete') > -1 || url.indexOf('/logout') > -1) {
      window.location.href = url;
      return;
    }
    var xhr = new XMLHttpRequest();
    xhr.open('GET', url + (url.indexOf('?') > -1 ? '&' : '?') + '_pjax=1', true);
    xhr.setRequestHeader('X-PJAX', 'true');
    xhr.onload = function () {
      if (xhr.status !== 200) { window.location.href = url; return; }
      var tmp = document.createElement('div');
      tmp.innerHTML = xhr.responseText;
      var newContent = tmp.querySelector('#pjax-content');
      var newTitle = tmp.querySelector('title');
      if (newContent) content.innerHTML = newContent.innerHTML;
      if (newTitle) document.title = newTitle.textContent;
      // Clean up Bootstrap modal/offcanvas backdrops (aggressive)
      function cleanUI() {
        document.querySelectorAll('.modal-backdrop, .offcanvas-backdrop').forEach(function(el) { el.remove(); });
        document.querySelectorAll('.offcanvas.show, .modal.show').forEach(function(el) { el.classList.remove('show'); });
        document.body.classList.remove('modal-open');
        document.body.style.overflow = '';
        document.body.style.paddingRight = '';
      }
      cleanUI();
      setTimeout(cleanUI, 100);
      setTimeout(cleanUI, 300);
      updateNav(url);
      document.dispatchEvent(new CustomEvent('pjax:complete'));
      window.scrollTo({ top: 0, behavior: 'smooth' });
    };
    xhr.onerror = function () { window.location.href = url; };
    xhr.send();
  }

  function updateNav(url) {
    // Get the relative path from BASE (works for both full URLs and pathname)
    var href = url || window.location.href;
    var rel = href.replace(BASE, '').split('?')[0].replace(/\/$/, '');
    var page = rel.split('/')[0] || 'dashboard';
    // Map alias URLs to nav section keys
    var navMap = { 'dashboard': 'dashboard', 'catalog': 'catalog', 'playlist': 'playlist', 'playlists': 'playlist', 'downloads': 'download', 'download': 'download', 'user': 'user', 'profile': 'user', 'admin': 'admin' };
    var pageSection = navMap[page] || 'dashboard';
    document.querySelectorAll('[data-pjax-nav]').forEach(function(link) {
      var lh = link.getAttribute('href') || '';
      var lr = lh.replace(BASE, '').split('?')[0].replace(/\/$/, '');
      var ls = lr.split('/')[0] || 'dashboard';
      var linkSection = navMap[ls] || 'dashboard';
      link.classList.toggle('nav__btn--active', linkSection === pageSection);
    });
  }

  /* ═══ Run on initial page load to ensure sidebar matches current URL ═══ */
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', function() { updateNav(null); });
  } else {
    updateNav(null);
  }

  document.addEventListener('click', function (e) {
    if (e.metaKey || e.ctrlKey || e.shiftKey) return;
    var link = e.target.closest('a:not([target="_blank"])');
    if (!link) return;
    var href = link.getAttribute('href');
    if (!isInternal(href)) return;
    if (link.closest('.player__menu') || link.closest('.song-card__menu') || link.closest('.dropdown')) return;
    if (link.hasAttribute('data-bs-toggle')) return;
    if (link.hasAttribute('data-song-id') && !link.closest('.player')) return;
    if (link.hasAttribute('data-play-now') || link.hasAttribute('data-queue-now')) return;
    if (link.hasAttribute('data-confirm')) {
      e.preventDefault();
      if (window.showConfirmModal) {
        window.showConfirmModal(href, link.getAttribute('data-confirm'));
      } else {
        window.location.href = href;
      }
      return;
    }

    e.preventDefault();

    // Force close any open offcanvas and remove all backdrops BEFORE navigation
    document.querySelectorAll('.offcanvas.show').forEach(function(o) {
      var inst = bootstrap.Offcanvas.getInstance(o);
      if (inst) inst.hide();
      o.classList.remove('show');
    });
    document.querySelectorAll('.modal-backdrop, .offcanvas-backdrop').forEach(function(el) { el.remove(); });
    document.body.classList.remove('modal-open');
    document.body.style.overflow = '';

    var url = href.indexOf('http') === 0 ? href : BASE + href.replace(/^\//, '');
    history.pushState({ pjax: url }, '', url);
    load(url);
  });

  window.addEventListener('popstate', function (e) {
    if (e.state && e.state.pjax) load(e.state.pjax);
  });

  /* ═══ Profile tabs — event delegation (dipanggil tiap PJAX load) ═══ */
  window.initProfileTabs = function() {
    var container = document.querySelector('.profile-tabs');
    if (!container || container._pjaxTabs) return;
    container._pjaxTabs = true;
    container.addEventListener('click', function(e) {
      var btn = e.target.closest('.profile-tabs__btn');
      if (!btn) return;
      document.querySelectorAll('.profile-tabs__btn').forEach(function(t) { t.classList.remove('profile-tabs__btn--active'); });
      document.querySelectorAll('.profile-panel').forEach(function(p) { p.classList.remove('profile-panel--active'); });
      btn.classList.add('profile-tabs__btn--active');
      var target = document.getElementById(btn.getAttribute('data-tab'));
      if (target) target.classList.add('profile-panel--active');
    });
  };
  // Panggil setiap kali content di-swap
  var _origLoad = load;
  load = function(url) {
    // Add cache-busting timestamp
    url += (url.indexOf('?') > -1 ? '&' : '?') + '_t=' + Date.now();
    _origLoad(url);
    setTimeout(function() { if (window.initProfileTabs) window.initProfileTabs(); }, 50);
  };
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', function() { window.initProfileTabs(); });
  } else {
    window.initProfileTabs();
  }

  /* ════════════════════════════════════════════
     ── Profile Page Functions (global for PJAX) ──
     ════════════════════════════════════════════ */

  window.switchTab = function(id) {
    document.querySelectorAll('.profile-tabs__btn').forEach(function(t) { t.classList.remove('profile-tabs__btn--active'); });
    document.querySelectorAll('.profile-panel').forEach(function(p) { p.classList.remove('profile-panel--active'); });
    var el = document.getElementById(id);
    if (el) el.classList.add('profile-panel--active');
    var btn = document.querySelector('[data-tab="' + id + '"]');
    if (btn) btn.classList.add('profile-tabs__btn--active');
    history.replaceState(null, '', '#tab=' + id);
  };

  window.saveTheme = function(key, val) {
    var x = new XMLHttpRequest();
    x.open('POST', BASE + 'user/save_pref_ajax', true);
    x.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
    x.onload = function() {
      if (x.status === 200) applyThemeCss(key, val);
    };
    x.send('field=' + encodeURIComponent(key) + '&value=' + encodeURIComponent(val));
  };

  window.saveThemeStay = function() {
    var theme = document.querySelector('[name=theme]:checked');
    var color = document.querySelector('[name=theme_color]:checked');
    var bg = document.getElementById('custom_bg_css');
    var params = '';
    if (theme) params += '&field=theme&value=' + encodeURIComponent(theme.value);
    if (color) params += '&field=theme_color&value=' + encodeURIComponent(color.value);
    if (bg && bg.value) params += '&field=theme_bg_css&value=' + encodeURIComponent(bg.value);
    if (!params) return;
    var x = new XMLHttpRequest();
    x.open('POST', BASE + 'user/save_pref_ajax', true);
    x.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
    x.onload = function() {
      if (x.status === 200) {
        if (theme) applyThemeCss('theme', theme.value);
        if (color) applyThemeCss('theme_color', color.value);
        if (bg && bg.value) applyThemeCss('theme_bg_css', bg.value);
      }
    };
    x.send(params.substring(1));
  };

  var THEMES = {
    cobalt:     { paper:'oklch(12% 0.006 250)', paper2:'oklch(16% 0.008 250)', paper3:'oklch(20% 0.008 250)', ink:'oklch(93% 0.005 80)', ink2:'oklch(85% 0.006 80)', muted:'oklch(60% 0.008 250)', neutral:'oklch(48% 0.015 250)', rule:'oklch(26% 0.015 250)' },
    midnight:   { paper:'oklch(10% 0.01 270)', paper2:'oklch(14% 0.012 270)', paper3:'oklch(18% 0.012 270)', ink:'oklch(94% 0.004 80)', ink2:'oklch(86% 0.005 80)', muted:'oklch(58% 0.01 270)', neutral:'oklch(45% 0.015 270)', rule:'oklch(24% 0.015 270)' },
    solar:      { paper:'oklch(97% 0.003 80)', paper2:'oklch(93% 0.004 80)', paper3:'oklch(89% 0.004 80)', ink:'oklch(14% 0.005 250)', ink2:'oklch(28% 0.006 250)', muted:'oklch(48% 0.008 80)', neutral:'oklch(60% 0.01 80)', rule:'oklch(84% 0.01 80)' },
    nord:       { paper:'oklch(15% 0.025 230)', paper2:'oklch(19% 0.03 230)', paper3:'oklch(23% 0.025 230)', ink:'oklch(90% 0.01 100)', ink2:'oklch(82% 0.01 100)', muted:'oklch(60% 0.03 230)', neutral:'oklch(48% 0.025 230)', rule:'oklch(30% 0.02 230)' },
    catppuccin: { paper:'oklch(15% 0.015 270)', paper2:'oklch(19% 0.02 270)', paper3:'oklch(23% 0.02 270)', ink:'oklch(93% 0.008 110)', ink2:'oklch(85% 0.01 110)', muted:'oklch(60% 0.03 300)', neutral:'oklch(48% 0.02 300)', rule:'oklch(28% 0.02 270)' },
    dracula:    { paper:'oklch(14% 0.025 280)', paper2:'oklch(18% 0.03 280)', paper3:'oklch(22% 0.025 280)', ink:'oklch(92% 0.01 110)', ink2:'oklch(84% 0.012 110)', muted:'oklch(55% 0.04 300)', neutral:'oklch(45% 0.03 280)', rule:'oklch(28% 0.025 280)' },
    monokai:    { paper:'oklch(13% 0.005 60)', paper2:'oklch(17% 0.008 60)', paper3:'oklch(21% 0.008 60)', ink:'oklch(95% 0.005 90)', ink2:'oklch(87% 0.006 90)', muted:'oklch(62% 0.01 60)', neutral:'oklch(50% 0.01 60)', rule:'oklch(27% 0.012 60)' },
    rosepine:   { paper:'oklch(13% 0.02 345)', paper2:'oklch(17% 0.025 345)', paper3:'oklch(21% 0.02 345)', ink:'oklch(92% 0.01 50)', ink2:'oklch(84% 0.012 50)', muted:'oklch(58% 0.04 345)', neutral:'oklch(48% 0.025 345)', rule:'oklch(28% 0.02 345)' },
    starshower: { paper:'oklch(12% 0.006 250)', paper2:'oklch(16% 0.008 250)', paper3:'oklch(20% 0.008 250)', ink:'oklch(93% 0.005 80)', ink2:'oklch(85% 0.006 80)', muted:'oklch(60% 0.008 250)', neutral:'oklch(48% 0.015 250)', rule:'oklch(26% 0.015 250)' },
    aurora:     { paper:'oklch(10% 0.01 250)', paper2:'oklch(14% 0.015 250)', paper3:'oklch(18% 0.015 250)', ink:'oklch(93% 0.005 80)', ink2:'oklch(85% 0.006 80)', muted:'oklch(55% 0.02 250)', neutral:'oklch(45% 0.015 250)', rule:'oklch(24% 0.015 250)' },
    matrix:     { paper:'oklch(8% 0.01 140)', paper2:'oklch(11% 0.015 140)', paper3:'oklch(14% 0.015 140)', ink:'oklch(80% 0.15 140)', ink2:'oklch(70% 0.12 140)', muted:'oklch(45% 0.10 140)', neutral:'oklch(35% 0.08 140)', rule:'oklch(18% 0.02 140)' },
    bubble:     { paper:'oklch(12% 0.01 270)', paper2:'oklch(16% 0.015 270)', paper3:'oklch(20% 0.015 270)', ink:'oklch(93% 0.01 80)', ink2:'oklch(85% 0.01 80)', muted:'oklch(58% 0.02 270)', neutral:'oklch(48% 0.015 270)', rule:'oklch(26% 0.015 270)' },
  };
  var ACCENTS = {
    blue:'oklch(62% 0.20 255)', purple:'oklch(60% 0.22 290)', green:'oklch(62% 0.20 145)',
    orange:'oklch(65% 0.20 70)', pink:'oklch(62% 0.20 350)', teal:'oklch(62% 0.18 190)',
    rose:'oklch(60% 0.22 20)', amber:'oklch(68% 0.22 85)',
  };

  function applyThemeCss(key, val) {
    var root = document.documentElement;
    if (key === 'theme') {
      var t = THEMES[val];
      if (!t) t = THEMES.cobalt;
      root.style.setProperty('--color-paper', t.paper);
      root.style.setProperty('--color-paper-2', t.paper2);
      root.style.setProperty('--color-paper-3', t.paper3);
      root.style.setProperty('--color-ink', t.ink);
      root.style.setProperty('--color-ink-2', t.ink2);
      root.style.setProperty('--color-muted', t.muted);
      root.style.setProperty('--color-neutral', t.neutral);
      root.style.setProperty('--color-rule', t.rule);
      var body = document.body;
      body.className = body.className.replace(/(?:^|\s)theme-anim\S*\s*/g, '').trim();
      if (['starshower','aurora','matrix','bubble'].indexOf(val) > -1) {
        body.classList.add('theme-anim', 'theme-' + val);
      }
    } else if (key === 'theme_color') {
      var a = ACCENTS[val] || ACCENTS.blue;
      root.style.setProperty('--color-accent', a);
      root.style.setProperty('--color-focus', a);
    } else if (key === 'theme_bg_css' && val) {
      root.style.setProperty('--bg-custom', val);
    }
  }

  /* ═══ Restore profile tab from hash on PJAX load ═══ */
  document.addEventListener('pjax:complete', function() {
    var m = window.location.hash.match(/tab=([a-z-]+)/);
    if (m && window.switchTab) window.switchTab(m[1]);
  });

})();
