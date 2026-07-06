<!-- ────────────────────────────────────────────────
     VIEW: templates/modal.php
     Dua elemen native <dialog> untuk seluruh aplikasi:
       1. confirmDlg — Modal konfirmasi umum (misal
          "Hapus playlist/lagu/akun").
       2. pickerDlg — Pemilih playlist: menampilkan
          playlist yang ada dan field "Buat + tambah".
     Juga berisi JS inline yang menggerakkan keduanya:
       window.showConfirmModal(href, action)
       window.showPlaylistPicker(songId)
     ──────────────────────────────────────────────── -->

<style>
  dialog.lf-dialog {
    position: fixed;
    inset: 0;
    margin: auto;
    background: var(--color-paper-2);
    color: var(--color-ink);
    border: 1px solid var(--color-rule);
    border-radius: 12px;
    box-shadow: 0 16px 48px rgba(0,0,0,.5);
    max-height: 90vh;
  }
  dialog.lf-dialog::backdrop {
    background: rgba(0,0,0,0.6);
  }
  .pl-picker-item {
    display: flex; align-items: center; gap: 10px; width: 100%;
    padding: 10px 16px; border: none; border-bottom: 1px solid var(--color-rule);
    background: transparent; color: var(--color-ink); text-align: left; cursor: pointer;
    font-size: 14px; font-family: inherit; transition: background .15s;
  }
  .pl-picker-item:hover { background: var(--color-paper-3); }
  .pl-picker-item strong { font-weight: 600; }
  .pl-picker-item small { font-size: 12px; }
</style>

<!-- ═══ Native <dialog> Konfirmasi ═══ -->
<dialog id="confirmDlg" class="lf-dialog" style="padding:32px 28px 24px;max-width:360px;width:90vw;text-align:center;">
  <h5 id="dlg-title" style="margin:0 0 8px;font-size:18px;color:var(--color-ink);">Are you sure?</h5>
  <p id="dlg-desc" style="margin:0 0 20px;font-size:14px;color:var(--color-muted);">This action cannot be undone.</p>
  <div style="display:flex;gap:8px;">
    <button id="dlg-cancel" style="flex:1;padding:10px 16px;border-radius:999px;border:1px solid var(--color-rule);background:transparent;color:var(--color-ink-2);font-size:14px;cursor:pointer;">Cancel</button>
    <button id="dlg-ok" style="flex:1;padding:10px 16px;border-radius:999px;border:none;background:#dc3545;color:#fff;font-size:14px;cursor:pointer;">Delete</button>
  </div>
</dialog>

<!-- ═══ Native <dialog> Pemilih Playlist ═══ -->
<dialog id="pickerDlg" class="lf-dialog" style="padding:0;max-width:420px;width:90vw;overflow:hidden;">
  <div style="display:flex;align-items:center;justify-content:space-between;padding:16px 20px;border-bottom:1px solid var(--color-rule);">
    <h5 style="margin:0;font-size:16px;color:var(--color-ink);">Add to Playlist</h5>
    <button id="picker-close" style="background:none;border:none;color:var(--color-muted);font-size:22px;cursor:pointer;padding:0 4px;line-height:1;">&times;</button>
  </div>
  <div id="pickerList" style="overflow-y:auto;min-height:100px;"></div>
  <div style="padding:12px 16px;border-top:1px solid var(--color-rule);">
    <div style="display:flex;gap:6px;">
      <input type="text" id="pickerName" style="flex:1;padding:6px 12px;border-radius:6px;border:1px solid var(--color-rule);background:var(--color-paper);color:var(--color-ink);font-size:13px;outline:none;" placeholder="New playlist name..." maxlength="100">
      <button id="pickerCreate" style="padding:6px 14px;border-radius:6px;border:none;background:var(--color-accent);color:var(--color-paper);font-size:13px;cursor:pointer;white-space:nowrap;">Create</button>
    </div>
  </div>
</dialog>

<script>
(function() {
  'use strict';

  /* ═══ Dialog Konfirmasi ═══ */
  var dlg = document.getElementById('confirmDlg');
  if (dlg) {
    var titleEl = document.getElementById('dlg-title');
    var descEl = document.getElementById('dlg-desc');
    var okBtn = document.getElementById('dlg-ok');
    var cancelBtn = document.getElementById('dlg-cancel');
    var cb = null;

    // Diekspos secara global agar tombol apa pun bisa memicu konfirmasi sebelum navigasi
    window.showConfirmModal = function(href, action) {
      if (!href) return;
      // Mapping action key ke judul/deskripsi yang human-readable
      var titles = {'delete-playlist':'Delete Playlist','delete-song':'Delete Song','delete-account':'Delete Account'};
      var descs  = {'delete-playlist':'This will permanently delete this playlist.','delete-song':'Permanently delete this song from the catalog.','delete-account':'Your account will be permanently removed.'};
      titleEl.textContent = titles[action] || 'Are you sure?';
      descEl.textContent = descs[action] || 'This action cannot be undone.';
      cb = function() { window.location.href = href; };
      dlg.showModal();
    };

    okBtn.addEventListener('click', function() {
      dlg.close();
      var fn = cb; cb = null;
      if (fn) setTimeout(fn, 150); // Delay singkat agar dialog sempat tertutup dulu
    });

    cancelBtn.addEventListener('click', function() { dlg.close(); cb = null; });
    dlg.addEventListener('close', function() { cb = null; });
    dlg.addEventListener('click', function(e) { if (e.target === dlg) dlg.close(); });
  }

  /* ═══ Dialog Pemilih Playlist ═══ */
  var pdlg = document.getElementById('pickerDlg');
  if (pdlg) {
    var listEl = document.getElementById('pickerList');
    var nameEl = document.getElementById('pickerName');
    var createBtn = document.getElementById('pickerCreate');
    var closeBtn = document.getElementById('picker-close');
    var pendingSongId = 0;

    // Diekspos secara global agar tombol "Add to Playlist" bisa membuka picker
    window.showPlaylistPicker = function(songId) {
      if (!songId || songId <= 0) return; // guard: harus valid song ID
      pendingSongId = songId;
      listEl.innerHTML = '<div style="padding:40px 20px;text-align:center;color:#999;font-size:14px;">Loading...</div>';
      pdlg.showModal();

      // Fetch playlist user via AJAX dan render daftar picker
      var x = new XMLHttpRequest();
      x.open('GET', BASE + 'playlist/get_playlists_json', true);
      x.onload = function() {
        var p;
        try { p = JSON.parse(x.responseText); } catch(e) { p = []; }
        renderList(p);
      };
      x.onerror = function() { listEl.innerHTML = '<div style="padding:40px 20px;text-align:center;color:#999;">Failed to load</div>'; };
      x.send();
    };

    function renderList(list) {
      if (!list || list.length === 0) {
        listEl.innerHTML = '<div style="padding:40px 20px;text-align:center;color:#999;">No playlists yet. Create one below!</div>';
        return;
      }
      var h = '';
      list.forEach(function(pl) {
        h += '<button class="pl-picker-item" data-pid="' + pl.id + '">'
          + '<div><strong>' + esc(pl.name) + '</strong><br><small style="color:#999;font-size:12px;">' + pl.song_count + ' track' + (pl.song_count !== 1 ? 's' : '') + '</small></div>'
          + '</button>';
      });
      listEl.innerHTML = h;
    }

    // Klik playlist yang ada mengirim POST untuk menambahkan lagu yang tertunda
    listEl.addEventListener('click', function(e) {
      var b = e.target.closest('.pl-picker-item');
      if (!b) return;
      var pid = b.getAttribute('data-pid');
      if (!pid) return;
      pdlg.close();
      var x = new XMLHttpRequest();
      x.open('POST', BASE + 'playlist/add_song/' + pid, true);
      x.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
      x.onload = function() { toast(x.status === 200 ? 'Added to playlist!' : 'Failed'); };
      x.send('song_id=' + pendingSongId);
    });

    // Tombol "Create": buat playlist baru via AJAX dan tambah lagu dalam satu panggilan
    createBtn.addEventListener('click', function() {
      var name = nameEl.value.trim();
      if (!name) { nameEl.focus(); return; }
      pdlg.close();
      var x = new XMLHttpRequest();
      x.open('POST', BASE + 'playlist/add_song_ajax', true);
      x.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
      x.onload = function() { toast(x.status === 200 ? 'Created & added!' : 'Failed'); };
      x.send('song_id=' + pendingSongId + '&name=' + encodeURIComponent(name));
      nameEl.value = '';
    });

    nameEl.addEventListener('keydown', function(e) { if (e.key === 'Enter') createBtn.click(); });
    closeBtn.addEventListener('click', function() { pdlg.close(); });
    pdlg.addEventListener('click', function(e) { if (e.target === pdlg) pdlg.close(); });
  }

  // Notifikasi toast sederhana, auto-hapus setelah 2.5 detik
  function toast(msg) {
    var el = document.createElement('div');
    el.style.cssText = 'position:fixed;bottom:80px;left:50%;transform:translateX(-50%);padding:10px 20px;background:#1a1a2e;color:#e0e0e0;border:1px solid #333;border-radius:8px;z-index:99999;font-size:14px;box-shadow:0 4px 16px rgba(0,0,0,.5);';
    el.textContent = msg;
    document.body.appendChild(el);
    setTimeout(function(){el.remove();},2500);
  }

  // Helper HTML-escape untuk konten yang dirender via JS
  function esc(s) { if (!s) return ''; return s.replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;'); }
})();
</script>
