<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>404 Page Not Found</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f4f4f4; padding: 20px; }
        .error-box {
            background: white;
            border: 1px solid #ddd;
            padding: 25px;
            border-radius: 8px;
            max-width: 600px;
            margin: 40px auto;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            text-align: center;
        }
        h1 { color: #d9534f; font-size: 48px; margin: 0 0 10px; }
        h2 { color: #333; margin: 0 0 15px; }
        p { color: #666; }
        a { color: #0d6efd; text-decoration: none; }
        a:hover { text-decoration: underline; }
    </style>
</head>
<body>
    <div class="error-box">
        <h1>404</h1>
        <h2>Page Not Found</h2>
        <p>The page you requested was not found.</p>
        <p><a href="<?= '/' ?>">Back to Home</a></p>
    </div>
</body>
</html>
