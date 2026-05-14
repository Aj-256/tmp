<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/supabase.php';

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

// Check file
if (!isset($_FILES['file'])) {
    http_response_code(400);
    echo json_encode(['error' => 'No file provided']);
    exit;
}

$file = $_FILES['file'];

if ($file['error'] !== UPLOAD_ERR_OK) {
    http_response_code(400);
    echo json_encode(['error' => 'Upload failed']);
    exit;
}

// Check size (100MB max)
if ($file['size'] > 104857600) {
    http_response_code(400);
    echo json_encode(['error' => 'File too large. Max 100MB']);
    exit;
}

// Generate file ID
$file_id = bin2hex(random_bytes(16));
$file_path = $user['id'] . '/' . $file_id . '_' . $file['name'];

// Upload to Supabase
$file_data = file_get_contents($file['tmp_name']);
$upload = uploadToSupabase($file_path, $file_data, $file['type']);

if (!$upload) {
    http_response_code(500);
    echo json_encode(['error' => 'Upload to storage failed']);
    exit;
}

// Save to database
$expires_at = date('Y-m-d H:i:s', strtotime('+30 minutes'));
$stmt = $pdo->prepare("INSERT INTO files (file_id, filename, original_name, size, mime_type, user_id, expires_at) VALUES (?, ?, ?, ?, ?, ?, ?)");
$stmt->execute([$file_id, $file_path, $file['name'], $file['size'], $file['type'], $user['id'], $expires_at]);

// Update user stats
$stmt = $pdo->prepare("UPDATE users SET total_uploads = total_uploads + 1, storage_used = storage_used + ? WHERE id = ?");
$stmt->execute([$file['size'], $user['id']]);

echo json_encode([
    'success' => true,
    'file_id' => $file_id,
    'original_name' => $file['name'],
    'size' => $file['size'],
    'expires_in_minutes' => 30,
    'download_url' => "https://" . $_SERVER['HTTP_HOST'] . "/api/download.php?id=" . $file_id
]);
?>