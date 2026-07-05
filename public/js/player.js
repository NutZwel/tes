/**
 * Laufey — Bottom Music Player (Bootstrap version)
 */
(function () {
  'use strict';

  var audio = document.getElementById('player-audio');
  var player = document.getElementById('player');
  var playBtn = document.getElementById('player-play');
  var playIcon = document.getElementById('player-play-icon');
  var prevBtn = document.getElementById('player-prev');
  var nextBtn = document.getElementById('player-next');
  var titleEl = document.getElementById('player-title');
  var artistEl = document.getElementById('player-artist');
  var artImg = document.getElementById('player-art-img');
  var artPlaceholder = document.getElementById('player-art');
  var timeCurrent = document.getElementById('player-time-current');
  var timeTotal = document.getElementById('player-time-total');
  var barFill = document.getElementById('player-bar-fill');
  var volRange = document.getElementById('player-vol-range');
  var volIcon = document.getElementById('player-vol-icon');
  var menuQueueList = document.getElementById('player-menu-queue');
  var infoLink = document.getElementById('player-info-link');
  var loopBtn = document.getElementById('player-loop-btn');
  var loopIcon = document.getElementById('player-loop-icon');
  var menuBtn = document.getElementById('player-menu-btn');
  var menuEl = document.getElementById('player-menu');

  var QUEUE = [], QUEUE_IDX = -1, PLAYING = false, SONG_ID = 0, LOOP = 0;
  var PLAYED_IDS = [];
  var PLAY_HISTORY = [];
  var CLICK_COUNT = 0, CLICK_TIMER = null;
  var audioInitialized = false;
  var STORAGE_KEY = 'laufey_player_state';

  function initAudio() {
    if (audioInitialized) return;
    audioInitialized = true;
    // Mobile autoplay fix: need user interaction first
    audio.load();
  }

  function markPlayed(id) { if (PLAYED_IDS.indexOf(id)===-1) PLAYED_IDS.push(id); }

  /* Global helper for inline onclick (Continue Listening cards etc.) */
  window.playSongById = function(id) {
    if (!id) return;
    var x = new XMLHttpRequest();
    x.open('GET', BASE + 'player/info/' + id, true);
    x.onload = function() {
      if (x.status === 200) {
        var d = JSON.parse(x.responseText);
        playSong({id: d.id, title: d.title, artist: d.artist, file_path: d.file_path, cover_path: d.cover_path});
      } else {
        toast('Could not load song');
        console.warn('playSongById: status ' + x.status + ' for ID ' + id);
      }
    };
    x.onerror = function() { toast('Network error'); };
    x.send();
  };

  function setSongInfo(song) {
    titleEl.textContent = song.title || 'Unknown';
    artistEl.textContent = song.artist || '—';
    infoLink.href = BASE + 'song/' + song.id;
    if (song.cover_path) {
      artImg.src = song.cover_path.indexOf('http') === 0 ? song.cover_path : BASE + song.cover_path;
      artImg.style.display = 'block';
      artPlaceholder.style.display = 'none';
    } else {
      artImg.style.display = 'none';
      artPlaceholder.style.display = 'flex';
    }
  }

  /* ═══ Now Playing indicator on Continue Listening cards ═══ */
  function updateNowPlayingIndicator() {
    var cards = document.querySelectorAll('.continue-listening-card');
    for (var i = 0; i < cards.length; i++) {
      cards[i].classList.remove('is-playing');
      if (parseInt(cards[i].getAttribute('data-song-id'), 10) === SONG_ID && PLAYING) {
        cards[i].classList.add('is-playing');
      }
    }
  }

  /* ═══ Refresh Continue Listening cards on dashboard ═══ */
  function refreshContinueListening() {
    var wrap = document.querySelector('#continue-listening .carousel__wrap');
    var track = wrap ? wrap.querySelector('.carousel__track') : null;
    if (!track) return;
    var x = new XMLHttpRequest();
    x.open('GET', BASE + 'dashboard/continue_listening?_t=' + Date.now() + '&current_id=' + SONG_ID, true);
    x.onload = function() {
      if (x.status === 200 && x.responseText.trim()) {
        track.innerHTML = x.responseText;
        updateNowPlayingIndicator();
        // Wait for layout, then scroll the now-playing card to left
        requestAnimationFrame(function() {
          requestAnimationFrame(function() {
            var playingCard = track.querySelector('.is-playing');
            if (playingCard) {
              // Get card position relative to wrap and scroll to it
              wrap.scrollLeft = playingCard.offsetLeft - 16;
            } else {
              wrap.scrollLeft = 0;
            }
          });
        });
      }
    };
    x.send();
  }

  /* Sync now-playing indicator on timeupdate */
  audio.addEventListener('timeupdate', function() {
    if (!audio.duration) return;
    var p = (audio.currentTime / audio.duration) * 100;
    barFill.style.width = p + '%';
    timeCurrent.textContent = fmt(audio.currentTime);
    // Update now playing indicator
    updateNowPlayingIndicator();
  });

  function loadSong(song) {
    SONG_ID = song.id;
    markPlayed(song.id);
    setSongInfo(song);
    player.classList.add('show');
    player.style.transform = 'translateY(0)';
    audio.src = BASE + 'player/stream/' + song.id;
    audio.load();
    PLAYING = true;
    updatePlayBtn();
    refreshContinueListening();
    audio.play().catch(function(e) {
      console.warn(e);
      // Retry once (handles mobile autoplay policy)
      setTimeout(function() {
        audio.play().catch(function(e2) { console.warn(e2); });
      }, 500);
    });
  }

  function playSong(song) {
    CLICK_COUNT = 0;
    PLAY_HISTORY = [];
    QUEUE = [song];
    QUEUE_IDX = 0;
    loadSong(song);
  }
  window.playSong = playSong;

  window.addToQueue = function(song) {
    if (QUEUE.some(function(s){return s.id===song.id;})) { toast('Already in queue'); return; }
    QUEUE.push(song); toast(song.title+' added to queue');
  };

  window.addToFavorites = function(sid) {
    var x = new XMLHttpRequest(); x.open('POST',BASE+'favorites/add',true);
    x.setRequestHeader('Content-Type','application/x-www-form-urlencoded');
    x.onload=function(){if(x.status===200)toast('Added to favorites!');}; x.send('song_id='+sid);
  };

  window.addToPlaylist = function(sid) {
    if (window.showPlaylistPicker) window.showPlaylistPicker(sid);
  };

  function togglePlay() {
    if(!audio.src)return;
    if(audio.paused){audio.play();PLAYING=true;}else{audio.pause();PLAYING=false;}
    updatePlayBtn();
  }
  function updatePlayBtn() {
    playIcon.innerHTML=PLAYING?'<rect x="6" y="4" width="4" height="16"/><rect x="14" y="4" width="4" height="16"/>':'<polygon points="8,5 19,12 8,19"/>';
  }

  function nextTrack() {
    if (!SONG_ID) return;
    if (LOOP === 2) {
      audio.currentTime = 0;
      audio.play().catch(function(e){console.warn(e);});
      return;
    }
    // Save current to history
    PLAY_HISTORY.push(SONG_ID);

    // If there are queued songs waiting, play the next one from queue
    var upcoming = QUEUE.slice(QUEUE_IDX + 1);
    if (upcoming.length > 0) {
      QUEUE_IDX++;
      loadIdx();
      return;
    }
    // No queued songs — pick random from catalog
    shuffleFromCatalog();
  }
  function prevTrack() {
    if (!SONG_ID) return;
    // Pop last song from history and play it
    var prevId = PLAY_HISTORY.pop();
    if (!prevId) return;
    var song = null;
    // Look in QUEUE first
    for (var i = 0; i < QUEUE.length; i++) {
      if (QUEUE[i].id === prevId) { song = QUEUE[i]; QUEUE_IDX = i; break; }
    }
    if (song) {
      loadSong(song);
    } else {
      // Fetch from server
      var x = new XMLHttpRequest();
      x.open('GET', BASE + 'player/info/' + prevId, false);
      x.send();
      if (x.status === 200) {
        var d = JSON.parse(x.responseText);
        if (d && d.id) {
          QUEUE = [{id:d.id,title:d.title,artist:d.artist,file_path:d.file_path,cover_path:d.cover_path}];
          QUEUE_IDX = 0;
          loadSong(QUEUE[0]);
        }
      }
    }
  }
  function loadIdx() {
    var song=QUEUE[QUEUE_IDX];if(!song)return;
    SONG_ID = song.id;
    markPlayed(song.id);
    setSongInfo(song);
    if (player.style.transform !== 'translateY(0)') {
      player.classList.add('show');
      player.style.transform = 'translateY(0)';
    }
    audio.src=BASE+'player/stream/'+song.id;
    audio.load();
    PLAYING = true;
    updatePlayBtn();
    audio.play().catch(function(e) {
      console.warn(e);
      // Retry once (handles mobile autoplay policy & buffering)
      setTimeout(function() {
        audio.play().catch(function(e2) { console.warn(e2); });
      }, 300);
    });
  }
  function stopPb() {
    audio.pause();audio.src='';SONG_ID=0;PLAYING=false;
    player.style.transform='translateY(100%)';player.classList.remove('show');
    updatePlayBtn();timeCurrent.textContent='0:00';timeTotal.textContent='0:00';barFill.style.width='0%';
  }

  var SHUFFLE_NEXT=null;
  function shuffleFromCatalog(){
    if(SHUFFLE_NEXT){var s=SHUFFLE_NEXT;SHUFFLE_NEXT=null;QUEUE=[s];QUEUE_IDX=0;loadIdx();prefetchShuffle();return;}
    var excl=PLAYED_IDS.join(',');
    // Synchronous XHR — keeps user gesture alive so audio.play() isn't blocked
    var x=new XMLHttpRequest();x.open('GET',BASE+'player/random?exclude='+excl,false);x.send();
    if(x.status===200){var d=JSON.parse(x.responseText);if(d&&d.id){QUEUE=[{id:d.id,title:d.title,artist:d.artist,file_path:d.file_path,cover_path:d.cover_path}];QUEUE_IDX=0;loadIdx();prefetchShuffle();return;}}
    if(x.status===404){PLAYED_IDS=[];shuffleFromCatalog();}
  }
  function prefetchShuffle(){
    var excl=PLAYED_IDS.join(',');
    var x=new XMLHttpRequest();x.open('GET',BASE+'player/random?exclude='+excl,true);
    x.onload=function(){if(x.status===200){var d=JSON.parse(x.responseText);if(d&&d.id)SHUFFLE_NEXT={id:d.id,title:d.title,artist:d.artist,file_path:d.file_path,cover_path:d.cover_path};}};x.send();
  }

  playBtn.addEventListener('click',togglePlay);
  prevBtn.addEventListener('click',prevTrack);
  nextBtn.addEventListener('click',nextTrack);

  audio.addEventListener('loadedmetadata',function(){timeTotal.textContent=fmt(audio.duration);});
  audio.addEventListener('ended', function() {
    // Auto-play next track when current ends
    nextTrack();
  });

  var bar=document.getElementById('player-bar');
  bar.addEventListener('click',function(e){
    var r=this.getBoundingClientRect();var pct=(e.clientX-r.left)/r.width;
    if(audio.duration)audio.currentTime=pct*audio.duration;
  });

  volRange.addEventListener('input',function(){audio.volume=this.value/100;updateVolIcon();});
  function updateVolIcon(){
    var v=audio.volume;
    if(v===0)volIcon.innerHTML='<polygon points="11 5 6 9 2 9 2 15 6 15 11 19 11 5"/><line x1="23" y1="9" x2="17" y2="15"/><line x1="17" y1="9" x2="23" y2="15"/>';
    else if(v<0.5)volIcon.innerHTML='<polygon points="11 5 6 9 2 9 2 15 6 15 11 19 11 5"/><path d="M15.54 8.46a5 5 0 0 1 0 7.07"/>';
    else volIcon.innerHTML='<polygon points="11 5 6 9 2 9 2 15 6 15 11 19 11 5"/><path d="M19.07 4.93a10 10 0 0 1 0 14.14M15.54 8.46a5 5 0 0 1 0 7.07"/>';
  }

  loopBtn.addEventListener('click',function(){LOOP=(LOOP+1)%3;updateLoopBtn();});
  function updateLoopBtn(){
    if(LOOP===0){loopIcon.innerHTML='<polyline points="17 1 21 5 17 9"/><path d="M3 11V9a4 4 0 0 1 4-4h14"/><polyline points="7 23 3 19 7 15"/><path d="M21 13v2a4 4 0 0 1-4 4H3"/>';loopBtn.classList.remove('text-primary');}
    else if(LOOP===1){loopIcon.innerHTML='<polyline points="16 3 21 3 21 8"/><line x1="4" y1="20" x2="21" y2="3"/><polyline points="21 16 21 21 16 21"/><line x1="15" y1="15" x2="21" y2="21"/><line x1="4" y1="4" x2="9" y2="9"/>';loopBtn.classList.add('text-primary');}
    else{loopIcon.innerHTML='<polyline points="17 1 21 5 17 9"/><path d="M3 11V9a4 4 0 0 1 4-4h14"/><polyline points="7 23 3 19 7 15"/><path d="M21 13v2a4 4 0 0 1-4 4H3"/><line x1="2" y1="2" x2="22" y2="22"/>';loopBtn.classList.add('text-primary');}
  }updateLoopBtn();

  infoLink.addEventListener('click',function(e){if(!SONG_ID)e.preventDefault();});

  function fmt(sec){if(!sec||isNaN(sec))return'0:00';var m=Math.floor(sec/60);var s=Math.floor(sec%60);return m+':'+(s<10?'0':'')+s;}
  function toast(msg){
    var el=document.createElement('div');el.className='position-fixed bottom-0 start-50 translate-middle-x mb-5 p-2 bg-dark text-light border border-secondary rounded-2 shadow';el.style.zIndex='9999';el.textContent=msg;
    document.body.appendChild(el);setTimeout(function(){el.remove();},2000);
  }

  /* ═══ Play from data-song-id — capture phase to beat Bootstrap stopPropagation ═══ */
  document.addEventListener('click',function(e){
    /* Skip clicks inside dropdown or on buttons entirely */
    if (e.target.closest('.dropdown') || e.target.closest('button')) return;
    /* Skip if inside a carousel that's currently being dragged */
    if (e.target.closest('.carousel__wrap.is-dragging')) return;
    var link=e.target.closest('[data-song-id]');
    if(!link||link.closest('.player__menu'))return;
    e.preventDefault();
    var id=parseInt(link.getAttribute('data-song-id'),10);
    if(!id)return;
    var x=new XMLHttpRequest();x.open('GET',BASE+'player/info/'+id,true);
    x.onload=function(){if(x.status===200){var d=JSON.parse(x.responseText);playSong({id:d.id,title:d.title,artist:d.artist,file_path:d.file_path,cover_path:d.cover_path});}};x.send();
  }, true); /* ← capture phase */

  /* ═══ Play from data-play-now ═══ */
  document.addEventListener('click',function(e){
    /* Skip clicks inside dropdown or on buttons */
    if (e.target.closest('.dropdown') || e.target.closest('button')) return;
    var btn=e.target.closest('[data-play-now]');if(!btn)return;e.preventDefault();
    var id=parseInt(btn.getAttribute('data-play-now'),10);
    if(!id)return;
    var x=new XMLHttpRequest();x.open('GET',BASE+'player/info/'+id,true);
    x.onload=function(){if(x.status===200){var d=JSON.parse(x.responseText);playSong({id:d.id,title:d.title,artist:d.artist,file_path:d.file_path,cover_path:d.cover_path});}else{toast('Could not load song');}};
    x.onerror=function(){toast('Network error');};
    x.send();
  });

  /* ═══ Queue from data-queue-now ═══ */
  document.addEventListener('click',function(e){
    var btn=e.target.closest('[data-queue-now]');if(!btn)return;e.preventDefault();
    var id=parseInt(btn.getAttribute('data-queue-now'),10);
    var x=new XMLHttpRequest();x.open('GET',BASE+'player/info/'+id,true);
    x.onload=function(){if(x.status===200){var d=JSON.parse(x.responseText);window.addToQueue({id:d.id,title:d.title,artist:d.artist,file_path:d.file_path,cover_path:d.cover_path});}};x.send();
  });

  /* ═══ All song action buttons (titik 3 catalog, playlist) ═══ */
  document.addEventListener('click',function(e){
    if (!e.isTrusted) return; /* skip synthetic events */
    var item=e.target.closest('[data-action]:not([data-action="remove"])');
    if(!item)return;
    /* Skip clicks from inside player menu — handled separately */
    if (item.closest('.player__menu')) return;
    var action=item.getAttribute('data-action');
    var sid=parseInt(item.getAttribute('data-song-id'),10);
    if(!sid)return;
    if(action==='queue'){
      var x=new XMLHttpRequest();x.open('GET',BASE+'player/info/'+sid,true);
      x.onload=function(){if(x.status===200){var d=JSON.parse(x.responseText);window.addToQueue({id:d.id,title:d.title,artist:d.artist,file_path:d.file_path,cover_path:d.cover_path});}};x.send();
    }else if(action==='favorite'){window.addToFavorites(sid);}
    else if(action==='playlist'){
      e.preventDefault();
      window.addToPlaylist(sid);
    }
  });

  /* ═══ Player menu actions (separate handler with stopPropagation) ═══ */
  if (menuEl) {
    menuEl.addEventListener('click', function(e) {
      e.stopPropagation();
      var item = e.target.closest('[data-action]');
      if (!item) return;
      var action = item.getAttribute('data-action');
      menuEl.style.display = 'none';
      menuEl.classList.remove('is-open');
      if (action === 'add-queue' && SONG_ID) {
        var x = new XMLHttpRequest();
        x.open('GET', BASE + 'player/info/' + SONG_ID, true);
        x.onload = function() { if (x.status === 200) { var d = JSON.parse(x.responseText); window.addToQueue({id: d.id, title: d.title, artist: d.artist, file_path: d.file_path, cover_path: d.cover_path, stream_url: d.stream_url }); } };
        x.send();
      } else if (action === 'playlist' && SONG_ID) {
        window.addToPlaylist(SONG_ID);
      } else if (action === 'favorite' && SONG_ID) {
        window.addToFavorites(SONG_ID);
      }
    });
  }

  /* ═══ Remove from playlist (separate to avoid conflicts) ═══ */
  document.addEventListener('click',function(e){
    var item=e.target.closest('[data-action="remove"]');
    if(!item)return;
    var sid=parseInt(item.getAttribute('data-song-id'),10);
    if(!sid)return;
    var p=window.location.pathname.split('/'),plId=p[p.length-1];
    if(plId&&!isNaN(plId)){var f=document.createElement('form');f.method='post';f.action=BASE+'playlist/remove_song/'+plId;var i=document.createElement('input');i.type='hidden';i.name='song_id';i.value=sid;f.appendChild(i);document.body.appendChild(f);f.submit();}
  });

  /* ═══ Player 3-dot menu toggle ═══ */
  if(menuBtn&&menuEl){
    menuBtn.addEventListener('click',function(e){
      e.preventDefault();e.stopPropagation();
      var isOpen=menuEl.classList.contains('is-open');
      menuEl.classList.toggle('is-open',!isOpen);
      menuEl.style.display=isOpen?'none':'block';
    });

    /* ═══ Close player menu when clicking outside ═══ */
    document.addEventListener('click',function(e){
      if(!menuEl.contains(e.target)&&!menuBtn.contains(e.target)){
        menuEl.classList.remove('is-open');
        menuEl.style.display='none';
      }
    },true);

    /* ═══ Close menu on Escape ═══ */
    document.addEventListener('keydown',function(e){
      if(e.key==='Escape'){
        menuEl.classList.remove('is-open');
        menuEl.style.display='none';
      }
    });
  }

  /* ═══ Player menu: just close after any click, actions handled globally ═══ */
  menuEl.addEventListener('click',function(){
    menuEl.classList.remove('is-open');
    menuEl.style.display='none';
  });

  /* ════════════════════════════════════════════
     ── Lyrics Panel ──
     ════════════════════════════════════════════ */
  var lyricsBtn = document.getElementById('player-lyrics-btn');
  var lyricsPanel = document.getElementById('player-lyrics-panel');
  var lyricsClose = document.getElementById('player-lyrics-close');
  var lyricsCloseBg = document.getElementById('player-lyrics-close-bg');
  var lyricsContent = document.getElementById('lyrics-content');
  var lyricsArtImg = document.getElementById('lyrics-art-img');
  var lyricsArtPlaceholder = document.getElementById('lyrics-art-placeholder');
  var lyricsSongTitle = document.getElementById('lyrics-song-title');
  var lyricsArtist = document.getElementById('lyrics-artist');
  var lyricsPlay = document.getElementById('lyrics-play');
  var lyricsPlayIcon = document.getElementById('lyrics-play-icon');
  var lyricsPrev = document.getElementById('lyrics-prev');
  var lyricsNext = document.getElementById('lyrics-next');
  var lyricsBarFill = document.getElementById('lyrics-bar-fill');
  var lyricsTimeCur = document.getElementById('lyrics-time-current');
  var lyricsTimeTotal = document.getElementById('lyrics-time-total');
  var lyricsBar = document.getElementById('lyrics-bar');
  var LRC_DATA = [], LRC_INTERVAL = null;

  function parseLRC(lrc) {
    var lines = lrc.split('\n');
    var result = [];
    var re = /\[(\d{1,3}):(\d{2})\.(\d{2,3})\]\s*(.*)/;
    for (var i = 0; i < lines.length; i++) {
      var m = lines[i].match(re);
      if (m) {
        var sec = parseInt(m[1],10)*60 + parseInt(m[2],10) + parseInt(m[3],10)/1000;
        result.push({ time: sec, text: m[4].trim() });
      }
    }
    result.sort(function(a,b){ return a.time - b.time; });
    return result;
  }

  function formatLrcTime(sec) {
    if (!sec || isNaN(sec)) return '0:00';
    var m = Math.floor(sec / 60);
    var s = Math.floor(sec % 60);
    return m + ':' + (s < 10 ? '0' : '') + s;
  }

  function updateLyricsPlayBtn() {
    lyricsPlayIcon.innerHTML = PLAYING && !audio.paused ?
      '<rect x="6" y="4" width="4" height="16"/><rect x="14" y="4" width="4" height="16"/>' :
      '<polygon points="8,5 19,12 8,19"/>';
  }

  function startLrcSync() {
    stopLrcSync();
    var prevActiveIdx = -1;
    LRC_INTERVAL = setInterval(function() {
      if (!audio.duration || LRC_DATA.length === 0) return;
      var ct = audio.currentTime;
      var activeIdx = -1;
      for (var i = LRC_DATA.length - 1; i >= 0; i--) {
        if (ct >= LRC_DATA[i].time) { activeIdx = i; break; }
      }
      if (activeIdx === prevActiveIdx) return; // no change, skip DOM
      prevActiveIdx = activeIdx;
      var lines = lyricsContent.querySelectorAll('.lyrics-line');
      for (var j = 0; j < lines.length; j++) {
        lines[j].classList.remove('is-active', 'is-past');
      }
      if (activeIdx >= 0 && lines[activeIdx]) {
        lines[activeIdx].classList.add('is-active');
        lines[activeIdx].scrollIntoView({ block: 'center', behavior: 'auto' });
        for (var k = 0; k < activeIdx; k++) {
          if (lines[k]) lines[k].classList.add('is-past');
        }
      }
    }, 200);
  }

  function stopLrcSync() {
    if (LRC_INTERVAL) { clearInterval(LRC_INTERVAL); LRC_INTERVAL = null; }
  }

  function renderLyrics(content, format) {
    if (!content) {
      lyricsContent.innerHTML = '<p class="player__lyrics-empty">No lyrics available for this track.</p>';
      LRC_DATA = [];
      stopLrcSync();
      return;
    }
    if (format === 'lrc') {
      LRC_DATA = parseLRC(content);
      if (LRC_DATA.length === 0) {
        // No timestamps — treat as plain text
        lyricsContent.innerHTML = '<div class="lyrics-scroll">' +
          content.split('\n').filter(function(l){ return l.trim(); }).map(function(l){
            return '<p class="lyrics-line">' + escapeHtml(l.trim()) + '</p>';
          }).join('') + '</div>';
        return;
      }
      lyricsContent.innerHTML = '<div class="lyrics-scroll">' +
        LRC_DATA.map(function(l, i){
          return '<p class="lyrics-line" data-idx="' + i + '">' + escapeHtml(l.text || '&nbsp;') + '</p>';
        }).join('') + '</div>';
      startLrcSync();
    } else {
      LRC_DATA = [];
      stopLrcSync();
      lyricsContent.innerHTML = '<div class="lyrics-scroll">' +
        content.split('\n').filter(function(l){ return l.trim(); }).map(function(l){
          return '<p class="lyrics-line">' + escapeHtml(l.trim()) + '</p>';
        }).join('') + '</div>';
    }
  }

  function escapeHtml(str) {
    var d = document.createElement('div');
    d.textContent = str;
    return d.innerHTML;
  }

  function fetchAndShowLyrics(songId) {
    if (!songId) { lyricsContent.innerHTML = '<p class="player__lyrics-empty">Select a song to see its lyrics.</p>'; return; }
    var x = new XMLHttpRequest();
    x.open('GET', BASE + 'player/lyrics/' + songId, true);
    x.onload = function() {
      if (x.status === 200) {
        var d = JSON.parse(x.responseText);
        renderLyrics(d.content, d.format || 'plain');
      } else {
        lyricsContent.innerHTML = '<p class="player__lyrics-empty">Could not load lyrics.</p>';
      }
    };
    x.send();
  }

  function openLyricsPanel() {
    if (!SONG_ID) return;
    // Sync cover, title, artist
    lyricsSongTitle.textContent = titleEl.textContent;
    lyricsArtist.textContent = artistEl.textContent;
    if (artImg.style.display !== 'none' && artImg.src) {
      lyricsArtImg.src = artImg.src;
      lyricsArtImg.style.display = 'block';
      lyricsArtPlaceholder.style.display = 'none';
    } else {
      lyricsArtImg.style.display = 'none';
      lyricsArtPlaceholder.style.display = 'flex';
    }
    lyricsPanel.style.display = 'flex';
    document.body.style.overflow = 'hidden';
    updateLyricsPlayBtn();
    fetchAndShowLyrics(SONG_ID);
    // Sync progress
    if (audio.duration) {
      lyricsTimeTotal.textContent = fmt(audio.duration);
    }
  }

  function closeLyricsPanel() {
    lyricsPanel.style.display = 'none';
    document.body.style.overflow = '';
    stopLrcSync();
  }

  lyricsBtn.addEventListener('click', function(e) {
    e.preventDefault();
    if (lyricsPanel.style.display === 'flex') { closeLyricsPanel(); return; }
    openLyricsPanel();
  });

  lyricsClose.addEventListener('click', closeLyricsPanel);
  lyricsCloseBg.addEventListener('click', closeLyricsPanel);

  /* Sync lyrics play/pause */
  lyricsPlay.addEventListener('click', function() {
    togglePlay();
    updateLyricsPlayBtn();
  });
  lyricsPrev.addEventListener('click', prevTrack);
  lyricsNext.addEventListener('click', nextTrack);

  /* Sync progress bar in lyrics panel */
  audio.addEventListener('timeupdate', function() {
    if (lyricsPanel.style.display !== 'flex') return;
    if (!audio.duration) return;
    var p = (audio.currentTime / audio.duration) * 100;
    lyricsBarFill.style.width = p + '%';
    lyricsTimeCur.textContent = fmt(audio.currentTime);
  });
  audio.addEventListener('loadedmetadata', function() {
    if (lyricsPanel.style.display === 'flex') {
      lyricsTimeTotal.textContent = fmt(audio.duration);
    }
  });

  /* Click-to-seek on lyrics progress bar */
  lyricsBar.addEventListener('click', function(e) {
    var r = this.getBoundingClientRect();
    var pct = (e.clientX - r.left) / r.width;
    if (audio.duration) audio.currentTime = pct * audio.duration;
  });

  /* Override playSong to reset lyrics when a new song loads */
  var origPlaySongLyrics = playSong;
  playSong = function(song) {
    origPlaySongLyrics(song);
    if (lyricsPanel.style.display === 'flex') {
      setTimeout(function() {
        lyricsSongTitle.textContent = titleEl.textContent;
        lyricsArtist.textContent = artistEl.textContent;
        if (artImg.style.display !== 'none' && artImg.src) {
          lyricsArtImg.src = artImg.src;
          lyricsArtImg.style.display = 'block';
          lyricsArtPlaceholder.style.display = 'none';
        } else {
          lyricsArtImg.style.display = 'none';
          lyricsArtPlaceholder.style.display = 'flex';
        }
        updateLyricsPlayBtn();
        fetchAndShowLyrics(SONG_ID);
      }, 100);
    }
  };

  /* Escape key to close lyrics panel */
  document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape' && lyricsPanel.style.display === 'flex') {
      closeLyricsPanel();
    }
  });

  /* ═══ Right-click context menu on all [data-song-id] cards ═══ */
  function showSongContextMenu(e, songId) {
    e.preventDefault();
    var old = document.getElementById('song-context-menu');
    if (old) old.remove();

    var menu = document.createElement('div');
    menu.id = 'song-context-menu';
    menu.style.cssText = 'position:fixed;z-index:10000;background:var(--color-paper-2);border:1px solid var(--color-rule);border-radius:10px;padding:6px;box-shadow:0 12px 40px -8px oklch(0% 0 0 / 0.5);min-width:190px;backdrop-filter:blur(20px);-webkit-backdrop-filter:blur(20px);';

    var items = [
      { l: 'Add to Queue', h: '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><polyline points="17 1 21 5 17 9"/><path d="M3 11V9a4 4 0 0 1 4-4h14"/><polyline points="7 23 3 19 7 15"/><path d="M21 13v2a4 4 0 0 1-4 4H3"/></svg>', a: 'queue' },
      { l: 'Add to Playlist', h: '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><line x1="8" y1="6" x2="21" y2="6"/><line x1="8" y1="12" x2="21" y2="12"/><line x1="8" y1="18" x2="21" y2="18"/><line x1="3" y1="6" x2="3.01" y2="6"/><line x1="3" y1="12" x2="3.01" y2="12"/><line x1="3" y1="18" x2="3.01" y2="18"/></svg>', a: 'playlist' },
      { l: 'Add to Favorites', h: '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/></svg>', a: 'favorite' },
    ];

    items.forEach(function(item) {
      var b = document.createElement('button');
      b.style.cssText = 'display:flex;align-items:center;gap:10px;width:100%;padding:8px 12px;border:none;border-radius:7px;background:transparent;color:var(--color-ink-2);font-size:13px;font-weight:500;cursor:pointer;text-align:left;transition:all .12s;';
      b.innerHTML = item.h + '<span>' + item.l + '</span>';
      b.onmouseenter = function() { this.style.background = 'var(--color-paper-3)'; this.style.color = 'var(--color-ink)'; };
      b.onmouseleave = function() { this.style.background = 'transparent'; this.style.color = 'var(--color-ink-2)'; };
      b.onclick = function() { menu.remove(); if(item.a==='queue'){var x=new XMLHttpRequest();x.open('GET',BASE+'player/info/'+songId,true);x.onload=function(){if(x.status===200){var d=JSON.parse(x.responseText);window.addToQueue({id:d.id,title:d.title,artist:d.artist,file_path:d.file_path,cover_path:d.cover_path});}};x.send();}else if(item.a==='playlist'){window.addToPlaylist(songId);}else if(item.a==='favorite'){window.addToFavorites(songId);} };
      menu.appendChild(b);
    });

    var x = e.clientX, y = e.clientY;
    var mw = 200, mh = items.length * 40 + 14;
    if (x + mw > window.innerWidth) x = window.innerWidth - mw - 10;
    if (y + mh > window.innerHeight) y = window.innerHeight - mh - 10;
    menu.style.left = x + 'px';
    menu.style.top = y + 'px';
    document.body.appendChild(menu);

    function cmClose(ev) { if (!menu.contains(ev.target)) { menu.remove(); document.removeEventListener('click', cmClose); } }
    setTimeout(function(){ document.addEventListener('click', cmClose); }, 10);
  }

  document.addEventListener('contextmenu', function(e) {
    var card = e.target.closest('[data-song-id]');
    if (!card || card.closest('.player__menu')) return;
    var id = parseInt(card.getAttribute('data-song-id'), 10);
    if (id) showSongContextMenu(e, id);
  });

  /* ═══ Update queue panel with thumbnails ═══ */
  function updateQueueUI(){
    if(!menuQueueList)return;
    var items=QUEUE.slice(QUEUE_IDX+1);
    if(items.length===0){
      menuQueueList.innerHTML='<li class="player__menu-queue-empty">No songs in queue</li>';
      return;
    }
    menuQueueList.innerHTML='';
    items.forEach(function(s,i){
      var li=document.createElement('li');
      li.className='player__menu-queue-item' + (i === 0 ? ' player__menu-queue-item--next' : '');

      // Thumbnail
      var thumb = document.createElement('div');
      thumb.className = 'player__menu-queue-item-thumb';
      if (s.cover_path) {
        thumb.innerHTML = '<img src="' + (s.cover_path.indexOf('http')===0 ? s.cover_path : BASE + s.cover_path) + '" alt="" loading="lazy">';
      } else {
        thumb.textContent = (s.title || '?')[0].toUpperCase();
      }

      // Link
      var link = document.createElement('div');
      link.className = 'player__menu-queue-link';
      link.innerHTML = '<span class="player__menu-queue-title">' + (s.title || 'Unknown') + '</span>' +
                       '<span class="player__menu-queue-artist">' + (s.artist || '—') + '</span>';

      // Play on click — jump to this song, keep rest of queue below
      li.style.cursor = 'pointer';
      li.addEventListener('click', function(ev) {
        if (ev.target.closest('.player__menu-queue-remove')) return;
        var idx = parseInt(this.getAttribute('data-idx'), 10);
        // Move this song to be next in queue
        var actualIdx = QUEUE_IDX + 1 + idx;
        var song = QUEUE[actualIdx];
        if (!song) return;
        // Remove it from its position
        QUEUE.splice(actualIdx, 1);
        // Insert it right after current
        QUEUE.splice(QUEUE_IDX + 1, 0, song);
        // Play it
        QUEUE_IDX++;
        loadIdx();
        updateQueueUI();
      });

      // Remove button
      var rmBtn = document.createElement('button');
      rmBtn.className = 'player__menu-queue-remove';
      rmBtn.setAttribute('data-idx', i);
      rmBtn.innerHTML = '<svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>';
      rmBtn.addEventListener('click', function(ev) {
        ev.stopPropagation();
        var idx = parseInt(this.getAttribute('data-idx'), 10);
        QUEUE.splice(QUEUE_IDX + 1 + idx, 1);
        updateQueueUI();
      });

      li.appendChild(thumb);
      li.appendChild(link);
      li.appendChild(rmBtn);
      // Store actual idx for click handler
      li.setAttribute('data-idx', i);
      menuQueueList.appendChild(li);
    });
  }
  /* Override addToQueue to also update UI */
  var origAddToQueue=window.addToQueue;
  window.addToQueue=function(song){
    origAddToQueue(song);
    updateQueueUI();
  };
  /* Override playSong to reset queue UI */
  var origPlaySong=playSong;
  playSong=function(song){
    // Clear saved state — fresh play should not mix with restored state
    sessionStorage.removeItem(STORAGE_KEY);
    origPlaySong(song);
    setTimeout(updateQueueUI,100);
  };
  window.playSong=playSong;

  /* ════════════════════════════════════════════
     ── sessionStorage Persistence (survive full reloads) ──
     ════════════════════════════════════════════ */

  /**
   * Save current player state before page unload.
   */
  function savePlayerState() {
    if (!SONG_ID) return;
    try {
      var state = {
        songId: SONG_ID,
        currentTime: audio.currentTime || 0,
        volume: audio.volume,
        loop: LOOP,
        queue: QUEUE,
        queueIdx: QUEUE_IDX,
        playedIds: PLAYED_IDS,
        title: titleEl.textContent,
        artist: artistEl.textContent,
        coverPath: artImg.style.display !== 'none' && artImg.src ? artImg.src.replace(BASE, '') : null,
        filePath: null,
      };
      sessionStorage.setItem(STORAGE_KEY, JSON.stringify(state));
    } catch (e) { /* ignore quota errors */ }
  }

  window.addEventListener('beforeunload', savePlayerState);

  /**
   * Restore player state after a full page reload.
   */
  function restorePlayerState() {
    var raw;
    try { raw = sessionStorage.getItem(STORAGE_KEY); } catch (e) { return; }
    if (!raw) return;

    var state;
    try { state = JSON.parse(raw); } catch (e) { return; }
    if (!state || !state.songId) return;

    // Clear storage so subsequent PJAX navigations don't re-restore
    sessionStorage.removeItem(STORAGE_KEY);

    // Fetch fresh song info to verify the song still exists
    var x = new XMLHttpRequest();
    x.open('GET', BASE + 'player/info/' + state.songId, true);
    x.onload = function () {
      if (x.status !== 200) {
        // Song was deleted — bail out
        return;
      }
      var d;
      try { d = JSON.parse(x.responseText); } catch (e) { return; }
      if (!d || !d.id) return;

      // Restore internal state
      SONG_ID = d.id;
      LOOP = state.loop || 0;
      QUEUE = state.queue || [];
      QUEUE_IDX = state.queueIdx >= 0 ? state.queueIdx : 0;
      PLAYED_IDS = state.playedIds || [];

      // Restore UI
      markPlayed(d.id);
      setSongInfo(d);
      player.classList.add('show');
      player.style.transform = 'translateY(0)';
      updatePlayBtn();
      updateLoopBtn();
      updateQueueUI();

      // Restore volume before loading audio
      if (typeof state.volume === 'number' && state.volume >= 0 && state.volume <= 1) {
        audio.volume = state.volume;
        volRange.value = state.volume * 100;
        updateVolIcon();
      }

      // Load audio and seek to saved position
      var savedTime = state.currentTime || 0;
      var seekHandler = function () {
        audio.removeEventListener('loadedmetadata', seekHandler);
        if (savedTime > 0 && audio.duration && savedTime < audio.duration) {
          audio.currentTime = savedTime;
        }
      };
      audio.addEventListener('loadedmetadata', seekHandler);
      audio.src = BASE + 'player/stream/' + d.id;
      audio.load();

      // Indicate to user that playback is paused and ready to resume
      timeCurrent.textContent = fmt(savedTime);
      if (audio.duration) {
        timeTotal.textContent = fmt(audio.duration);
        barFill.style.width = ((savedTime / audio.duration) * 100) + '%';
      }
    };
    x.onerror = function () { /* network error — silently give up */ };
    x.send();
  }

  // Restore on load (runs after full page reloads)
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', restorePlayerState);
  } else {
    restorePlayerState();
  }

})();
