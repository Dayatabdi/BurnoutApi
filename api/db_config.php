<?php
function getConnection() {
    $host = 'mysql-61c9485-dayatabdi7690-0df1.e.aivencloud.com';
    $port = 12174;
    $user = 'avnadmin';
   $pass = 'AVNS_P255Ll9ne3vFk-FQPUU';
    $db   = 'defaultdb';

    $conn = new mysqli($host, $user, $pass, $db, $port);
    if ($conn->connect_error) {
        http_response_code(500);
        echo json_encode(["status" => "error", "message" => "DB connection failed: " . $conn->connect_error]);
        exit;
    }
    return $conn;
}

function getAuthEmail() {
    $headers = getallheaders();
    return $headers['Authorization'] ?? $headers['authorization'] ?? null;
}
