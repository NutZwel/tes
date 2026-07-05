<?php
$isPjax = !empty($_SERVER['HTTP_X_PJAX']) || !empty($_GET['_pjax']);

// Get user theme preferences from session
$themeStyle = $this->session->userdata('theme_style') ?: 'cobalt';
$themeColor = $this->session->userdata('theme_color') ?: 'blue';
$themeBgCss = $this->session->userdata('theme_bg_css') ?: '';

// Theme presets — full color schemes
$themePresets = [
  'cobalt' => [
    'paper' => 'oklch(12% 0.006 250)', 'paper2' => 'oklch(16% 0.008 250)', 'paper3' => 'oklch(20% 0.008 250)',
    'ink' => 'oklch(93% 0.005 80)', 'ink2' => 'oklch(85% 0.006 80)', 'muted' => 'oklch(60% 0.008 250)',
    'neutral' => 'oklch(48% 0.015 250)', 'rule' => 'oklch(26% 0.015 250)',
    'label' => 'Cobalt', 'desc' => 'Original dark theme',
  ],
  'midnight' => [
    'paper' => 'oklch(10% 0.01 270)', 'paper2' => 'oklch(14% 0.012 270)', 'paper3' => 'oklch(18% 0.012 270)',
    'ink' => 'oklch(94% 0.004 80)', 'ink2' => 'oklch(86% 0.005 80)', 'muted' => 'oklch(58% 0.01 270)',
    'neutral' => 'oklch(45% 0.015 270)', 'rule' => 'oklch(24% 0.015 270)',
    'label' => 'Midnight', 'desc' => 'Deep blue night sky',
  ],
  'solar' => [
    'paper' => 'oklch(97% 0.003 80)', 'paper2' => 'oklch(93% 0.004 80)', 'paper3' => 'oklch(89% 0.004 80)',
    'ink' => 'oklch(14% 0.005 250)', 'ink2' => 'oklch(28% 0.006 250)', 'muted' => 'oklch(48% 0.008 80)',
    'neutral' => 'oklch(60% 0.01 80)', 'rule' => 'oklch(84% 0.01 80)',
    'label' => 'Solar', 'desc' => 'Warm light theme',
  ],
  'nord' => [
    'paper' => 'oklch(15% 0.025 230)', 'paper2' => 'oklch(19% 0.03 230)', 'paper3' => 'oklch(23% 0.025 230)',
    'ink' => 'oklch(90% 0.01 100)', 'ink2' => 'oklch(82% 0.01 100)', 'muted' => 'oklch(60% 0.03 230)',
    'neutral' => 'oklch(48% 0.025 230)', 'rule' => 'oklch(30% 0.02 230)',
    'label' => 'Nord', 'desc' => 'Arctic blue',
  ],
  'catppuccin' => [
    'paper' => 'oklch(15% 0.015 270)', 'paper2' => 'oklch(19% 0.02 270)', 'paper3' => 'oklch(23% 0.02 270)',
    'ink' => 'oklch(93% 0.008 110)', 'ink2' => 'oklch(85% 0.01 110)', 'muted' => 'oklch(60% 0.03 300)',
    'neutral' => 'oklch(48% 0.02 300)', 'rule' => 'oklch(28% 0.02 270)',
    'label' => 'Catppuccin', 'desc' => 'Warm pastel dark',
  ],
  'dracula' => [
    'paper' => 'oklch(14% 0.025 280)', 'paper2' => 'oklch(18% 0.03 280)', 'paper3' => 'oklch(22% 0.025 280)',
    'ink' => 'oklch(92% 0.01 110)', 'ink2' => 'oklch(84% 0.012 110)', 'muted' => 'oklch(55% 0.04 300)',
    'neutral' => 'oklch(45% 0.03 280)', 'rule' => 'oklch(28% 0.025 280)',
    'label' => 'Dracula', 'desc' => 'Purple night',
  ],
  'monokai' => [
    'paper' => 'oklch(13% 0.005 60)', 'paper2' => 'oklch(17% 0.008 60)', 'paper3' => 'oklch(21% 0.008 60)',
    'ink' => 'oklch(95% 0.005 90)', 'ink2' => 'oklch(87% 0.006 90)', 'muted' => 'oklch(62% 0.01 60)',
    'neutral' => 'oklch(50% 0.01 60)', 'rule' => 'oklch(27% 0.012 60)',
    'label' => 'Monokai', 'desc' => 'Warm neutral',
  ],
  'rosepine' => [
    'paper' => 'oklch(13% 0.02 345)', 'paper2' => 'oklch(17% 0.025 345)', 'paper3' => 'oklch(21% 0.02 345)',
    'ink' => 'oklch(92% 0.01 50)', 'ink2' => 'oklch(84% 0.012 50)', 'muted' => 'oklch(58% 0.04 345)',
    'neutral' => 'oklch(48% 0.025 345)', 'rule' => 'oklch(28% 0.02 345)',
    'label' => 'Rosé Pine', 'desc' => 'Muted rose',
  ],
  // Animated themes (use cobalt colors + CSS animation)
  'starshower' => [
    'paper' => 'oklch(12% 0.006 250)', 'paper2' => 'oklch(16% 0.008 250)', 'paper3' => 'oklch(20% 0.008 250)',
    'ink' => 'oklch(93% 0.005 80)', 'ink2' => 'oklch(85% 0.006 80)', 'muted' => 'oklch(60% 0.008 250)',
    'neutral' => 'oklch(48% 0.015 250)', 'rule' => 'oklch(26% 0.015 250)',
    'label' => '🌠 Star Shower', 'desc' => 'Falling stars animation',
  ],
  'aurora' => [
    'paper' => 'oklch(10% 0.01 250)', 'paper2' => 'oklch(14% 0.015 250)', 'paper3' => 'oklch(18% 0.015 250)',
    'ink' => 'oklch(93% 0.005 80)', 'ink2' => 'oklch(85% 0.006 80)', 'muted' => 'oklch(55% 0.02 250)',
    'neutral' => 'oklch(45% 0.015 250)', 'rule' => 'oklch(24% 0.015 250)',
    'label' => '🌌 Aurora', 'desc' => 'Northern lights glow',
  ],
  'matrix' => [
    'paper' => 'oklch(8% 0.01 140)', 'paper2' => 'oklch(11% 0.015 140)', 'paper3' => 'oklch(14% 0.015 140)',
    'ink' => 'oklch(80% 0.15 140)', 'ink2' => 'oklch(70% 0.12 140)', 'muted' => 'oklch(45% 0.10 140)',
    'neutral' => 'oklch(35% 0.08 140)', 'rule' => 'oklch(18% 0.02 140)',
    'label' => '💚 Matrix', 'desc' => 'Green rain drops',
  ],
  'bubble' => [
    'paper' => 'oklch(12% 0.01 270)', 'paper2' => 'oklch(16% 0.015 270)', 'paper3' => 'oklch(20% 0.015 270)',
    'ink' => 'oklch(93% 0.01 80)', 'ink2' => 'oklch(85% 0.01 80)', 'muted' => 'oklch(58% 0.02 270)',
    'neutral' => 'oklch(48% 0.015 270)', 'rule' => 'oklch(26% 0.015 270)',
    'label' => '🫧 Bubbles', 'desc' => 'Rising bubbles effect',
  ],
];

$preset = $themePresets[$themeStyle] ?? $themePresets['cobalt'];

// Accent color mapping
$accentColors = [
  'blue'   => 'oklch(62% 0.20 255)', 'purple' => 'oklch(60% 0.22 290)',
  'green'  => 'oklch(62% 0.20 145)', 'orange' => 'oklch(65% 0.20 70)',
  'pink'   => 'oklch(62% 0.20 350)', 'teal'   => 'oklch(62% 0.18 190)',
  'rose'   => 'oklch(60% 0.22 20)', 'amber'  => 'oklch(68% 0.22 85)',
];
$accentVal = $accentColors[$themeColor] ?? $accentColors['blue'];

$bgStyle = $themeBgCss ? $themeBgCss : '';
$animThemes = ['starshower', 'aurora', 'matrix', 'bubble'];
$animClass = in_array($themeStyle, $animThemes) ? 'theme-anim theme-' . $themeStyle : '';

if ($isPjax):
?><title><?= $title ?? 'Laufey — Music Player & Downloader' ?></title>
<div id="pjax-content">
  <?php $this->load->view($main_view); ?>
</div>
<?php else: ?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
  <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
  <meta http-equiv="Pragma" content="no-cache">
  <title><?= $title ?? 'Laufey — Music Player & Downloader' ?></title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="<?= base_url('public/css/tokens.css?v=' . filemtime(FCPATH . 'public/css/tokens.css')) ?>">
  <script>var BASE = '<?= base_url() ?>';</script>
  <style>
    :root {
      --color-paper: <?= $preset['paper'] ?>;
      --color-paper-2: <?= $preset['paper2'] ?>;
      --color-paper-3: <?= $preset['paper3'] ?>;
      --color-ink: <?= $preset['ink'] ?>;
      --color-ink-2: <?= $preset['ink2'] ?>;
      --color-muted: <?= $preset['muted'] ?>;
      --color-neutral: <?= $preset['neutral'] ?>;
      --color-rule: <?= $preset['rule'] ?>;
      --color-accent: <?= $accentVal ?>;
      --color-focus: <?= $accentVal ?>;
      --color-accent-hover: <?= $accentVal ?>;
    }
  </style>
</head>
<body class="<?= $animClass ?> d-flex flex-column min-vh-100" style="height:100vh;display:flex;flex-direction:column;<?= $bgStyle ?>">
<style>
<?php if ($animClass): ?>
body.theme-anim{background:var(--color-paper)}
body.theme-anim::before{content:'';position:fixed;inset:0;pointer-events:none;z-index:9998}
body.theme-anim .navbar{background:color-mix(in oklch,var(--color-paper) 60%,transparent)!important;backdrop-filter:blur(16px);border-bottom-color:transparent!important}
body.theme-anim .footer{--footer-bg:color-mix(in oklch,var(--color-paper) 75%,transparent);backdrop-filter:blur(16px);border-top-color:transparent!important}
<?php endif; ?>
<?php if ($themeStyle==='starshower'): ?>
@keyframes twinkle{0%,100%{opacity:.2}50%{opacity:1}}
body.theme-starshower::before{background:radial-gradient(2px 2px at 20% 30%,rgba(255,255,255,.9),transparent),radial-gradient(2px 2px at 40% 70%,rgba(255,255,255,.7),transparent),radial-gradient(1px 1px at 60% 20%,rgba(255,255,255,.6),transparent),radial-gradient(1px 1px at 80% 50%,rgba(255,255,255,.8),transparent),radial-gradient(1px 1px at 50% 10%,rgba(255,255,255,.5),transparent);animation:twinkle 3s ease-in-out infinite}
@keyframes starfall{0%{transform:translateY(-10vh) translateX(0);opacity:0}10%{opacity:1}90%{opacity:1}100%{transform:translateY(110vh) translateX(80px);opacity:0}}
body.theme-starshower::after{content:'';position:fixed;inset:0;pointer-events:none;z-index:9998;background:linear-gradient(90deg,transparent,transparent 40%,#fff 40%,#fff 41%,transparent 41%) 0 0/200px 120vh;mask:linear-gradient(transparent 85%,#fff);-webkit-mask:linear-gradient(transparent 85%,#fff);animation:starfall 3s linear infinite}
<?php endif; ?>
<?php if ($themeStyle==='aurora'): ?>
@keyframes aurora-wave{0%{transform:translateX(-50%) scaleY(1)}50%{transform:translateX(0%) scaleY(1.4)}100%{transform:translateX(-50%) scaleY(1)}}
body.theme-aurora::before{background:linear-gradient(90deg,transparent,oklch(62% 0.20 255/.15),oklch(60% 0.22 290/.15),transparent);filter:blur(50px);height:250px;top:0;animation:aurora-wave 6s ease-in-out infinite}
<?php endif; ?>
<?php if ($themeStyle==='matrix'): ?>
@keyframes matrix-fall{0%{transform:translateY(-100%)}100%{transform:translateY(100vh)}}
body.theme-matrix::before{background:repeating-linear-gradient(0deg,transparent,transparent 20px,oklch(80% 0.15 140/.08) 20px,oklch(80% 0.15 140/.08) 21px);animation:matrix-fall 8s linear infinite}
<?php endif; ?>
<?php if ($themeStyle==='bubble'): ?>
@keyframes bubble-rise{0%{transform:translateY(100vh) translateX(0) scale(.3);opacity:0}20%{opacity:.6}80%{opacity:.6}100%{transform:translateY(-10vh) translateX(40px) scale(1.1);opacity:0}}
body.theme-bubble::before{background:radial-gradient(circle at 15% 75%,oklch(62% 0.20 255/.12) 3px,transparent 3px),radial-gradient(circle at 35% 55%,oklch(62% 0.20 200/.1) 4px,transparent 4px),radial-gradient(circle at 55% 65%,oklch(62% 0.20 280/.12) 3px,transparent 3px),radial-gradient(circle at 75% 45%,oklch(62% 0.20 255/.08) 5px,transparent 5px),radial-gradient(circle at 90% 85%,oklch(62% 0.20 200/.06) 3px,transparent 3px);animation:bubble-rise 5s ease-in-out infinite}
<?php endif; ?>
</style>

  <?php
  $userId  = $this->session->userdata('user_id');
  $isLoggedIn = !empty($userId);
  $this->load->view('templates/nav', compact('isLoggedIn'));
  ?>

  <div id="pjax-content" style="flex:1 0 auto;">
    <?php $this->load->view($main_view); ?>
  </div>

  <?php $this->load->view('templates/footer', compact('isLoggedIn')); ?>
  <?php $this->load->view('templates/player'); ?>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <?php $this->load->view('templates/modal'); ?>
  <script src="<?= base_url('public/js/carousel.js?v=' . filemtime(FCPATH . 'public/js/carousel.js')) ?>"></script>
  <script src="<?= base_url('public/js/pjax.js') ?>"></script>
  <script src="<?= base_url('public/js/admin-upload.js?v=' . filemtime(FCPATH . 'public/js/admin-upload.js')) ?>"></script>
  <script src="<?= base_url('public/js/player.js?v=' . filemtime(FCPATH . 'public/js/player.js')) ?>"></script>
</body>
</html>
<?php endif; ?>
