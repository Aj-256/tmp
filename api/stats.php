<?php
require_once __DIR__ . '/../includes/config.php';

header('Content-Type: application/json');

function formatBytes($bytes) {
    if ($bytes == 0) return '0 Bytes';
    $k = 1024;
    $sizes = ['Bytes', 'KB', 'MB', 'GB'];
    $i = floor(log($bytes) / log($k));
    return round($bytes / pow($k, $i), 2) . ' ' . $sizes[$i];
}

// Get API key from header
$headers = getallheaders();
$api_key = $headers['X-API-Key'] ?? null;

if (!$api_key) {
    http_response_code(401);
    echo json_encode(['error' => 'X-API-Key header required']);
    exit;
}

// Get user
$stmt = $pdo->prepare("SELECT * FROM users WHERE api_key = ?");
$stmt->execute([$api_key]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    http_response_code(401);
    echo json_encode(['error' => 'Invalid API key']);
    exit;
}

// Get active files count
$stmt = $pdo->prepare("SELECT COUNT(*) as active FROM files WHERE user_id = ? AND expires_at > NOW()");
$stmt->execute([$user['id']]);
$active = $stmt->fetch(PDO::FETCH_ASSOC)['active'];

echo json_encode([
    'username' => $user['username'],
    'email' => $user['email'],
    'api_key' => $user['api_key'],
    'total_uploads' => (int)$user['total_uploads'],
    'storage_used' => (int)$user['storage_used'],
    'storage_formatted' => formatBytes($user['storage_used']),
    'active_files' => (int)$active
]);
?>