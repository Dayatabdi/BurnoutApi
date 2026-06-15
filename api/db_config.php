<?php
function getConnection() {
    $host = getenv('DB_HOST') ?: 'mysql-61c9485-dayatabdi7690-0df1.e.aivencloud.com';
    $port = getenv('DB_PORT') ?: 12174;
    $user = getenv('DB_USER') ?: 'avnadmin';
    $pass = getenv('DB_PASSWORD') ?: 'AVNS_P255Ll9ne3vFk-FQPUU';
    $db   = getenv('DB_NAME') ?: 'defaultdb';

    $conn = mysqli_init();
    
    // Karena Aiven mewajibkan SSL (SSL mode: REQUIRED)
    
    if (!$conn->real_connect($host, $user, $pass, $db, (int)$port, null, MYSQLI_CLIENT_SSL)) {
        http_response_code(500);
        echo json_encode(["status" => "error", "message" => "DB connection failed: " . mysqli_connect_error()]);
        exit;
    }

    // Disable strict mode
    $conn->query("SET SESSION sql_mode = ''");
    
    return $conn;
}

function getAuthEmail() {
    $headers = getallheaders();
    return $headers['Authorization'] ?? $headers['authorization'] ?? null;
}
