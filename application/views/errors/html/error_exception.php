<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Exception Error</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f4f4f4; padding: 20px; }
        .error-box {
            background: white;
            border: 1px solid #ddd;
            padding: 25px;
            border-radius: 8px;
            max-width: 800px;
            margin: 40px auto;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        h1 { color: #d9534f; }
        h2 { color: #333; margin-top: 20px; }
        pre { background: #f8f8f8; padding: 15px; border-radius: 5px; overflow-x: auto; font-size: 13px; }
        .meta { color: #666; font-size: 13px; }
    </style>
</head>
<body>
    <div class="error-box">
        <h1>An uncaught Exception was encountered</h1>

        <p class="meta">Type: <?= get_class($exception) ?></p>
        <p><strong>Message:</strong> <?= $exception->getMessage() ?></p>
        <p class="meta">Filename: <?= $exception->getFile() ?></p>
        <p class="meta">Line Number: <?= $exception->getLine() ?></p>

        <?php if (defined('SHOW_DEBUG_BACKTRACE') && SHOW_DEBUG_BACKTRACE === TRUE): ?>
        <h2>Backtrace</h2>
        <?php foreach ($exception->getTrace() as $error): ?>
            <?php if (isset($error['file']) && strpos($error['file'], realpath(BASEPATH)) !== 0): ?>
            <p class="meta">
                File: <?= $error['file'] ?><br>
                Line: <?= $error['line'] ?><br>
                Function: <?= $error['function'] ?>
            </p>
            <?php endif; ?>
        <?php endforeach; ?>
        <?php endif; ?>
    </div>
</body>
</html>
