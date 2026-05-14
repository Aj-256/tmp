<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AJ TMP API - Temporary File Hosting</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { background: #000; color: #fff; font-family: monospace; line-height: 1.6; }
        .container { max-width: 1000px; margin: 0 auto; padding: 20px; }
        .navbar { display: flex; justify-content: space-between; align-items: center; padding: 20px 0; border-bottom: 1px solid #333; flex-wrap: wrap; gap: 20px; }
        .logo { font-size: 24px; font-weight: bold; text-decoration: none; color: #fff; }
        .nav-links { display: flex; gap: 24px; }
        .nav-links a { color: #fff; text-decoration: none; }
        .nav-links a:hover { opacity: 0.7; }
        .card { background: #0a0a0a; border: 1px solid #222; border-radius: 12px; padding: 32px; margin-bottom: 24px; }
        .hero { text-align: center; padding: 60px 0; }
        .hero h1 { font-size: 48px; margin-bottom: 20px; }
        .hero p { color: #888; margin-bottom: 32px; }
        .stats { display: grid; grid-template-columns: repeat(4, 1fr); gap: 20px; margin: 40px 0; }
        .stat-card { text-align: center; padding: 24px; background: #0a0a0a; border: 1px solid #222; border-radius: 12px; }
        .stat-number { font-size: 36px; font-weight: bold; }
        .stat-label { color: #888; font-size: 12px; margin-top: 8px; }
        .code-block { background: #050505; border: 1px solid #333; border-radius: 8px; padding: 16px; overflow-x: auto; margin: 16px 0; }
        .code-block pre { color: #0f0; margin: 0; font-family: monospace; font-size: 13px; }
        .btn { padding: 10px 24px; border: 1px solid #333; border-radius: 6px; cursor: pointer; text-decoration: none; display: inline-block; color: #fff; background: transparent; }
        .btn-primary { background: #fff; color: #000; border: none; }
        .btn-primary:hover { background: #ddd; }
        @media (max-width: 768px) {
            .navbar { flex-direction: column; text-align: center; }
            .stats { grid-template-columns: 1fr; }
            .hero h1 { font-size: 32px; }
        }
    </style>
</head>
<body>
    <div class="container">
        <nav class="navbar">
            <a href="/" class="logo">⚡ AJ TMP API</a>
            <div class="nav-links">
                <a href="/">Home</a>
                <?php if (isset($_SESSION['user_id'])): ?>
                    <a href="/dashboard.php">Dashboard</a>
                    <a href="/logout.php">Logout</a>
                <?php else: ?>
                    <a href="/login.php">Login</a>
                    <a href="/register.php">Register</a>
                <?php endif; ?>
            </div>
        </nav>
        
        <div class="hero">
            <h1>AJ TMP API</h1>
            <p>Temporary file hosting with 30-minute expiry.<br>Upload via API, get download links.</p>
            <?php if (!isset($_SESSION['user_id'])): ?>
                <a href="/register.php" class="btn btn-primary">Get API Key</a>
            <?php else: ?>
                <a href="/dashboard.php" class="btn btn-primary">Go to Dashboard</a>
            <?php endif; ?>
        </div>
        
        <div class="stats">
            <div class="stat-card"><div class="stat-number"><?php echo $total_users ?? 0; ?></div><div class="stat-label">Users</div></div>
            <div class="stat-card"><div class="stat-number"><?php echo $active_files ?? 0; ?></div><div class="stat-label">Active Files</div></div>
            <div class="stat-card"><div class="stat-number">30</div><div class="stat-label">Minutes Expiry</div></div>
            <div class="stat-card"><div class="stat-number">100MB</div><div class="stat-label">Max File Size</div></div>
        </div>
        
        <div class="card">
            <h3>📡 API Endpoints</h3>
            <div class="code-block">
                <pre># POST - Upload file
curl -X POST https://<?php echo $_SERVER['HTTP_HOST']; ?>/api/upload.php \
  -H "X-API-Key: YOUR_API_KEY" \
  -F "file=@document.pdf"

# GET - Download file
curl -L -O https://<?php echo $_SERVER['HTTP_HOST']; ?>/api/download.php?id=FILE_ID \
  -H "X-API-Key: YOUR_API_KEY"

# DELETE - Delete file
curl -X DELETE https://<?php echo $_SERVER['HTTP_HOST']; ?>/api/delete.php?id=FILE_ID \
  -H "X-API-Key: YOUR_API_KEY"

# GET - List files
curl https://<?php echo $_SERVER['HTTP_HOST']; ?>/api/files.php \
  -H "X-API-Key: YOUR_API_KEY"

# GET - Your stats
curl https://<?php echo $_SERVER['HTTP_HOST']; ?>/api/stats.php \
  -H "X-API-Key: YOUR_API_KEY"</pre>
            </div>
        </div>
    </div>
</body>
</html>