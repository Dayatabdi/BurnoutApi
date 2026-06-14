<?php
require_once 'db_config.php';

$conn = getConnection();
$result = $conn->query("SELECT id, user_id, nama FROM burnout_records");
$data = [];
while ($row = $result->fetch_assoc()) {
    $data[] = $row;
}
echo json_encode($data);
$conn->close();
