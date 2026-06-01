<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Database Error</title>
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
        pre { background: #f8f8f8; padding: 15px; border-radius: 5px; overflow-x: auto; }
    </style>
</head>
<body>
    <div class="error-box">
        <h1>Database Error</h1>
        <p><?php echo $message ?? 'Terjadi kesalahan pada database.'; ?></p>
        
        <?php if (isset($heading)): ?>
            <p><strong><?php echo $heading; ?></strong></p>
        <?php endif; ?>
    </div>
</body>
</html>