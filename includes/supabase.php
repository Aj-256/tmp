<?php
require_once __DIR__ . '/../vendor/autoload.php';

use Supabase\Storage\StorageClient;

$supabaseUrl = getenv('SUPABASE_URL');
$supabaseKey = getenv('SUPABASE_KEY');
$bucketName = getenv('SUPABASE_BUCKET');

$storage = new StorageClient($supabaseUrl, $supabaseKey);

function uploadToSupabase($filePath, $fileData, $contentType) {
    global $storage, $bucketName;
    try {
        return $storage->from($bucketName)->upload($filePath, $fileData, [
            'content-type' => $contentType
        ]);
    } catch(Exception $e) {
        return false;
    }
}

function getSignedUrl($filePath, $expiresIn = 300) {
    global $storage, $bucketName;
    try {
        $url = $storage->from($bucketName)->createSignedUrl($filePath, $expiresIn);
        return $url['signedURL'] ?? null;
    } catch(Exception $e) {
        return null;
    }
}

function deleteFromSupabase($filePath) {
    global $storage, $bucketName;
    try {
        return $storage->from($bucketName)->remove([$filePath]);
    } catch(Exception $e) {
        return false;
    }
}
?>