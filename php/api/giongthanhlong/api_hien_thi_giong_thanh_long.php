<?php
include_once __DIR__ . '/../../connect.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
  http_response_code(405);
  echo json_encode(["success"=>false,"message"=>"Chỉ hỗ trợ GET"], JSON_UNESCAPED_UNICODE); exit;
}

$MaGiong = isset($_GET['MaGiong']) ? trim($_GET['MaGiong']) : null;

if ($MaGiong) {
  $stmt = $conn->prepare("SELECT MaGiong, TenGiong, NguonGoc, DacDiem, NgayApDung FROM giongthanhlong WHERE MaGiong=?");
  $stmt->bind_param("s", $MaGiong);
  $stmt->execute(); $res = $stmt->get_result();
  if ($row = $res->fetch_assoc()) echo json_encode(["success"=>true,"data"=>$row], JSON_UNESCAPED_UNICODE);
  else { http_response_code(404); echo json_encode(["success"=>false,"message"=>"Không tìm thấy giống"], JSON_UNESCAPED_UNICODE); }
  $stmt->close(); $conn->close(); exit;
}

$q = "SELECT MaGiong, TenGiong, NguonGoc, DacDiem, NgayApDung FROM giongthanhlong ORDER BY MaGiong";
$res = $conn->query($q); $data=[]; while($r=$res->fetch_assoc()) $data[]=$r;
echo json_encode(["success"=>true,"data"=>$data], JSON_UNESCAPED_UNICODE);
$conn->close();
