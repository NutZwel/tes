/**
 * Laufey — Admin Upload Indicators
 * Shows real-time feedback when audio/cover files are selected.
 * Vanilla JS — no framework dependencies.
 */
(function () {
  'use strict';

  /**
   * Format bytes to human-readable string.
   * @param {number} bytes
   * @return {string}
   */
  function formatSize(bytes) {
    if (bytes === 0) return '0 B';
    var units = ['B', 'KB', 'MB', 'GB'];
    var i = Math.floor(Math.log(bytes) / Math.log(1024));
    if (i >= units.length) i = units.length - 1;
    return (bytes / Math.pow(1024, i)).toFixed(i > 0 ? 1 : 0) + ' ' + units[i];
  }

  /**
   * Allowed audio MIME types (CI3 upload lib maps these internally).
   */
  var ALLOWED_AUDIO = {
    'audio/mpeg': 'MP3',
    'audio/wav': 'WAV',
    'audio/wave': 'WAV',
    'audio/x-wav': 'WAV',
    'audio/ogg': 'OGG',
    'audio/flac': 'FLAC',
    'audio/x-flac': 'FLAC',
    'audio/aac': 'AAC',
    'audio/x-aac': 'AAC',
    'audio/mp4': 'AAC',
  };
  var ALLOWED_AUDIO_NAMES = ['mp3', 'wav', 'ogg', 'flac', 'aac'];

  var ALLOWED_IMAGE = {
    'image/png': 'PNG',
    'image/jpeg': 'JPG',
    'image/webp': 'WebP',
  };
  var ALLOWED_IMAGE_NAMES = ['png', 'jpg', 'jpeg', 'webp'];

  /**
   * Get extension from filename.
   * @param {string} name
   * @return {string}
   */
  function getExt(name) {
    var parts = name.split('.');
    return parts.length > 1 ? (parts.pop() || '').toLowerCase() : '';
  }

  /**
   * Check if audio MIME type is valid.
   * @param {string} mime
   * @return {boolean}
   */
  function isValidAudioMime(mime) {
    return !!ALLOWED_AUDIO[mime];
  }

  /**
   * Check if image MIME type is valid.
   * @param {string} mime
   * @return {boolean}
   */
  function isValidImageMime(mime) {
    return !!ALLOWED_IMAGE[mime];
  }

  /**
   * Check if file extension is allowed for audio.
   * @param {string} ext
   * @return {boolean}
   */
  function isAllowedAudioExt(ext) {
    return ALLOWED_AUDIO_NAMES.indexOf(ext) > -1;
  }

  /**
   * Check if file extension is allowed for image.
   * @param {string} ext
   * @return {boolean}
   */
  function isAllowedImageExt(ext) {
    return ALLOWED_IMAGE_NAMES.indexOf(ext) > -1;
  }

  /**
   * Initialize upload indicator for a given file input.
   * @param {HTMLInputElement} input
   * @param {boolean} isCover
   */
  function initIndicator(input, isCover) {
    var wrapper = input.closest('.upload-box');
    if (!wrapper) return;

    // If already initialized, skip
    if (wrapper.dataset.uploadInit) return;
    wrapper.dataset.uploadInit = '1';

    // Create status element
    var statusEl = document.createElement('div');
    statusEl.className = 'upload-status mt-2';
    statusEl.style.cssText =
      'display:none;font-size:var(--text-xs);padding:var(--space-xs) var(--space-sm);border-radius:var(--radius-sm);align-items:center;gap:var(--space-xs);';
    wrapper.after(statusEl);

    // Preview container (for cover images)
    var previewEl = null;
    if (isCover) {
      previewEl = document.createElement('div');
      previewEl.className = 'upload-preview mt-2';
      previewEl.style.cssText =
        'display:none;width:80px;height:80px;border-radius:var(--radius-md);overflow:hidden;border:1px solid var(--color-rule);background:var(--color-paper-3);';
      previewEl.innerHTML = '<img alt="" style="width:100%;height:100%;object-fit:cover;display:block;">';
      wrapper.after(previewEl);
    }

    // Extra info appended to the upload-box__text
    var textSpan = wrapper.querySelector('.upload-box__text');
    var hintSpan = wrapper.querySelector('.upload-box__hint');

    /**
     * Update indicator state.
     * @param {File|null} file
     */
    function update(file) {
      if (!file) {
        // No file selected — reset
        statusEl.style.display = 'none';
        wrapper.style.borderColor = '';
        wrapper.style.background = '';
        if (textSpan) textSpan.textContent = isCover
          ? 'Click to upload cover image'
          : 'Click to upload audio file';
        if (hintSpan) hintSpan.style.display = '';
        if (previewEl) previewEl.style.display = 'none';
        return;
      }

      var ext = getExt(file.name);
      var validExt = isCover ? isAllowedImageExt(ext) : isAllowedAudioExt(ext);
      var mimeLabel = isCover
        ? (ALLOWED_IMAGE[file.type] || ext.toUpperCase())
        : (ALLOWED_AUDIO[file.type] || ext.toUpperCase());
      var isValid = isCover ? isValidImageMime(file.type) : isValidAudioMime(file.type);

      // Show filename in the upload box text
      if (textSpan) textSpan.textContent = file.name + ' (' + formatSize(file.size) + ')';
      if (hintSpan) hintSpan.style.display = 'none';

      if (isValid && validExt) {
        // Valid file
        statusEl.style.display = 'flex';
        statusEl.style.background = 'color-mix(in oklch, oklch(65% 0.20 145) 10%, transparent)';
        statusEl.style.border = '1px solid oklch(65% 0.20 145 / 0.3)';
        statusEl.style.color = 'oklch(72% 0.18 145)';
        statusEl.innerHTML =
          '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>' +
          '<span>' + mimeLabel + ' file selected</span>';
        wrapper.style.borderColor = 'oklch(65% 0.20 145 / 0.5)';
        wrapper.style.background = 'color-mix(in oklch, oklch(65% 0.20 145) 4%, var(--color-paper))';

        // Cover preview
        if (previewEl && isCover) {
          var reader = new FileReader();
          reader.onload = function (e) {
            previewEl.style.display = 'block';
            previewEl.querySelector('img').src = e.target.result;
          };
          reader.readAsDataURL(file);
        }
      } else {
        // Invalid file type
        statusEl.style.display = 'flex';
        statusEl.style.background = 'color-mix(in oklch, oklch(65% 0.20 25) 10%, transparent)';
        statusEl.style.border = '1px solid oklch(65% 0.20 25 / 0.3)';
        statusEl.style.color = 'oklch(72% 0.18 30)';
        var allowed = isCover ? 'PNG, JPG, WebP' : 'MP3, WAV, OGG, FLAC, AAC';
        statusEl.innerHTML =
          '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>' +
          '<span>Unsupported format. Accepted: ' + allowed + '</span>';
        wrapper.style.borderColor = 'oklch(65% 0.20 25 / 0.5)';
        wrapper.style.background = 'color-mix(in oklch, oklch(65% 0.20 25) 4%, var(--color-paper))';
        if (previewEl) previewEl.style.display = 'none';
      }
    }

    input.addEventListener('change', function () {
      update(input.files && input.files.length > 0 ? input.files[0] : null);
    });

    // Handle form reset (PJAX reload or manual)
    var form = input.closest('form');
    if (form) {
      form.addEventListener('reset', function () {
        update(null);
      });
    }
  }

  // Initialize on page load
  function initAll() {
    var audioInput = document.getElementById('audio_file');
    var coverInput = document.getElementById('cover_file');
    if (audioInput) initIndicator(audioInput, false);
    if (coverInput) initIndicator(coverInput, true);
  }

  // Run on DOM ready
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initAll);
  } else {
    initAll();
  }

  // Re-init on PJAX complete (admin pages loaded via PJAX)
  document.addEventListener('pjax:complete', initAll);

})();
