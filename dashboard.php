<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/auth.php';

requireLogin();

// Get user data
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

// Get files
$stmt = $pdo->prepare("SELECT * FROM files WHERE user_id = ? ORDER BY created_at DESC");
$stmt->execute([$_SESSION['user_id']]);
$files = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get active files count
$stmt = $pdo->prepare("SELECT COUNT(*) as active FROM files WHERE user_id = ? AND expires_at > NOW()");
$stmt->execute([$_SESSION['user_id']]);
$active_count = $stmt->fetch(PDO::FETCH_ASSOC)['active'];

// Format bytes function (only once)
function formatBytes($bytes) {
    if ($bytes == 0) return '0 Bytes';
    $k = 1024;
    $sizes = ['Bytes', 'KB', 'MB', 'GB'];
    $i = floor(log($bytes) / log($k));
    return round($bytes / pow($k, $i), 2) . ' ' . $sizes[$i];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - AJ TMP API</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { background: #000; color: #fff; font-family: monospace; }
        .container { max-width: 1200px; margin: 0 auto; padding: 20px; }
        .navbar { display: flex; justify-content: space-between; align-items: center; padding: 20px 0; border-bottom: 1px solid #333; flex-wrap: wrap; gap: 20px; margin-bottom: 32px; }
        .logo { font-size: 24px; font-weight: bold; text-decoration: none; color: #fff; }
        .nav-links { display: flex; gap: 24px; }
        .nav-links a { color: #fff; text-decoration: none; }
        .nav-links a:hover { opacity: 0.7; }
        .card { background: #0a0a0a; border: 1px solid #222; border-radius: 12px; padding: 24px; margin-bottom: 24px; }
        .stats-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px; margin-bottom: 32px; }
        .stat-card { text-align: center; padding: 24px; background: #0a0a0a; border: 1px solid #222; border-radius: 12px; }
        .stat-number { font-size: 48px; font-weight: bold; }
        .stat-label { color: #888; font-size: 12px; margin-top: 8px; }
        .api-key-box { background: #000; border: 1px solid #333; border-radius: 8px; padding: 12px 16px; font-family: monospace; word-break: break-all; margin: 12px 0; }
        .btn { padding: 8px 16px; border: 1px solid #333; border-radius: 6px; cursor: pointer; font-size: 14px; text-decoration: none; display: inline-block; background: transparent; color: #fff; }
        .btn-primary { background: #fff; color: #000; border: none; }
        .btn-primary:hover { background: #ddd; }
        .btn-danger { border-color: #f00; color: #f00; }
        .btn-danger:hover { background: rgba(255,0,0,0.1); }
        .file-item { background: #000; border: 1px solid #222; border-radius: 8px; padding: 16px; margin-bottom: 12px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px; }
        .file-info { flex: 1; }
        .file-name { font-weight: 600; margin-bottom: 8px; word-break: break-all; }
        .file-meta { font-size: 12px; color: #888; }
        .file-actions { display: flex; gap: 8px; }
        h3 { margin-bottom: 16px; }
        .success { background: #0a2a0a; border: 1px solid #0f0; color: #0f0; padding: 12px; border-radius: 8px; margin-bottom: 20px; }
        @media (max-width: 768px) {
            .navbar { flex-direction: column; text-align: center; }
            .stats-grid { grid-template-columns: 1fr; }
            .file-item { flex-direction: column; text-align: center; }
            .file-actions { justify-content: center; }
        }
    </style>
</head>
<body>
    <div class="container">
        <nav class="navbar">
            <a href="/" class="logo">⚡ AJ TMP API</a>
            <div class="nav-links">
                <a href="/">Home</a>
                <a href="/dashboard.php">Dashboard</a>
                <a href="/logout.php">Logout</a>
            </div>
        </nav>
        
        <?php if (isset($_GET['msg'])): ?>
            <div class="success"><?php echo htmlspecialchars($_GET['msg']); ?></div>
        <?php endif; ?>
        
        <div class="stats-grid">
            <div class="stat-card"><div class="stat-number"><?php echo $user['total_uploads']; ?></div><div class="stat-label">Total Uploads</div></div>
            <div class="stat-card"><div class="stat-number"><?php echo $active_count; ?></div><div class="stat-label">Active Files</div></div>
            <div class="stat-card"><div class="stat-number"><?php echo formatBytes($user['storage_used']); ?></div><div class="stat-label">Storage Used</div></div>
        </div>
        
        <div class="card">
            <h3>🔑 Your API Key</h3>
            <div class="api-key-box"><?php echo htmlspecialchars($user['api_key']); ?></div>
            <button onclick="copyApiKey()" class="btn btn-primary">Copy API Key</button>
        </div>
        
        <div class="card">
            <h3>📁 Your Files</h3>
            <?php if (count($files) > 0): ?>
                <?php foreach ($files as $file): ?>
                    <div class="file-item">
                        <div class="file-info">
                            <div class="file-name"><?php echo htmlspecialchars($file['original_name']); ?></div>
                            <div class="file-meta"><?php echo formatBytes($file['size']); ?> • Uploaded <?php echo date('Y-m-d H:i', strtotime($file['created_at'])); ?> • Downloads: <?php echo $file['downloads']; ?></div>
                            <?php if (strtotime($file['expires_at']) > time()): ?>
                                <div class="file-meta" style="color: #0f0;">Active • Expires: <?php echo date('Y-m-d H:i', strtotime($file['expires_at'])); ?></div>
                            <?php else: ?>
                                <div class="file-meta" style="color: #f00;">Expired</div>
                            <?php endif; ?>
                            <div class="file-meta">File ID: <code><?php echo htmlspecialchars($file['file_id']); ?></code></div>
                        </div>
                        <div class="file-actions">
                            <?php if (strtotime($file['expires_at']) > time()): ?>
                                <a href="/api/download.php?id=<?php echo $file['file_id']; ?>" class="btn">Download</a>
                            <?php endif; ?>
                            <button onclick="copyUrl('<?php echo "https://" . $_SERVER['HTTP_HOST'] . "/api/download.php?id=" . $file['file_id']; ?>')" class="btn">Copy URL</button>
                            <button onclick="deleteFile('<?php echo $file['file_id']; ?>')" class="btn btn-danger">Delete</button>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p style="color: #888; text-align: center;">No files. Use API to upload.</p>
            <?php endif; ?>
        </div>
    </div>
    
    <script>
        function copyApiKey() {
            navigator.clipboard.writeText('<?php echo $user['api_key']; ?>');
            alert('API Key copied!');
        }
        function copyUrl(url) {
            navigator.clipboard.writeText(url);
            alert('Download URL copied!');
        }
        async function deleteFile(fileId) {
            if (!confirm('Delete this file?')) return;
            const res = await fetch(`/api/delete.php?id=${fileId}`, { method: 'DELETE' });
            if (res.ok) location.reload();
            else alert('Delete failed');
        }
    </script>
</body>
</html>