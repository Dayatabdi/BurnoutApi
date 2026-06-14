<?php
// =========================================================
// KONFIGURASI DATABASE
// Sesuaikan dengan kredensial dari hosting kamu
// (InfinityFree / 000webhost biasanya kasih info ini di cPanel)
// =========================================================

define('DB_HOST', 'sql202.infinityfree.com');      // biasanya 'localhost' atau host khusus dari provider
define('DB_USER', 'if0_42178272');   // contoh: epiz_xxxxxxx_burnout
define('DB_PASS', 'Dayat7690');
define('DB_NAME', 'if0_42178272_burnout');

function getConnection() {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    if ($conn->connect_error) {
        http_response_code(500);
        echo json_encode(["status" => "error", "message" => "Koneksi DB gagal: " . $conn->connect_error]);
        exit;
    }
    return $conn;
}

// Ambil header Authorization dengan aman (beberapa shared hosting
// tidak meneruskan header Authorization secara default)
function getAuthEmail() {
    $headers = [];

    if (function_exists('getallheaders')) {
        $headers = getallheaders();
    }

    $email = null;
    if (isset($headers['Authorization'])) {
        $email = $headers['Authorization'];
    } elseif (isset($_SERVER['HTTP_AUTHORIZATION'])) {
        $email = $_SERVER['HTTP_AUTHORIZATION'];
    } elseif (isset($_SERVER['REDIRECT_HTTP_AUTHORIZATION'])) {
        $email = $_SERVER['REDIRECT_HTTP_AUTHORIZATION'];
    }

    return $email ? trim($email) : null;
}
