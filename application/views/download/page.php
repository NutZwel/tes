<style>
  .dl-card { transition: transform 0.2s var(--ease-out), box-shadow 0.2s var(--ease-out); }
  .dl-card:hover { transform: translateY(-3px); box-shadow: 0 12px 32px -8px oklch(0% 0 0 / 0.4); }
  .dl-btn { transition: all 0.2s var(--ease-out); }
  .dl-btn:hover { background: var(--color-accent) !important; color: var(--color-paper) !important; border-color: var(--color-accent) !important; }
</style>

<section class="py-5">
  <div class="container">
    <header class="mb-5 text-center">
      <h1 class="fw-light mb-2" style="font-family:var(--font-display);font-size:var(--text-2xl);color:var(--color-ink);">Download</h1>
      <p class="mb-4" style="color:var(--color-muted);font-size:var(--text-sm);">Get your favorite tracks — free and unlimited for registered users.</p>

      <form action="<?= base_url('download/page') ?>" method="get" style="max-width:400px;margin:0 auto;">
        <div style="display:flex;gap:6px;border-radius:999px;border:1px solid var(--color-rule);background:var(--color-paper-2);padding:4px;">
          <input type="search" name="q" placeholder="Search songs..." value="<?= html_escape($search_query ?? '') ?>" style="flex:1;border:none;background:transparent;padding:8px 14px;color:var(--color-ink);font-size:var(--text-sm);outline:none;">
          <button type="submit" style="padding:8px 18px;border-radius:999px;border:none;background:var(--color-accent);color:var(--color-paper);font-size:var(--text-sm);font-weight:500;cursor:pointer;">Search</button>
        </div>
      </form>
    </header>

    <?php if (empty($songs)): ?>
      <div class="text-center py-5">
        <p style="color:var(--color-muted);"><?= !empty($search_query) ? 'No songs found for "' . html_escape($search_query) . '"' : 'No songs available.' ?></p>
        <a href="<?= base_url('download/page') ?>" class="btn btn-outline-light rounded-pill">Browse All</a>
      </div>
    <?php else: ?>
      <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(280px,1fr));gap:16px;">
        <?php foreach ($songs as $song): $cover = $song->cover_path && cover_available($song->cover_path) ? cover_url($song->cover_path) : null; ?>
        <div class="dl-card" style="background:var(--color-paper-2);border:1px solid var(--color-rule);border-radius:10px;overflow:hidden;">
          <div style="display:flex;gap:14px;padding:14px;">
            <div style="width:56px;height:56px;border-radius:8px;overflow:hidden;flex-shrink:0;background:var(--color-paper-3);">
              <?php if ($cover): ?>
                <img src="<?= $cover ?>" alt="" style="width:100%;height:100%;object-fit:cover;">
              <?php else: ?>
                <div style="display:flex;align-items:center;justify-content:center;height:100%;font-size:18px;font-weight:600;color:var(--color-muted);"><?= mb_strtoupper(mb_substr($song->title, 0, 1)) ?></div>
              <?php endif; ?>
            </div>
            <div style="flex:1;min-width:0;display:flex;flex-direction:column;justify-content:center;">
              <div style="font-size:var(--text-sm);font-weight:600;color:var(--color-ink);white-space:nowrap;overflow:hidden;text-overflow:ellipsis;"><?= html_escape($song->title) ?></div>
              <div style="font-size:var(--text-xs);color:var(--color-muted);white-space:nowrap;overflow:hidden;text-overflow:ellipsis;margin-top:2px;"><?= html_escape($song->artist) ?></div>
              <?php if ($song->genre_name): ?>
                <span style="font-size:10px;color:var(--color-accent);margin-top:4px;"><?= html_escape($song->genre_name) ?></span>
              <?php endif; ?>
            </div>
            <div style="display:flex;align-items:center;">
              <a href="<?= base_url('download/' . $song->id) ?>" class="dl-btn" style="display:flex;align-items:center;gap:6px;padding:8px 16px;border-radius:999px;border:1px solid var(--color-rule);background:transparent;color:var(--color-ink-2);font-size:12px;font-weight:500;text-decoration:none;transition:all 0.2s;">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                Download
              </a>
            </div>
          </div>
        </div>
        <?php endforeach; ?>
      </div>

      <!-- Pagination -->
      <?php if ($total_pages > 1): ?>
      <nav style="display:flex;align-items:center;justify-content:center;gap:8px;margin-top:32px;">
        <?php if ($current_page > 1): ?>
          <a href="?page=<?= $current_page-1 ?><?= !empty($search_query) ? '&q='.urlencode($search_query) : '' ?>" style="padding:8px 16px;border-radius:999px;border:1px solid var(--color-rule);color:var(--color-ink-2);text-decoration:none;font-size:13px;">← Prev</a>
        <?php endif; ?>
        <span style="font-size:13px;color:var(--color-muted);">Page <?= $current_page ?> of <?= $total_pages ?></span>
        <?php if ($current_page < $total_pages): ?>
          <a href="?page=<?= $current_page+1 ?><?= !empty($search_query) ? '&q='.urlencode($search_query) : '' ?>" style="padding:8px 16px;border-radius:999px;border:1px solid var(--color-rule);color:var(--color-ink-2);text-decoration:none;font-size:13px;">Next →</a>
        <?php endif; ?>
      </nav>
      <?php endif; ?>
    <?php endif; ?>
  </div>
</section>
