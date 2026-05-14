<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/supabase.php';

header('Content-Type: application/json');

session_start();

// Check if logged in
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Login required']);
    exit;
}

$file_id = $_GET['id'] ?? null;

if (!$file_id) {
    http_response_code(400);
    echo json_encode(['error' => 'File ID required']);
    exit;
}

// Get file
$stmt = $pdo->prepare("SELECT * FROM files WHERE file_id = ? AND user_id = ?");
$stmt->execute([$file_id, $_SESSION['user_id']]);
$file = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$file) {
    http_response_code(404);
    echo json_encode(['error' => 'File not found']);
    exit;
}

// Delete from Supabase
deleteFromSupabase($file['filename']);

// Update user storage
$stmt = $pdo->prepare("UPDATE users SET storage_used = storage_used - ? WHERE id = ?");
$stmt->execute([$file['size'], $_SESSION['user_id']]);

// Delete from database
$stmt = $pdo->prepare("DELETE FROM files WHERE id = ?");
$stmt->execute([$file['id']]);

echo json_encode(['success' => true, 'message' => 'File deleted']);
?>