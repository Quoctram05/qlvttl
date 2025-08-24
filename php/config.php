<?php
// ======================
// CẤU HÌNH DATABASE
// ======================
$DB_HOST = "localhost";      // Tên host
$DB_USER = "sql_nhom16_itimi";           // Tài khoản MySQL
$DB_PASS = "9f1db7853e8ab";               // Mật khẩu MySQL
$DB_NAME = "sql_nhom16_itimi";    // Tên CSDL

// ======================
// CẤU HÌNH KHÁC
// ======================
date_default_timezone_set("Asia/Ho_Chi_Minh"); // Múi giờ VN
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json; charset=utf-8");

// Cho phép preflight
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(204); exit; }

?>
