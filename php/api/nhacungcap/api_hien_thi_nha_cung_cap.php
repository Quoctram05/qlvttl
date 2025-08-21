<?php
include_once __DIR__ . '/../../connect.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
  http_response_code(405);
  echo json_encode(["success"=>false,"message"=>"Chỉ hỗ trợ GET"], JSON_UNESCAPED_UNICODE); exit;
}

$MaNCC = isset($_GET['MaNCC']) ? trim($_GET['MaNCC']) : null;

if ($MaNCC) {
  $st = $conn->prepare("SELECT MaNCC, TenNCC, DiaChi, SDT, Email FROM nhacungcapvattu WHERE MaNCC=?");
  $st->bind_param("s", $MaNCC);
  $st->execute(); $res = $st->get_result();
  if ($row = $res->fetch_assoc()) echo json_encode(["success"=>true,"data"=>$row], JSON_UNESCAPED_UNICODE);
  else { http_response_code(404); echo json_encode(["success"=>false,"message"=>"Không tìm thấy nhà cung cấp"], JSON_UNESCAPED_UNICODE); }
  $st->close(); $conn->close(); exit;
}

$q = "SELECT MaNCC, TenNCC, DiaChi, SDT, Email FROM nhacungcapvattu ORDER BY MaNCC";
$res = $conn->query($q); $data = [];
while ($r = $res->fetch_assoc()) $data[] = $r;
echo json_encode(["success"=>true,"data"=>$data], JSON_UNESCAPED_UNICODE);
$conn->close();
