<?php
// Endpoint untuk mengambil gambar
// Contoh akses: image.php?id=img_xxxxx

$imageId = $_GET['id'] ?? '';

if (empty($imageId)) {
    http_response_code(400);
    exit('Image ID diperlukan');
}

// Sanitasi — hanya huruf, angka, underscore, titik
if (!preg_match('/^[a-zA-Z0-9_.]+$/', $imageId)) {
    http_response_code(400);
    exit('ID tidak valid');
}

$path = __DIR__ . '/images/' . $imageId . '.jpg';

if (!file_exists($path)) {
    http_response_code(404);
    exit('Gambar tidak ditemukan');
}

header('Content-Type: image/jpeg');
header('Content-Length: ' . filesize($path));
readfile($path);