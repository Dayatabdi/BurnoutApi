<?php
header("Content-Type: application/json");

echo json_encode([
    "status" => "success",
    "name" => "BurnoutGuard API",
    "version" => "1.0.0",
    "message" => "API is running perfectly! 🚀",
    "timestamp" => date("Y-m-d H:i:s")
]);
?>
