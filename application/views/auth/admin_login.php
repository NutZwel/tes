<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Admin Login — Laufey</title>
  <style>
    * { margin: 0; padding: 0; box-sizing: border-box; }
    body { font-family: system-ui, sans-serif; background: #0d0d0d; color: #eee; display: flex; align-items: center; justify-content: center; min-height: 100vh; }
    .card { background: #1a1a2e; border: 1px solid #2a2a4e; border-radius: 12px; padding: 40px; width: 360px; }
    h1 { font-size: 24px; margin-bottom: 8px; font-weight: 300; }
    p { color: #888; font-size: 14px; margin-bottom: 24px; }
    label { display: block; font-size: 13px; color: #aaa; margin-bottom: 4px; }
    input { width: 100%; padding: 10px 14px; background: #0d0d1a; border: 1px solid #333; border-radius: 8px; color: #eee; font-size: 14px; margin-bottom: 16px; }
    input:focus { outline: none; border-color: #4a6cf7; }
    button { width: 100%; padding: 10px; background: #4a6cf7; border: none; border-radius: 8px; color: #fff; font-size: 14px; font-weight: 600; cursor: pointer; }
    button:hover { background: #3b5de7; }
    .error { background: #3d1a1a; border: 1px solid #6b2a2a; color: #ff6b6b; padding: 10px; border-radius: 8px; font-size: 13px; margin-bottom: 16px; }
    .success { background: #1a3d1a; border: 1px solid #2a6b2a; color: #6bff6b; padding: 10px; border-radius: 8px; font-size: 13px; margin-bottom: 16px; }
    .info { background: #1a1a3d; border: 1px solid #2a2a6b; color: #6b6bff; padding: 10px; border-radius: 8px; font-size: 13px; margin-bottom: 16px; }
  </style>
</head>
<body>
  <div class="card">
    <h1>Admin Login</h1>
    <p>Sign in to manage Laufey</p>

    <?php if (!empty($msg)): ?>
      <div class="<?= $msg_type ?>"><?= html_escape($msg) ?></div>
    <?php endif; ?>

    <?php if (!empty($error)): ?>
      <div class="error"><?= html_escape($error) ?></div>
    <?php endif; ?>

    <form method="post">
      <label for="u">Username</label>
      <input type="text" id="u" name="identity" required placeholder="admin" autofocus>
      <label for="p">Password</label>
      <input type="password" id="p" name="password" required placeholder="admin123">
      <button type="submit">Sign In</button>
    </form>
  </div>
</body>
</html>
