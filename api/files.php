<?php
require_once __DIR__ . '/../includes/config.php';

header('Content-Type: application/json');

// Get API key from header
$headers = getallheaders();
$api_key = $headers['X-API-Key'] ?? null;

if (!$api_key) {
    http_response_code(401);
    echo json_encode(['error' => 'X-API-Key header required']);
    exit;
}

// Get user
$stmt = $pdo->prepare("SELECT id FROM users WHERE api_key = ?");
$stmt->execute([$api_key]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    http_response_code(401);
    echo json_encode(['error' => 'Invalid API key']);
    exit;
}

// Get files
$stmt = $pdo->prepare("SELECT * FROM files WHERE user_id = ? ORDER BY created_at DESC");
$stmt->execute([$user['id']]);
$files = $stmt->fetchAll(PDO::FETCH_ASSOC);

$result = [];
foreach ($files as $file) {
    $result[] = [
        'file_id' => $file['file_id'],
        'original_name' => $file['original_name'],
        'size' => $file['size'],
        'created_at' => $file['created_at'],
        'expires_at' => $file['expires_at'],
        'downloads' => $file['downloads'],
        'is_expired' => strtotime($file['expires_at']) < time(),
        'download_url' => "https://" . $_SERVER['HTTP_HOST'] . "/api/download.php?id=" . $file['file_id']
    ];
}

echo json_encode(['files' => $result]);
?>