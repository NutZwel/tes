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
    // Profile page must full reload (tabs need fresh DOM)
    if (href.indexOf('/user') > -1 || href.indexOf('/profile') > -1) return false;
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
    var navMap = { 'dashboard': 'dashboard', 'catalog': 'catalog', 'playlist': 'playlist', 'playlists': 'playlist', 'user': 'user', 'profile': 'user', 'admin': 'admin' };
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

})();
