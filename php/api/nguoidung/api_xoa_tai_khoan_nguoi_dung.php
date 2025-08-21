<?php
include_once __DIR__ . '/../../connect.php';

if ($_SERVER['REQUEST_METHOD'] !== 'DELETE') {
    http_response_code(405);
    echo json_encode(["success"=>false,"message"=>"Chỉ hỗ trợ DELETE"], JSON_UNESCAPED_UNICODE);
    exit;
}

$MaTaiKhoan = isset($_GET['MaTaiKhoan']) ? trim($_GET['MaTaiKhoan']) : null;
$raw = file_get_contents("php://input");
$b = json_decode($raw, true);
if (!$MaTaiKhoan && is_array($b) && isset($b['MaTaiKhoan'])) $MaTaiKhoan = trim($b['MaTaiKhoan']);

if (!$MaTaiKhoan) { http_response_code(400); echo json_encode(["success"=>false,"error"=>"MISSING_MATAIKHOAN"], JSON_UNESCAPED_UNICODE); exit; }

/* tồn tại? */
$chk = $conn->prepare("SELECT 1 FROM taikhoannguoidung WHERE MaTaiKhoan=? LIMIT 1");
$chk->bind_param("s", $MaTaiKhoan); $chk->execute(); $chk->store_result();
if ($chk->num_rows === 0) { $chk->close(); http_response_code(404); echo json_encode(["success"=>false,"error"=>"NOT_FOUND"], JSON_UNESCAPED_UNICODE); exit; }
$chk->close();

/* xóa */
$st = $conn->prepare("DELETE FROM taikhoannguoidung WHERE MaTaiKhoan=?");
$st->bind_param("s", $MaTaiKhoan);

if ($st->execute()) {
    echo json_encode(["success"=>true,"message"=>"Đã xoá tài khoản", "deleted"=>$MaTaiKhoan], JSON_UNESCAPED_UNICODE);
} else {
    http_response_code(500);
    echo json_encode(["success"=>false,"error"=>"DELETE_FAILED","debug"=>$st->error], JSON_UNESCAPED_UNICODE);
}

$st->close(); $conn->close();
