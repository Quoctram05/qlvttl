<?php
// ======================
// CẤU HÌNH DATABASE
// ======================
$DB_HOST = "localhost";      // Tên host
$DB_USER = "root";           // Tài khoản MySQL
$DB_PASS = "";               // Mật khẩu MySQL
$DB_NAME = "qlvttl_dvt";    // Tên CSDL

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
