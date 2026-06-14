<?php
require_once 'db_config.php';

$conn = getConnection();

$sql = "CREATE TABLE IF NOT EXISTS burnout_records (
    id VARCHAR(50) PRIMARY KEY,
    user_id VARCHAR(255) NOT NULL,
    nama VARCHAR(255) NOT NULL,
    jam_tidur DECIMAL(4,1),
    mudah_lelah TINYINT(1) DEFAULT 0,
    sulit_fokus TINYINT(1) DEFAULT 0,
    susah_tidur TINYINT(1) DEFAULT 0,
    mudah_marah TINYINT(1) DEFAULT 0,
    tidak_bersemangat TINYINT(1) DEFAULT 0,
    overwhelmed TINYINT(1) DEFAULT 0,
    stres_level VARCHAR(50),
    skor INT DEFAULT 0,
    image_id VARCHAR(100),
    tanggal TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";

if ($conn->query($sql)) {
    echo json_encode(["status" => "success", "message" => "Tabel berhasil dibuat!"]);
} else {
    echo json_encode(["status" => "error", "message" => $conn->error]);
}

$conn->close();
