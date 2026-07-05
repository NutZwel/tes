<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?>
<!-- ─── Hero ─── -->
<section class="overflow-hidden" id="hero">
  <div class="container">
    <div class="row align-items-center gx-5 gy-4">
      <div class="col-lg-7">
        <h1 class="display-3 fw-light lh-sm mb-3"
            style="font-family:var(--font-display);letter-spacing:-0.03em;overflow-wrap:anywhere">
          Music <em class="fw-semibold fst-normal text-primary">divine</em>,<br>
          made <span class="fw-bold fst-italic text-primary"
                     style="font-family:var(--font-outlier);font-size:1.1em">mortal</span>
        </h1>
        <p class="fs-5 mb-4" style="color:var(--color-muted);max-width:48ch;line-height:1.65">
          Stream lossless, download unlimited, and curate your world. The divine library of sound, now in your hands.
        </p>
        <div class="d-flex gap-2 flex-wrap">
          <a href="<?= base_url('register') ?>"
             class="btn btn-primary rounded-pill px-4">Start Listening</a>
          <a href="<?= base_url('catalog') ?>"
             class="btn btn-outline-light rounded-pill px-4">Browse Catalog</a>
        </div>
      </div>
      <div class="col-lg-5 text-center">
        <figure class="mb-0" aria-hidden="true">
          <img src="<?= base_url('public/images/waveform.svg') ?>"
               alt="Audio waveform visualization"
               class="img-fluid d-block mx-auto"
               style="max-width:520px;filter:drop-shadow(0 0 40px oklch(62% 0.20 255 / 0.12))"
               width="600" height="400"
               loading="eager">
        </figure>
      </div>
    </div>
  </div>
</section>

<!-- ─── Catalog Preview ─── -->
<section class="py-5" id="preview">
  <div class="container">
    <header class="d-flex align-items-baseline gap-3 mb-5">
      <h2 class="h2 fw-light mb-0" style="font-family:var(--font-display)">
        Browse the catalog
      </h2>
      <a href="<?= base_url('catalog') ?>"
         class="btn btn-outline-secondary btn-sm rounded-pill ms-auto d-inline-flex align-items-center gap-1 flex-shrink-0">
        View More
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none"
             stroke="currentColor" stroke-width="2"
             stroke-linecap="round" stroke-linejoin="round">
          <polyline points="9 18 15 12 9 6"/>
        </svg>
      </a>
    </header>

    <?php if (!empty($preview_songs)): ?>
    <div class="carousel position-relative" role="region" aria-label="Catalog preview">
      <!-- Prev -->
      <button class="carousel__arrow carousel__arrow--prev btn btn-outline-secondary rounded-circle border-0 position-absolute top-50 start-0 translate-middle-y z-3 d-flex align-items-center justify-content-center"
              aria-label="Previous tracks" type="button"
              style="width:48px;height:48px">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none"
             stroke="currentColor" stroke-width="2.5"
             stroke-linecap="round" stroke-linejoin="round">
          <polyline points="15 18 9 12 15 6"/>
        </svg>
      </button>
      <!-- Next -->
      <button class="carousel__arrow carousel__arrow--next btn btn-outline-secondary rounded-circle border-0 position-absolute top-50 end-0 translate-middle-y z-3 d-flex align-items-center justify-content-center"
              aria-label="Next tracks" type="button"
              style="width:48px;height:48px">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none"
             stroke="currentColor" stroke-width="2.5"
             stroke-linecap="round" stroke-linejoin="round">
          <polyline points="9 18 15 12 9 6"/>
        </svg>
      </button>

      <div class="carousel__wrap position-relative"
           style="padding:0 var(--space-xl,2.5rem);">
        <div class="carousel__track d-flex">
          <?php foreach ($preview_songs as $song):
            $cover = $song->cover_path && cover_available($song->cover_path)
              ? cover_url($song->cover_path)
              : null;
            $initial = mb_strtoupper(mb_substr($song->title, 0, 1));
          ?>
          <article class="carousel__card card flex-shrink-0 overflow-hidden border-secondary"
                   style="flex:0 0 calc((100% - 3 * var(--space-lg,1.5rem)) / 4);min-width:0;background:var(--color-paper-2)">
            <a href="#"
               class="carousel__link text-decoration-none text-light stretched-link"
               data-song-id="<?= $song->id ?>">
              <div class="carousel__art position-relative overflow-hidden"
                   style="aspect-ratio:1;background:var(--color-paper-3)<?php if ($cover): ?>;--glow-img:url('<?= $cover ?>')<?php endif; ?>">
                <?php if ($cover): ?>
                  <img src="<?= $cover ?>"
                       alt="<?= html_escape($song->title) ?>"
                       class="carousel__img w-100 h-100 object-fit-cover d-block position-relative"
                       style="z-index:2"
                       loading="lazy"
                       width="280" height="280">
                <?php else: ?>
                  <div class="w-100 h-100 d-flex align-items-center justify-content-center position-relative"
                       style="z-index:2;background:linear-gradient(135deg,var(--color-paper-2),var(--color-paper-3))">
                    <span class="carousel__initial display-3 fw-light text-secondary"><?= $initial ?></span>
                  </div>
                <?php endif; ?>
                <!-- Play button overlay -->
                <div class="carousel__play position-absolute z-3 d-flex align-items-center justify-content-center rounded-circle bg-primary"
                     style="bottom:var(--space-md,1rem);left:var(--space-md,1rem);width:40px;height:40px;opacity:0;transform:translateY(8px);box-shadow:0 4px 12px rgba(0,0,0,0.4);transition:opacity 0.2s,transform 0.2s,background 0.12s">
                  <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                    <polygon points="8,5 19,12 8,19"/>
                  </svg>
                </div>
                <?php if ($song->duration_seconds): ?>
                  <span class="position-absolute z-3 small fw-medium"
                        style="bottom:var(--space-xs,0.5rem);right:var(--space-xs,0.5rem);padding:2px 6px;background:rgba(0,0,0,0.65);border-radius:var(--radius-sm,4px);line-height:1.4;color:#fff"><?= gmdate('i:s', $song->duration_seconds) ?></span>
                <?php endif; ?>
              </div>
              <div class="carousel__body card-body px-2 py-2">
                <h3 class="carousel__title card-title h6 text-truncate mb-0"
                    style="font-family:var(--font-display);font-weight:600"><?= html_escape($song->title) ?></h3>
                <p class="carousel__artist card-text small text-secondary text-truncate mt-1 mb-0"><?= html_escape($song->artist) ?></p>
              </div>
            </a>
          </article>
          <?php endforeach; ?>
        </div>
      </div>
    </div>

    <?php else: ?>
    <div class="text-center py-5">
      <p class="fs-5 mb-4" style="color:var(--color-muted)">No tracks yet — check back soon.</p>
      <a href="<?= base_url() ?>" class="btn btn-primary rounded-pill">Browse Home</a>
    </div>
    <?php endif; ?>
  </div>
</section>

<!-- ─── Guest vs Registered Comparison ─── -->
<section class="py-5" id="comparison">
  <div class="container">
    <header class="mb-5">
      <h2 class="h2 fw-light mb-1" style="font-family:var(--font-display)">
        Why create a free account?
      </h2>
      <p class="small mb-0" style="color:var(--color-muted);max-width:48ch">
        Guest access lets you try before you commit. Registered users get the
        full experience — unlimited, zero ads, always.
      </p>
    </header>

    <div class="row g-4">
      <!-- Guest card -->
      <div class="col-md-6 d-flex">
        <article class="card flex-fill border-secondary overflow-hidden"
                 style="background:var(--color-paper-2)">
          <div class="card-body d-flex flex-column p-4">
            <div class="d-flex align-items-center justify-content-center rounded mb-3"
                 style="width:44px;height:44px;background:var(--color-paper-3);color:var(--color-muted)"
                 aria-hidden="true">
              <svg width="24" height="24" viewBox="0 0 24 24" fill="none"
                   stroke="currentColor" stroke-width="1.5"
                   stroke-linecap="round" stroke-linejoin="round">
                <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
                <circle cx="12" cy="7" r="4"/>
              </svg>
            </div>
            <h3 class="card-title h5 fw-semibold" style="font-family:var(--font-display)">
              Guest Access
            </h3>
            <p class="card-text small mb-3" style="color:var(--color-muted)">
              No account needed — try before you commit
            </p>
            <ul class="list-unstyled d-flex flex-column gap-2 mb-3 flex-fill">
              <li class="small d-flex align-items-start gap-1">
                <span class="flex-shrink-0" style="width:1.4em;text-align:center;color:var(--color-neutral)">○</span>
                Browse the full catalog
              </li>
              <li class="small d-flex align-items-start gap-1">
                <span class="flex-shrink-0" style="width:1.4em;text-align:center;color:var(--color-neutral)">○</span>
                Stream <strong>3 tracks per session</strong>
              </li>
              <li class="small d-flex align-items-start gap-1">
                <span class="flex-shrink-0" style="width:1.4em;text-align:center;color:var(--color-neutral)">○</span>
                <strong>1 download per day</strong> per IP
              </li>
              <li class="small d-flex align-items-start gap-1">
                <span class="flex-shrink-0" style="width:1.4em;text-align:center;color:var(--color-neutral)">○</span>
                Session-based progress only
              </li>
            </ul>
          </div>
        </article>
      </div>

      <!-- Registered card (premium) -->
      <div class="col-md-6 d-flex">
        <article class="card flex-fill border-primary position-relative overflow-hidden"
                 style="background:linear-gradient(160deg,var(--color-paper-2) 0%,color-mix(in oklch,oklch(62% 0.20 255) 6%,var(--color-paper-2)) 40%,color-mix(in oklch,oklch(62% 0.20 255) 3%,var(--color-paper-2)) 100%);box-shadow:0 0 40px oklch(62% 0.20 255/0.08),inset 0 1px 0 oklch(62% 0.20 255/0.15)">
          <span class="badge bg-primary text-dark position-absolute top-0 end-0 m-2 text-uppercase fw-semibold"
                style="font-size:0.625rem;letter-spacing:0.08em">Best Value</span>

          <div class="card-body d-flex flex-column p-4 position-relative" style="z-index:1">
            <div class="d-flex align-items-center justify-content-center rounded mb-3"
                 style="width:44px;height:44px;color:var(--color-accent);background:linear-gradient(135deg,color-mix(in oklch,oklch(62% 0.20 255) 18%,transparent),color-mix(in oklch,oklch(62% 0.20 255) 8%,transparent));box-shadow:0 0 20px oklch(62% 0.20 255/0.15)"
                 aria-hidden="true">
              <svg width="24" height="24" viewBox="0 0 24 24" fill="none"
                   stroke="currentColor" stroke-width="1.5"
                   stroke-linecap="round" stroke-linejoin="round">
                <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
              </svg>
            </div>
            <h3 class="card-title h5 fw-semibold" style="font-family:var(--font-display)">
              Registered User
            </h3>
            <p class="card-text small mb-3" style="color:var(--color-muted)">
              Free account — unlimited access, zero ads
            </p>
            <ul class="list-unstyled d-flex flex-column gap-2 mb-3 flex-fill">
              <li class="small d-flex align-items-start gap-1">
                <span class="flex-shrink-0 text-primary fw-bold" style="width:1.4em;text-align:center">✓</span>
                Browse &amp; search full catalog
              </li>
              <li class="small d-flex align-items-start gap-1">
                <span class="flex-shrink-0 text-primary fw-bold" style="width:1.4em;text-align:center">✓</span>
                <strong>Unlimited streaming</strong> of all tracks
              </li>
              <li class="small d-flex align-items-start gap-1">
                <span class="flex-shrink-0 text-primary fw-bold" style="width:1.4em;text-align:center">✓</span>
                <strong>Unlimited downloads</strong> to own
              </li>
              <li class="small d-flex align-items-start gap-1">
                <span class="flex-shrink-0 text-primary fw-bold" style="width:1.4em;text-align:center">✓</span>
                Create &amp; save playlists
              </li>
              <li class="small d-flex align-items-start gap-1">
                <span class="flex-shrink-0 text-primary fw-bold" style="width:1.4em;text-align:center">✓</span>
                Favorite tracks &amp; personal library
              </li>
            </ul>
            <div class="pt-2 mt-auto">
              <a href="<?= base_url('register') ?>"
                 class="btn btn-primary rounded-pill w-100">Create Free Account</a>
            </div>
          </div>
        </article>
      </div>
    </div>
  </div>
</section>
