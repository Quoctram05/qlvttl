<?php
include __DIR__ . "/connect.php";

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(["success"=>false,"message"=>"Chỉ hỗ trợ GET"], JSON_UNESCAPED_UNICODE);
    exit;
}

$MaTaiKhoan = isset($_GET['MaTaiKhoan']) ? trim($_GET['MaTaiKhoan']) : null;

if ($MaTaiKhoan) {
    $stmt = $conn->prepare("SELECT MaTaiKhoan, TenDangNhap, VaiTro, TrangThai, NgayDangKy, MaHo FROM taikhoannguoidung WHERE MaTaiKhoan=?");
    $stmt->bind_param("s", $MaTaiKhoan);
    $stmt->execute(); $res = $stmt->get_result();
    if ($row = $res->fetch_assoc()) echo json_encode(["success"=>true,"data"=>$row], JSON_UNESCAPED_UNICODE);
    else { http_response_code(404); echo json_encode(["success"=>false,"message"=>"Không tìm thấy tài khoản"], JSON_UNESCAPED_UNICODE); }
    $stmt->close(); $conn->close(); exit;
}

$q = "SELECT MaTaiKhoan, TenDangNhap, VaiTro, TrangThai, NgayDangKy, MaHo FROM taikhoannguoidung ORDER BY MaTaiKhoan";
$res = $conn->query($q); $data = [];
while ($r = $res->fetch_assoc()) $data[] = $r;
echo json_encode(["success"=>true,"data"=>$data], JSON_UNESCAPED_UNICODE);
$conn->close();
