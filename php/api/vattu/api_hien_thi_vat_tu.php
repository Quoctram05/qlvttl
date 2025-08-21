<?php
include_once __DIR__ . '/../../connect.php';
if ($_SERVER['REQUEST_METHOD'] !== 'GET') { http_response_code(405); echo json_encode(["success"=>false,"message"=>"Chỉ hỗ trợ GET"], JSON_UNESCAPED_UNICODE); exit; }

$MaVatTu = isset($_GET['MaVatTu']) ? trim($_GET['MaVatTu']) : null;

if ($MaVatTu){
  $st=$conn->prepare("SELECT MaVatTu, TenVatTu, LoaiVatTu, DonViTinh, NgayNhap FROM vattunongnghiep WHERE MaVatTu=?");
  $st->bind_param("s",$MaVatTu); $st->execute(); $res=$st->get_result();
  if($row=$res->fetch_assoc()) echo json_encode(["success"=>true,"data"=>$row], JSON_UNESCAPED_UNICODE);
  else { http_response_code(404); echo json_encode(["success"=>false,"message"=>"Không tìm thấy vật tư"], JSON_UNESCAPED_UNICODE); }
  $st->close(); $conn->close(); exit;
}

$q="SELECT MaVatTu, TenVatTu, LoaiVatTu, DonViTinh, NgayNhap FROM vattunongnghiep ORDER BY MaVatTu";
$res=$conn->query($q); $data=[]; while($r=$res->fetch_assoc()) $data[]=$r;
echo json_encode(["success"=>true,"data"=>$data], JSON_UNESCAPED_UNICODE);
$conn->close();
