<?php
require_once 'db_config.php';

$id = $_GET['id'] ?? '';
if (empty($id)) {
    http_response_code(400);
    exit;
}

$conn = getConnection();
$stmt = $conn->prepare("SELECT image_data FROM burnout_records WHERE id = ?");
$stmt->bind_param("s", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    http_response_code(404);
    exit;
}

$row = $result->fetch_assoc();
$imageData = $row['image_data'];
$conn->close();

header("Content-Type: image/jpeg");
echo base64_decode($imageData);
