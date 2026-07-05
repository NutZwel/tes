<div class="player" id="player" role="region" aria-label="Music player">
  <div class="player__inner">

    <!-- Song Info -->
    <a href="#" class="player__info-link" id="player-info-link">
      <div class="player__art">
        <div class="player__art-placeholder" id="player-art">
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M9 18V5l12-2v13"/><circle cx="6" cy="18" r="3"/><circle cx="18" cy="16" r="3"/></svg>
        </div>
        <img src="" alt="" class="player__art-img" id="player-art-img" style="display:none" width="48" height="48">
      </div>
      <div class="player__text">
        <span class="player__title" id="player-title" style="font-family: var(--font-display);">No track selected</span>
        <span class="player__artist" id="player-artist">—</span>
      </div>
    </a>

    <!-- Controls -->
    <div class="player__controls">
      <button class="player__btn player__btn--prev" id="player-prev" title="Previous">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor"><polygon points="19 20 9 12 19 4 19 20"/><line x1="5" y1="19" x2="5" y2="5" stroke="currentColor" stroke-width="2"/></svg>
      </button>
      <button class="player__btn player__btn--play" id="player-play" title="Play">
        <svg width="22" height="22" viewBox="0 0 24 24" fill="currentColor" id="player-play-icon"><polygon points="8,5 19,12 8,19"/></svg>
      </button>
      <button class="player__btn player__btn--next" id="player-next" title="Next">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor"><polygon points="5 4 15 12 5 20 5 4"/><line x1="19" y1="5" x2="19" y2="19" stroke="currentColor" stroke-width="2"/></svg>
      </button>
    </div>

    <!-- Progress -->
    <div class="player__progress">
      <span class="player__time" id="player-time-current">0:00</span>
      <div class="player__bar" id="player-bar">
        <div class="player__bar-track">
          <div class="player__bar-fill" id="player-bar-fill" style="width:0%"></div>
        </div>
      </div>
      <span class="player__time" id="player-time-total">0:00</span>
    </div>

    <!-- Volume + Menu -->
    <div class="player__extras">
      <button class="player__btn player__btn--volume" id="player-volume" title="Volume">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" id="player-vol-icon"><polygon points="11 5 6 9 2 9 2 15 6 15 11 19 11 5"/><path d="M19.07 4.93a10 10 0 0 1 0 14.14M15.54 8.46a5 5 0 0 1 0 7.07"/></svg>
      </button>
      <div class="player__volume-slider">
        <input type="range" class="player__range--vol" id="player-vol-range" value="80" min="0" max="100" aria-label="Volume">
      </div>

      <button class="player__btn player__btn--lyrics" id="player-lyrics-btn" title="Lyrics">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" id="player-lyrics-icon">
          <path d="M12 20h9"/>
          <path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4 9.5-9.5z"/>
          <circle cx="6" cy="16" r="2"/>
          <circle cx="18" cy="14" r="2"/>
        </svg>
      </button>

      <button class="player__btn player__btn--loop" id="player-loop-btn" title="Sequential">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" id="player-loop-icon"><polyline points="17 1 21 5 17 9"/><path d="M3 11V9a4 4 0 0 1 4-4h14"/><polyline points="7 23 3 19 7 15"/><path d="M21 13v2a4 4 0 0 1-4 4H3"/></svg>
      </button>

      <div class="player__menu-wrapper">
        <button class="player__btn player__btn--menu" id="player-menu-btn" title="More">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor"><circle cx="12" cy="5" r="2"/><circle cx="12" cy="12" r="2"/><circle cx="12" cy="19" r="2"/></svg>
        </button>
        <div class="player__menu" id="player-menu" role="menu" style="display:none">
          <div class="player__menu-section">
            <span class="player__menu-section-title">Up Next</span>
            <ul class="player__menu-queue" id="player-menu-queue">
              <li class="player__menu-queue-empty">No songs in queue</li>
            </ul>
          </div>
          <div class="player__menu-actions">
            <button class="player__menu-item" data-action="add-queue" type="button" role="menuitem">
              <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><polyline points="17 1 21 5 17 9"/><path d="M3 11V9a4 4 0 0 1 4-4h14"/><polyline points="7 23 3 19 7 15"/><path d="M21 13v2a4 4 0 0 1-4 4H3"/></svg>
              Queue
            </button>
            <button class="player__menu-item" data-action="playlist" type="button" role="menuitem">
              <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><line x1="8" y1="6" x2="21" y2="6"/><line x1="8" y1="12" x2="21" y2="12"/><line x1="8" y1="18" x2="21" y2="18"/><line x1="3" y1="6" x2="3.01" y2="6"/><line x1="3" y1="12" x2="3.01" y2="12"/><line x1="3" y1="18" x2="3.01" y2="18"/></svg>
              Playlist
            </button>
            <button class="player__menu-item" data-action="favorite" type="button" role="menuitem">
              <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/></svg>
              Favorite
            </button>
          </div>
        </div>
      </div>
    </div>

  </div>
</div>

<!-- ═══ Lyrics Panel (full-screen overlay) ═══ -->
<div class="player__lyrics-panel" id="player-lyrics-panel" style="display:none">
  <div class="player__lyrics-backdrop" id="player-lyrics-close-bg"></div>
  <div class="player__lyrics-container">
    <!-- Left: Cover + Controls -->
    <div class="player__lyrics-left">
      <div class="player__lyrics-art">
        <div class="player__lyrics-art-img" id="lyrics-art-placeholder" style="display:none;">
          <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1" opacity="0.4"><path d="M9 18V5l12-2v13"/><circle cx="6" cy="18" r="3"/><circle cx="18" cy="16" r="3"/></svg>
        </div>
        <img src="" alt="" id="lyrics-art-img" class="player__lyrics-art-img" style="display:none" width="240" height="240">
      </div>
      <h3 class="player__lyrics-song-title" id="lyrics-song-title">—</h3>
      <p class="player__lyrics-artist" id="lyrics-artist">—</p>
      <div class="player__lyrics-controls">
        <button class="player__lyrics-btn" id="lyrics-prev" title="Previous">
          <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor"><polygon points="19 20 9 12 19 4 19 20"/><line x1="5" y1="19" x2="5" y2="5" stroke="currentColor" stroke-width="2"/></svg>
        </button>
        <button class="player__lyrics-btn player__lyrics-btn--play" id="lyrics-play" title="Play">
          <svg width="28" height="28" viewBox="0 0 24 24" fill="currentColor" id="lyrics-play-icon"><polygon points="8,5 19,12 8,19"/></svg>
        </button>
        <button class="player__lyrics-btn" id="lyrics-next" title="Next">
          <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor"><polygon points="5 4 15 12 5 20 5 4"/><line x1="19" y1="5" x2="19" y2="19" stroke="currentColor" stroke-width="2"/></svg>
        </button>
      </div>
      <!-- Lyrics progress bar -->
      <div class="player__lyrics-progress">
        <span class="player__lyrics-time" id="lyrics-time-current">0:00</span>
        <div class="player__lyrics-bar" id="lyrics-bar">
          <div class="player__lyrics-bar-track">
            <div class="player__lyrics-bar-fill" id="lyrics-bar-fill" style="width:0%"></div>
          </div>
        </div>
        <span class="player__lyrics-time" id="lyrics-time-total">0:00</span>
      </div>
    </div>

    <!-- Right: Scrollable lyrics -->
    <div class="player__lyrics-right">
      <button class="player__lyrics-close" id="player-lyrics-close" title="Close">&times;</button>
      <div class="player__lyrics-content" id="lyrics-content">
        <p class="player__lyrics-empty">Select a song to see its lyrics.</p>
      </div>
    </div>
  </div>
</div>

<audio id="player-audio" preload="auto" style="display:none"></audio>
