<!-- ═══════════════════════════════
     ── Profile Header (Spotify-style) ──
     ═══════════════════════════════ -->
<section style="background:linear-gradient(180deg, var(--color-paper-3) 0%, var(--color-paper) 100%);padding-block:var(--space-2xl) var(--space-lg);">
  <div class="container">
    <div class="row align-items-end g-4">
      <div class="col-auto">
        <?php if (!empty($user->avatar_path) && file_exists(FCPATH . $user->avatar_path)): ?>
          <img src="<?= base_url($user->avatar_path) ?>" alt="" style="width:180px;height:180px;border-radius:50%;object-fit:cover;box-shadow:0 8px 30px rgba(0,0,0,.4);" width="180" height="180">
        <?php else: ?>
          <div style="width:180px;height:180px;border-radius:50%;background:linear-gradient(135deg,var(--color-paper-3),var(--color-accent));display:flex;align-items:center;justify-content:center;box-shadow:0 8px 30px rgba(0,0,0,.4);">
            <span style="font-family:var(--font-display);font-size:4rem;font-weight:300;color:var(--color-ink);"><?= mb_strtoupper(mb_substr($user->display_name ?: $user->username, 0, 1)) ?></span>
          </div>
        <?php endif; ?>
      </div>
      <div class="col">
        <p style="font-size:var(--text-xs);font-weight:600;letter-spacing:.08em;text-transform:uppercase;color:var(--color-muted);margin:0 0 6px;">Profile</p>
        <h1 style="font-family:var(--font-display);font-size:clamp(2rem,5vw,3.5rem);font-weight:700;color:var(--color-ink);margin:0 0 8px;line-height:1.1;"><?= html_escape($user->display_name ?: $user->username) ?></h1>
        <p style="color:var(--color-muted);font-size:var(--text-sm);margin:0;">
          <?= (int) $favorites_count ?> favorites
          <span style="color:var(--color-neutral);margin:0 8px;">·</span>
          <?= (int) $playlists_count ?> playlists
          <span style="color:var(--color-neutral);margin:0 8px;">·</span>
          <?= (int) $total_listens ?> listens
          <span style="color:var(--color-neutral);margin:0 8px;">·</span>
          Joined <?= date('M Y', strtotime($user->created_at)) ?>
        </p>
      </div>
    </div>
  </div>
</section>

<!-- ═══════════════════════════════
     ── Profile Tabs + Content ──
     ═══════════════════════════════ -->
<section style="padding-block:var(--space-lg) var(--space-3xl);">
  <div class="container">
    <div class="row g-5">

      <!-- Sidebar Tabs -->
      <div class="col-12 col-md-3">
        <div style="display:flex;flex-direction:column;gap:2px;position:sticky;top:80px;">
          <button class="profile-tabs__btn profile-tabs__btn--active" data-tab="edit-profile" type="button" onclick="switchTab('edit-profile')" style="display:flex;align-items:center;gap:12px;padding:10px 14px;border-radius:6px;border:none;background:transparent;color:var(--color-muted);font-size:var(--text-sm);font-weight:500;cursor:pointer;text-align:left;transition:all .15s;">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
            Edit Profile
          </button>
          <button class="profile-tabs__btn" data-tab="security" type="button" onclick="switchTab('security')" style="display:flex;align-items:center;gap:12px;padding:10px 14px;border-radius:6px;border:none;background:transparent;color:var(--color-muted);font-size:var(--text-sm);font-weight:500;cursor:pointer;text-align:left;transition:all .15s;">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
            Security
          </button>
          <button class="profile-tabs__btn" data-tab="preferences" type="button" onclick="switchTab('preferences')" style="display:flex;align-items:center;gap:12px;padding:10px 14px;border-radius:6px;border:none;background:transparent;color:var(--color-muted);font-size:var(--text-sm);font-weight:500;cursor:pointer;text-align:left;transition:all .15s;">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06A1.65 1.65 0 0 0 4.68 15a1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06A1.65 1.65 0 0 0 9 4.68a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06A1.65 1.65 0 0 0 19.4 9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"/></svg>
            Preferences
          </button>
          <button class="profile-tabs__btn" data-tab="account" type="button" onclick="switchTab('account')" style="display:flex;align-items:center;gap:12px;padding:10px 14px;border-radius:6px;border:none;background:transparent;color:var(--color-muted);font-size:var(--text-sm);font-weight:500;cursor:pointer;text-align:left;transition:all .15s;">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>
            Account
          </button>
        </div>
      </div>

      <!-- Tab Panels -->
      <div class="col-12 col-md-9">

        <!-- ══ Edit Profile ══ -->
        <div class="profile-panel profile-panel--active" id="edit-profile">
          <h2 style="font-family:var(--font-display);font-size:var(--text-xl);font-weight:600;color:var(--color-ink);margin:0 0 4px;">Edit Profile</h2>
          <p style="color:var(--color-muted);font-size:var(--text-sm);margin:0 0 24px;">Update your public information.</p>

          <?php if (validation_errors()): ?>
          <div style="padding:12px 16px;background:color-mix(in oklch, oklch(65% 0.20 25) 10%, transparent);border:1px solid oklch(65% 0.20 25 / .3);border-radius:8px;color:oklch(72% 0.18 30);font-size:var(--text-sm);margin-bottom:16px;"><?= validation_errors('', ' ') ?></div>
          <?php endif; ?>
          <?php if ($this->session->flashdata('profile_success')): ?>
          <div style="padding:12px 16px;background:color-mix(in oklch, oklch(65% 0.20 145) 10%, transparent);border:1px solid oklch(65% 0.20 145 / .3);border-radius:8px;color:oklch(72% 0.18 145);font-size:var(--text-sm);margin-bottom:16px;">Profile updated successfully.</div>
          <?php endif; ?>

          <?= form_open_multipart('user/update_profile') ?>
            <div style="background:var(--color-paper-2);border:1px solid var(--color-rule);border-radius:8px;padding:24px;margin-bottom:16px;">
              <div class="row g-3">
                <div class="col-12">
                  <div style="display:flex;align-items:center;gap:16px;">
                    <?php if (!empty($user->avatar_path) && file_exists(FCPATH . $user->avatar_path)): ?>
                      <img src="<?= base_url($user->avatar_path) ?>" alt="" width="64" height="64" style="border-radius:8px;object-fit:cover;">
                    <?php else: ?>
                      <div style="width:64px;height:64px;border-radius:8px;background:var(--color-paper-3);display:flex;align-items:center;justify-content:center;font-family:var(--font-display);font-size:1.5rem;color:var(--color-accent);"><?= mb_strtoupper(mb_substr($user->display_name ?: $user->username, 0, 1)) ?></div>
                    <?php endif; ?>
                    <div style="display:flex;align-items:center;gap:8px;flex-wrap:wrap;">
                      <label for="avatar-input" style="display:inline-flex;align-items:center;gap:6px;padding:8px 20px;border-radius:999px;border:1px solid var(--color-rule);background:transparent;color:var(--color-ink-2);font-size:var(--text-sm);font-weight:500;cursor:pointer;transition:all .15s;">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z"/><circle cx="12" cy="13" r="4"/></svg>
                        Change Photo
                      </label>
                      <input type="file" name="avatar" id="avatar-input" accept="image/png,image/jpeg,image/webp,image/gif" hidden>
                      <?php if (!empty($user->avatar_path)): ?>
                      <button type="button" id="btn-remove-avatar" style="display:inline-flex;align-items:center;gap:6px;padding:8px 20px;border-radius:999px;border:1px solid oklch(65% 0.20 25 / .4);background:transparent;color:oklch(65% 0.20 25);font-size:var(--text-sm);font-weight:500;cursor:pointer;transition:all .15s;" onclick="document.getElementById('remove-avatar-input').value='1';this.textContent='Removed';this.style.opacity='0.5';this.style.pointerEvents='none';">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                        Remove
                      </button>
                      <input type="hidden" name="remove_avatar" id="remove-avatar-input" value="0">
                      <?php endif; ?>
                    </div>
                  </div>
                </div>
                <div class="col-12 col-md-6">
                  <label style="font-size:var(--text-sm);font-weight:500;color:var(--color-ink-2);display:block;margin-bottom:4px;">Username</label>
                  <input type="text" name="username" value="<?= html_escape($user->username) ?>" required style="width:100%;padding:10px 14px;border-radius:8px;border:1px solid var(--color-rule);background:var(--color-paper);color:var(--color-ink);font-size:var(--text-sm);outline:none;transition:border-color .15s;">
                </div>
                <div class="col-12 col-md-6">
                  <label style="font-size:var(--text-sm);font-weight:500;color:var(--color-ink-2);display:block;margin-bottom:4px;">Display Name</label>
                  <input type="text" name="display_name" value="<?= html_escape($user->display_name) ?>" style="width:100%;padding:10px 14px;border-radius:8px;border:1px solid var(--color-rule);background:var(--color-paper);color:var(--color-ink);font-size:var(--text-sm);outline:none;">
                </div>
                <div class="col-12">
                  <label style="font-size:var(--text-sm);font-weight:500;color:var(--color-ink-2);display:block;margin-bottom:4px;">Email</label>
                  <input type="email" name="email" value="<?= html_escape($user->email) ?>" required style="width:100%;padding:10px 14px;border-radius:8px;border:1px solid var(--color-rule);background:var(--color-paper);color:var(--color-ink);font-size:var(--text-sm);outline:none;">
                </div>
                <div class="col-12">
                  <label style="font-size:var(--text-sm);font-weight:500;color:var(--color-ink-2);display:block;margin-bottom:4px;">Bio</label>
                  <textarea name="bio" rows="3" maxlength="280" placeholder="Tell us about yourself..." style="width:100%;padding:10px 14px;border-radius:8px;border:1px solid var(--color-rule);background:var(--color-paper);color:var(--color-ink);font-size:var(--text-sm);outline:none;resize:vertical;"><?= html_escape($user->bio ?? '') ?></textarea>
                </div>
              </div>
            </div>
            <div style="display:flex;gap:8px;justify-content:flex-end;">
              <button type="reset" style="padding:10px 24px;border-radius:999px;border:1px solid var(--color-rule);background:transparent;color:var(--color-ink-2);font-size:var(--text-sm);font-weight:500;cursor:pointer;transition:all .15s;">Cancel</button>
              <button type="submit" style="padding:10px 24px;border-radius:999px;border:none;background:var(--color-accent);color:var(--color-paper);font-size:var(--text-sm);font-weight:500;cursor:pointer;transition:all .15s;">Save</button>
            </div>
          <?= form_close() ?>
        </div>

        <!-- ══ Security ══ -->
        <div class="profile-panel" id="security">
          <h2 style="font-family:var(--font-display);font-size:var(--text-xl);font-weight:600;color:var(--color-ink);margin:0 0 4px;">Security</h2>
          <p style="color:var(--color-muted);font-size:var(--text-sm);margin:0 0 24px;">Change your password.</p>

          <?php if ($this->session->flashdata('password_success')): ?>
          <div style="padding:12px 16px;background:color-mix(in oklch, oklch(65% 0.20 145) 10%, transparent);border:1px solid oklch(65% 0.20 145 / .3);border-radius:8px;color:oklch(72% 0.18 145);font-size:var(--text-sm);margin-bottom:16px;">Password changed.</div>
          <?php endif; ?>
          <?php if ($this->session->flashdata('password_error')): ?>
          <div style="padding:12px 16px;background:color-mix(in oklch, oklch(65% 0.20 25) 10%, transparent);border:1px solid oklch(65% 0.20 25 / .3);border-radius:8px;color:oklch(72% 0.18 30);font-size:var(--text-sm);margin-bottom:16px;"><?= $this->session->flashdata('password_error') ?></div>
          <?php endif; ?>

          <?= form_open('user/change_password') ?>
            <div style="background:var(--color-paper-2);border:1px solid var(--color-rule);border-radius:8px;padding:24px;margin-bottom:16px;">
              <div class="row g-3">
                <div class="col-12">
                  <label style="font-size:var(--text-sm);font-weight:500;color:var(--color-ink-2);display:block;margin-bottom:4px;">Current Password</label>
                  <input type="password" name="current_password" required minlength="8" style="width:100%;padding:10px 14px;border-radius:8px;border:1px solid var(--color-rule);background:var(--color-paper);color:var(--color-ink);font-size:var(--text-sm);outline:none;">
                </div>
                <div class="col-12 col-md-6">
                  <label style="font-size:var(--text-sm);font-weight:500;color:var(--color-ink-2);display:block;margin-bottom:4px;">New Password</label>
                  <input type="password" name="new_password" required minlength="8" style="width:100%;padding:10px 14px;border-radius:8px;border:1px solid var(--color-rule);background:var(--color-paper);color:var(--color-ink);font-size:var(--text-sm);outline:none;">
                </div>
                <div class="col-12 col-md-6">
                  <label style="font-size:var(--text-sm);font-weight:500;color:var(--color-ink-2);display:block;margin-bottom:4px;">Confirm New Password</label>
                  <input type="password" name="confirm_password" required minlength="8" style="width:100%;padding:10px 14px;border-radius:8px;border:1px solid var(--color-rule);background:var(--color-paper);color:var(--color-ink);font-size:var(--text-sm);outline:none;">
                </div>
              </div>
            </div>
            <div style="display:flex;gap:8px;justify-content:flex-end;">
              <button type="submit" style="padding:10px 24px;border-radius:999px;border:none;background:var(--color-accent);color:var(--color-paper);font-size:var(--text-sm);font-weight:500;cursor:pointer;transition:all .15s;">Change Password</button>
            </div>
          <?= form_close() ?>
        </div>

        <!-- ══ Preferences ══ -->
        <div class="profile-panel" id="preferences">
          <h2 style="font-family:var(--font-display);font-size:var(--text-xl);font-weight:600;color:var(--color-ink);margin:0 0 4px;">Preferences</h2>
          <p style="color:var(--color-muted);font-size:var(--text-sm);margin:0 0 24px;">Customize your experience.</p>

          <?= form_open_multipart('user/update_preferences') ?>
            <!-- Toggles -->
            <div style="background:var(--color-paper-2);border:1px solid var(--color-rule);border-radius:8px;padding:4px 20px;margin-bottom:16px;">
              <div style="display:flex;align-items:center;justify-content:space-between;padding:16px 0;">
                <div>
                  <div style="font-size:var(--text-sm);font-weight:500;color:var(--color-ink-2);">Autoplay</div>
                  <div style="font-size:var(--text-xs);color:var(--color-muted);margin-top:2px;">Auto-play next track</div>
                </div>
                <label style="position:relative;display:inline-block;width:44px;height:24px;flex-shrink:0;cursor:pointer;">
                  <input type="checkbox" name="autoplay" value="1" <?= !empty($prefs->autoplay) ? 'checked' : '' ?> style="opacity:0;width:0;height:0;">
                  <span style="position:absolute;inset:0;background:<?= !empty($prefs->autoplay) ? 'var(--color-accent)' : 'var(--color-paper-3)' ?>;border-radius:999px;transition:background .15s;border:1px solid var(--color-rule);"></span>
                  <span style="position:absolute;top:2px;left:<?= !empty($prefs->autoplay) ? '22px' : '2px' ?>;width:18px;height:18px;background:var(--color-ink);border-radius:50%;transition:left .15s;"></span>
                </label>
              </div>
              <div style="display:flex;align-items:center;justify-content:space-between;padding:16px 0;border-top:1px solid var(--color-rule);">
                <div>
                  <div style="font-size:var(--text-sm);font-weight:500;color:var(--color-ink-2);">Listening Activity</div>
                  <div style="font-size:var(--text-xs);color:var(--color-muted);margin-top:2px;">Show what you're listening to</div>
                </div>
                <label style="position:relative;display:inline-block;width:44px;height:24px;flex-shrink:0;cursor:pointer;">
                  <input type="checkbox" name="show_activity" value="1" <?= !empty($prefs->show_activity) ? 'checked' : '' ?> style="opacity:0;width:0;height:0;">
                  <span style="position:absolute;inset:0;background:<?= !empty($prefs->show_activity) ? 'var(--color-accent)' : 'var(--color-paper-3)' ?>;border-radius:999px;transition:background .15s;border:1px solid var(--color-rule);"></span>
                  <span style="position:absolute;top:2px;left:<?= !empty($prefs->show_activity) ? '22px' : '2px' ?>;width:18px;height:18px;background:var(--color-ink);border-radius:50%;transition:left .15s;"></span>
                </label>
              </div>
              <div style="display:flex;align-items:center;justify-content:space-between;padding:16px 0;border-top:1px solid var(--color-rule);">
                <div>
                  <div style="font-size:var(--text-sm);font-weight:500;color:var(--color-ink-2);">Email Notifications</div>
                  <div style="font-size:var(--text-xs);color:var(--color-muted);margin-top:2px;">Updates about new releases</div>
                </div>
                <label style="position:relative;display:inline-block;width:44px;height:24px;flex-shrink:0;cursor:pointer;">
                  <input type="checkbox" name="email_notifs" value="1" <?= !empty($prefs->email_notifs) ? 'checked' : '' ?> style="opacity:0;width:0;height:0;">
                  <span style="position:absolute;inset:0;background:<?= !empty($prefs->email_notifs) ? 'var(--color-accent)' : 'var(--color-paper-3)' ?>;border-radius:999px;transition:background .15s;border:1px solid var(--color-rule);"></span>
                  <span style="position:absolute;top:2px;left:<?= !empty($prefs->email_notifs) ? '22px' : '2px' ?>;width:18px;height:18px;background:var(--color-ink);border-radius:50%;transition:left .15s;"></span>
                </label>
              </div>
            </div>

            <!-- Theme Presets (Spicetify-style) -->
            <div style="background:var(--color-paper-2);border:1px solid var(--color-rule);border-radius:8px;padding:24px;margin-bottom:16px;">
              <div style="font-size:var(--text-sm);font-weight:500;color:var(--color-ink-2);margin-bottom:4px;">Theme</div>
              <div style="font-size:var(--text-xs);color:var(--color-muted);margin-bottom:12px;">Choose a color scheme for the entire interface.</div>
              <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(130px,1fr));gap:8px;">
                <?php
                $allThemes = [
                  'cobalt' => ['label'=>'Cobalt','bg'=>'#1a1a2e','fg'=>'#e0e0e0','accent'=>'#6d8eff'],
                  'midnight' => ['label'=>'Midnight','bg'=>'#0f0f23','fg'=>'#eef0ff','accent'=>'#5b7dff'],
                  'solar' => ['label'=>'Solar','bg'=>'#fdf6e3','fg'=>'#586e75','accent'=>'#cb4b16'],
                  'nord' => ['label'=>'Nord','bg'=>'#2e3440','fg'=>'#d8dee9','accent'=>'#88c0d0'],
                  'catppuccin' => ['label'=>'Catppuccin','bg'=>'#1e1e2e','fg'=>'#cdd6f4','accent'=>'#cba6f7'],
                  'dracula' => ['label'=>'Dracula','bg'=>'#1e1e2e','fg'=>'#f8f8f2','accent'=>'#bd93f9'],
                  'monokai' => ['label'=>'Monokai','bg'=>'#272822','fg'=>'#f8f8f2','accent'=>'#a6e22e'],
                  'rosepine' => ['label'=>'Rosé Pine','bg'=>'#191724','fg'=>'#e0def4','accent'=>'#eb6f92'],
                  'starshower' => ['label'=>'🌠 Star Shower','bg'=>'#1a1a2e','fg'=>'#e0e0e0','accent'=>'#6d8eff'],
                  'aurora' => ['label'=>'🌌 Aurora','bg'=>'#0e0e1e','fg'=>'#d8dee9','accent'=>'#88c0d0'],
                  'matrix' => ['label'=>'💚 Matrix','bg'=>'#0a0a0a','fg'=>'#39ff14','accent'=>'#39ff14'],
                  'bubble' => ['label'=>'🫧 Bubbles','bg'=>'#1a1a2e','fg'=>'#e0e0e0','accent'=>'#6d8eff'],
                ];
                $currentTheme = $prefs->theme ?? 'cobalt';
                foreach ($allThemes as $key => $t):
                ?>
                <label onclick="var r=this.querySelector('input');r.checked=true;document.querySelectorAll('[name=theme]').forEach(function(x){x.closest('label').style.borderColor=x.checked?'var(--color-accent)':'var(--color-rule)'});saveTheme('theme',r.value);" style="display:flex;flex-direction:column;border-radius:8px;border:2px solid <?= $currentTheme === $key ? 'var(--color-accent)' : 'var(--color-rule)' ?>;overflow:hidden;cursor:pointer;transition:all .15s;">
                  <div style="height:48px;background:<?= $t['bg'] ?>;display:flex;align-items:center;justify-content:center;gap:4px;padding:4px;">
                    <span style="width:10px;height:10px;border-radius:50%;background:<?= $t['accent'] ?>;"></span>
                    <span style="width:10px;height:10px;border-radius:2px;background:<?= $t['fg'] ?>;opacity:0.4;"></span>
                    <span style="width:10px;height:10px;border-radius:2px;background:<?= $t['fg'] ?>;opacity:0.2;"></span>
                  </div>
                  <div style="padding:6px 10px;text-align:center;">
                    <input type="radio" name="theme" value="<?= $key ?>" <?= $currentTheme === $key ? 'checked' : '' ?> style="display:none;">
                    <span style="font-size:var(--text-xs);font-weight:500;color:var(--color-ink);"><?= $t['label'] ?></span>
                  </div>
                </label>
                <?php endforeach; ?>
              </div>
            </div>

            <!-- Accent Color (dengan visual feedback) -->
            <div style="background:var(--color-paper-2);border:1px solid var(--color-rule);border-radius:8px;padding:24px;margin-bottom:16px;">
              <div style="font-size:var(--text-sm);font-weight:500;color:var(--color-ink-2);margin-bottom:12px;">Accent Color</div>
              <div style="display:flex;gap:6px;flex-wrap:wrap;">
                <?php
                $colorMap = [
                  'blue' => '#4465e0', 'purple' => '#7c3aed', 'green' => '#22c55e',
                  'orange' => '#f97316', 'pink' => '#ec4899', 'teal' => '#14b8a6',
                  'rose' => '#e11d48', 'amber' => '#f59e0b',
                ];
                $currentColor = $prefs->theme_color ?? 'blue';
                foreach ($colorMap as $key => $hex):
                ?>
                <label onclick="var r=this.querySelector('input');r.checked=true;document.querySelectorAll('[name=theme_color]').forEach(function(x){x.closest('label').style.borderColor=x.checked?r.getAttribute('data-hex'):'transparent'});saveTheme('theme_color',r.value);" style="display:inline-flex;align-items:center;gap:6px;padding:8px 12px;border-radius:8px;border:2px solid <?= $currentColor === $key ? $hex : 'transparent' ?>;background:<?= $hex ?>22;cursor:pointer;transition:all .15s;">
                  <input type="radio" name="theme_color" value="<?= $key ?>" data-hex="<?= $hex ?>" <?= $currentColor === $key ? 'checked' : '' ?> style="display:none;">
                  <span style="width:14px;height:14px;border-radius:50%;background:<?= $hex ?>;display:inline-block;"></span>
                  <span style="font-size:var(--text-xs);color:var(--color-ink-2);text-transform:capitalize;"><?= $key ?></span>
                </label>
                <?php endforeach; ?>
              </div>
            </div>

            <!-- Background CSS from ColorHunt / Custom -->
            <div style="background:var(--color-paper-2);border:1px solid var(--color-rule);border-radius:8px;padding:24px;margin-bottom:16px;">
              <div style="font-size:var(--text-sm);font-weight:500;color:var(--color-ink-2);margin-bottom:4px;">Background Effect</div>
              <div style="font-size:var(--text-xs);color:var(--color-muted);margin-bottom:12px;">Paste CSS from <a href="https://colorhunt.co" target="_blank" style="color:var(--color-accent);">ColorHunt</a>, <a href="https://cssgradient.io" target="_blank" style="color:var(--color-accent);">CSS Gradient</a>, or any background animation code.</div>
              <input type="text" name="custom_bg_css" id="custom_bg_css" value="<?= html_escape($prefs->theme_bg_css ?? '') ?>" placeholder="background: linear-gradient(...) or CSS animation" style="width:100%;padding:10px 14px;border-radius:8px;border:1px solid var(--color-rule);background:var(--color-paper);color:var(--color-ink);font-size:var(--text-xs);box-sizing:border-box;">
              <div style="margin-top:8px;display:flex;gap:6px;flex-wrap:wrap;">
                <button type="button" onclick="var inp=document.getElementById('custom_bg_css');inp.value='background:linear-gradient(135deg,#0f0c29,#302b63,#24243e);';saveTheme('theme_bg_css',inp.value);" style="padding:6px 12px;border-radius:999px;border:1px solid var(--color-rule);background:var(--color-paper-3);color:var(--color-ink-2);font-size:10px;cursor:pointer;">🌌 Twilight</button>
                <button type="button" onclick="document.getElementById('custom_bg_css').value='background:linear-gradient(135deg,#f093fb,#f5576c);';saveTheme('theme_bg_css',document.getElementById('custom_bg_css').value);" style="padding:6px 12px;border-radius:999px;border:1px solid var(--color-rule);background:var(--color-paper-3);color:var(--color-ink-2);font-size:10px;cursor:pointer;">🌅 Sunset</button>
                <button type="button" onclick="document.getElementById('custom_bg_css').value='background:linear-gradient(135deg,#2193b0,#6dd5ed);';saveTheme('theme_bg_css',document.getElementById('custom_bg_css').value);" style="padding:6px 12px;border-radius:999px;border:1px solid var(--color-rule);background:var(--color-paper-3);color:var(--color-ink-2);font-size:10px;cursor:pointer;">🌊 Ocean</button>
                <button type="button" onclick="document.getElementById('custom_bg_css').value='background:var(--color-paper);background-image:radial-gradient(circle,var(--color-accent) 1px,transparent 1px);background-size:30px 30px;';saveTheme('theme_bg_css',document.getElementById('custom_bg_css').value);" style="padding:6px 12px;border-radius:999px;border:1px solid var(--color-rule);background:var(--color-paper-3);color:var(--color-ink-2);font-size:10px;cursor:pointer;">🔵 Dots</button>
                <button type="button" onclick="document.getElementById('custom_bg_css').value='background:var(--color-paper);background-image:repeating-linear-gradient(45deg,transparent,transparent 15px,var(--color-rule) 15px,var(--color-rule) 16px);';saveTheme('theme_bg_css',document.getElementById('custom_bg_css').value);" style="padding:6px 12px;border-radius:999px;border:1px solid var(--color-rule);background:var(--color-paper-3);color:var(--color-ink-2);font-size:10px;cursor:pointer;">🔶 Stripes</button>
                <button type="button" onclick="document.getElementById('custom_bg_css').value='background:var(--color-paper);background-image:linear-gradient(var(--color-rule) 1px,transparent 1px),linear-gradient(90deg,var(--color-rule) 1px,transparent 1px);background-size:50px 50px;';saveTheme('theme_bg_css',document.getElementById('custom_bg_css').value);" style="padding:6px 12px;border-radius:999px;border:1px solid var(--color-rule);background:var(--color-paper-3);color:var(--color-ink-2);font-size:10px;cursor:pointer;">🔲 Grid</button>
              </div>
            </div>

            <!-- Language -->
            <div style="background:var(--color-paper-2);border:1px solid var(--color-rule);border-radius:8px;padding:24px;margin-bottom:16px;">
              <div style="font-size:var(--text-sm);font-weight:500;color:var(--color-ink-2);margin-bottom:4px;">Language</div>
              <select name="language" style="width:100%;padding:10px 14px;border-radius:8px;border:1px solid var(--color-rule);background:var(--color-paper);color:var(--color-ink);font-size:var(--text-sm);cursor:pointer;">
                <?php
                $langs = [
                  'en' => 'English', 'id' => 'Bahasa Indonesia', 'ja' => 'Japanese',
                  'ko' => 'Korean', 'fr' => 'French', 'es' => 'Spanish',
                  'de' => 'German', 'ar' => 'Arabic', 'zh' => 'Chinese',
                  'hi' => 'Hindi', 'pt' => 'Portuguese', 'ru' => 'Russian',
                ];
                $currentLang = $prefs->language ?? 'en';
                foreach ($langs as $code => $label):
                ?>
                <option value="<?= $code ?>" <?= $currentLang === $code ? 'selected' : '' ?>><?= $label ?></option>
                <?php endforeach; ?>
              </select>
            </div>

          <?= form_close() ?>
        </div>

        <!-- ══ Account ══ -->
        <div class="profile-panel" id="account">
          <h2 style="font-family:var(--font-display);font-size:var(--text-xl);font-weight:600;color:var(--color-ink);margin:0 0 4px;">Account</h2>
          <p style="color:var(--color-muted);font-size:var(--text-sm);margin:0 0 24px;">Manage your account.</p>

          <div style="display:flex;flex-direction:column;gap:8px;">
            <a href="<?= base_url('user/export_data') ?>" style="display:flex;align-items:center;justify-content:space-between;padding:16px 20px;background:var(--color-paper-2);border:1px solid var(--color-rule);border-radius:8px;text-decoration:none;color:var(--color-ink);transition:background .15s;">
              <div>
                <div style="font-size:var(--text-sm);font-weight:500;">Export Data</div>
                <div style="font-size:var(--text-xs);color:var(--color-muted);margin-top:2px;">Download your playlists, favorites & history</div>
              </div>
              <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="color:var(--color-muted);flex-shrink:0;"><polyline points="9 18 15 12 9 6"/></svg>
            </a>
            <a href="<?= base_url('user/logout_all') ?>" style="display:flex;align-items:center;justify-content:space-between;padding:16px 20px;background:var(--color-paper-2);border:1px solid var(--color-rule);border-radius:8px;text-decoration:none;color:var(--color-ink);transition:background .15s;">
              <div>
                <div style="font-size:var(--text-sm);font-weight:500;">Sign Out All Devices</div>
                <div style="font-size:var(--text-xs);color:var(--color-muted);margin-top:2px;">End sessions except this one</div>
              </div>
              <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="color:var(--color-muted);flex-shrink:0;"><polyline points="9 18 15 12 9 6"/></svg>
            </a>
            <a href="<?= base_url() ?>" style="display:flex;align-items:center;justify-content:space-between;padding:16px 20px;background:var(--color-paper-2);border:1px solid var(--color-rule);border-radius:8px;text-decoration:none;color:var(--color-ink);transition:background .15s;">
              <div>
                <div style="font-size:var(--text-sm);font-weight:500;">Dashboard</div>
                <div style="font-size:var(--text-xs);color:var(--color-muted);margin-top:2px;">Back to home page</div>
              </div>
              <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="color:var(--color-muted);flex-shrink:0;"><polyline points="9 18 15 12 9 6"/></svg>
            </a>
            <a href="<?= base_url('logout') ?>" style="display:flex;align-items:center;justify-content:space-between;padding:16px 20px;background:var(--color-paper-2);border:1px solid var(--color-rule);border-radius:8px;text-decoration:none;transition:background .15s;">
              <div>
                <div style="font-size:var(--text-sm);font-weight:500;color:oklch(65% 0.20 25);">Sign Out</div>
                <div style="font-size:var(--text-xs);color:var(--color-muted);margin-top:2px;">Sign out from this device</div>
              </div>
              <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="color:var(--color-muted);flex-shrink:0;"><polyline points="9 18 15 12 9 6"/></svg>
            </a>
            <div style="display:flex;align-items:center;justify-content:space-between;padding:16px 20px;background:color-mix(in oklch, oklch(65% 0.20 25) 5%, transparent);border:1px solid oklch(65% 0.20 25 / .3);border-radius:8px;">
              <div>
                <div style="font-size:var(--text-sm);font-weight:500;color:oklch(65% 0.20 25);">Delete Account</div>
                <div style="font-size:var(--text-xs);color:var(--color-muted);margin-top:2px;">Permanently remove your account</div>
              </div>
              <button type="button" data-confirm="delete-account" style="padding:8px 20px;border-radius:999px;border:none;background:oklch(55% 0.20 25);color:#fff;font-size:var(--text-sm);font-weight:500;cursor:pointer;flex-shrink:0;transition:all .15s;">Delete</button>
            </div>
          </div>
        </div>

      </div>
    </div>
  </div>
</section>

<!-- initProfileTabs di pjax.js -->
