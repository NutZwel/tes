/**
 * Laufey — Drag-to-Scroll Carousel with arrow navigation
 */
(function () {
  'use strict';

  function initCarousel(wrap) {
    var track = wrap.querySelector('.carousel__track');
    if (!track || track._dragInit) return;
    track._dragInit = true;

    var isDown = false, startX = 0, scrollLeft = 0, vel = 0, animationId = null;
    var prevX = 0, prevTime = 0;
    var isDragging = false, dragDist = 0;

    // Find arrow buttons (sibling or parent's children)
    var carousel = wrap.closest('.carousel');
    var prevBtn = carousel ? carousel.querySelector('.carousel__arrow--prev') : null;
    var nextBtn = carousel ? carousel.querySelector('.carousel__arrow--next') : null;

    function getMaxScroll() {
      return wrap.scrollWidth - wrap.clientWidth;
    }


    if (prevBtn) {
      prevBtn.addEventListener('mousedown', function (e) { e.stopPropagation(); });
      prevBtn.addEventListener('click', function (e) {
        e.preventDefault();
        if (isDown) { isDown = false; wrap.style.cursor = ''; }
        var card = track.querySelector('.carousel__card');
        var cardWidth = card ? card.offsetWidth + 16 : 260;
        wrap.scrollLeft -= cardWidth;
      });
    }
    if (nextBtn) {
      nextBtn.addEventListener('mousedown', function (e) { e.stopPropagation(); });
      nextBtn.addEventListener('click', function (e) {
        e.preventDefault();
        if (isDown) { isDown = false; wrap.style.cursor = ''; }
        var card = track.querySelector('.carousel__card');
        var cardWidth = card ? card.offsetWidth + 16 : 260;
        wrap.scrollLeft += cardWidth;
      });
    }

    function onStart(clientX) {
      isDown = true;
      isDragging = false;
      startX = clientX;
      dragDist = 0;
      scrollLeft = wrap.scrollLeft;
      prevX = clientX;
      prevTime = Date.now();
      wrap.style.cursor = 'grabbing';
      wrap.style.userSelect = 'none';
      if (animationId) { cancelAnimationFrame(animationId); animationId = null; }
      vel = 0;
    }

    function onMove(clientX) {
      if (!isDown) return;
      var dx = clientX - startX;
      dragDist = Math.abs(dx);
      if (dragDist > 8) isDragging = true;
      wrap.scrollLeft = scrollLeft - dx;

      var now = Date.now();
      if (now - prevTime > 40) {
        vel = prevX - clientX;
        prevX = clientX;
        prevTime = now;
      }
    }

    function onEnd() {
      if (!isDown) return;
      isDown = false;
      wrap.style.cursor = '';
      wrap.style.userSelect = '';

      if (isDragging && Math.abs(vel) > 3) {
        var friction = 0.93;
        var stopThreshold = 0.8;
        var maxScroll = getMaxScroll();

        function step() {
          var atLeft = wrap.scrollLeft <= 0;
          var atRight = wrap.scrollLeft >= maxScroll;

          if (atLeft || atRight) {
            vel *= 0.5;
            if (atLeft) wrap.scrollLeft = Math.max(0, wrap.scrollLeft + Math.abs(vel) * 0.3);
            if (atRight) wrap.scrollLeft = Math.min(maxScroll, wrap.scrollLeft - Math.abs(vel) * 0.3);
          }

          vel *= friction;
          wrap.scrollLeft -= vel;

          if (wrap.scrollLeft < 0) { wrap.scrollLeft = 0; vel = 0; }
          if (wrap.scrollLeft > maxScroll) { wrap.scrollLeft = maxScroll; vel = 0; }

          if (Math.abs(vel) > stopThreshold) {
            animationId = requestAnimationFrame(step);
          } else {
            animationId = null;
          }
        }
        animationId = requestAnimationFrame(step);
      }

      if (isDragging) {
        wrap.classList.add('is-dragging');
        requestAnimationFrame(function () {
          requestAnimationFrame(function () {
            wrap.classList.remove('is-dragging');
          });
        });
      }
    }

    // Mouse drag
    function handleDown(e) {
      if (e.button !== 0) return;
      if (e.target.closest('.carousel__arrow')) return;
      if (e.target.closest('.dropdown') || e.target.closest('button')) return;
      // Safety: reset if stuck from previous interaction
      if (isDown) { isDown = false; }
      onStart(e.clientX);
    }
    // Listen on wrap (not just track) to catch drags starting from anywhere in carousel
    wrap.addEventListener('mousedown', handleDown);
    document.addEventListener('mousemove', function (e) {
      if (!isDown) return;
      e.preventDefault();
      onMove(e.clientX);
    });
    document.addEventListener('mouseup', function () {
      if (isDown) onEnd();
    });

    // Touch events
    track.addEventListener('touchstart', function (e) {
      if (e.target.closest('.dropdown') || e.target.closest('button')) return;
      onStart(e.touches[0].clientX);
    }, { passive: true });
    track.addEventListener('touchmove', function (e) {
      if (!isDown) return;
      onMove(e.touches[0].clientX);
    }, { passive: true });
    track.addEventListener('touchend', function () {
      if (isDown) onEnd();
    }, { passive: true });
    track.addEventListener('touchcancel', function () {
      if (isDown) onEnd();
    }, { passive: true });
  }

  function initAll() {
    document.querySelectorAll('.carousel__wrap').forEach(initCarousel);
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initAll);
  } else {
    initAll();
  }

  document.addEventListener('pjax:complete', function () {
    setTimeout(initAll, 50);
  });

  window.initCarousels = initAll;
})();
