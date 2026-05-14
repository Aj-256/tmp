<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/supabase.php';

$file_id = $_GET['id'] ?? null;

if (!$file_id) {
    http_response_code(400);
    echo json_encode(['error' => 'File ID required']);
    exit;
}

// Get file
$stmt = $pdo->prepare("SELECT * FROM files WHERE file_id = ?");
$stmt->execute([$file_id]);
$file = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$file) {
    http_response_code(404);
    echo json_encode(['error' => 'File not found']);
    exit;
}

// Check expiry
if (strtotime($file['expires_at']) < time()) {
    http_response_code(410);
    echo json_encode(['error' => 'File has expired']);
    exit;
}

// Get signed URL
$signed_url = getSignedUrl($file['filename'], 300);

if (!$signed_url) {
    http_response_code(500);
    echo json_encode(['error' => 'Failed to generate download link']);
    exit;
}

// Increment download count
$stmt = $pdo->prepare("UPDATE files SET downloads = downloads + 1 WHERE id = ?");
$stmt->execute([$file['id']]);

header('Location: ' . $signed_url);
exit;
?>