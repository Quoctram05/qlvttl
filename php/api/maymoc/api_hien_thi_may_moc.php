<?php
include_once __DIR__ . '/../../connect.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
  http_response_code(405);
  echo json_encode(["success"=>false,"message"=>"Chỉ hỗ trợ GET"], JSON_UNESCAPED_UNICODE); exit;
}

$MaThietBi = isset($_GET['MaThietBi']) ? trim($_GET['MaThietBi']) : null;

if ($MaThietBi) {
  $st = $conn->prepare("SELECT MaThietBi, TenThietBi, LoaiThietBi, TrangThai, NgayNhap, NhaSanXuat
                        FROM thietbimaymoc WHERE MaThietBi=?");
  $st->bind_param("s", $MaThietBi);
  $st->execute(); $res = $st->get_result();
  if ($row = $res->fetch_assoc()) echo json_encode(["success"=>true,"data"=>$row], JSON_UNESCAPED_UNICODE);
  else { http_response_code(404); echo json_encode(["success"=>false,"message"=>"Không tìm thấy thiết bị"], JSON_UNESCAPED_UNICODE); }
  $st->close(); $conn->close(); exit;
}

$q = "SELECT MaThietBi, TenThietBi, LoaiThietBi, TrangThai, NgayNhap, NhaSanXuat
      FROM thietbimaymoc ORDER BY MaThietBi";
$res = $conn->query($q); $data=[]; while($r=$res->fetch_assoc()) $data[]=$r;
echo json_encode(["success"=>true,"data"=>$data], JSON_UNESCAPED_UNICODE);
$conn->close();
