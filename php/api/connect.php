<?php
// Nạp file config
include "config.php";

// Kết nối MySQLi
$conn = new mysqli($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME);

// Kiểm tra kết nối
if ($conn->connect_error) {
    die(json_encode([
        "success" => false,
        "message" => "Kết nối CSDL thất bại: " . $conn->connect_error
    ], JSON_UNESCAPED_UNICODE));
}

// Thiết lập UTF-8
$conn->set_charset("utf8mb4");

// Nếu chạy trực tiếp connect.php thì hiển thị thông báo test kết nối
if (basename($_SERVER['PHP_SELF']) === basename(__FILE__)) {
    echo json_encode([
        "success" => true,
        "message" => "Kết nối CSDL thành công!"
    ], JSON_UNESCAPED_UNICODE);
}
?>
