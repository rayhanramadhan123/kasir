<?php 
$conn = new mysqli("localhost", "root", "", "kasir_db");

// Check connection
if ($conn->connect_error) {
    error_log("Database connection failed: " . $conn->connect_error, 3, '/var/log/php_errors.log');
    http_response_code(500);
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Internal server error']);
    exit();
}

// Set charset to UTF-8
$conn->set_charset("utf8mb4");
?>