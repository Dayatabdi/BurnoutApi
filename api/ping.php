<?php
require_once 'db_config.php';

// Membuka koneksi ke Aiven
$conn = getConnection();

// Melakukan query super ringan cuma buat ngasih tau Aiven "Hei, aku masih hidup!"
if ($conn->query("SELECT 1")) {
    echo json_encode(["status" => "success", "message" => "Database is awake!"]);
} else {
    echo json_encode(["status" => "error", "message" => "Failed to wake database"]);
}

$conn->close();
