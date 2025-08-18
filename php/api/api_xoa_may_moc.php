<?php
include __DIR__ . "/connect.php";

if ($_SERVER['REQUEST_METHOD'] !== 'DELETE') {
  http_response_code(405);
  echo json_encode(["success"=>false,"message"=>"Chỉ hỗ trợ DELETE"], JSON_UNESCAPED_UNICODE); exit;
}

$MaThietBi = isset($_GET['MaThietBi']) ? trim($_GET['MaThietBi']) : null;
$raw = file_get_contents("php://input");
$b = json_decode($raw, true);
if (!$MaThietBi && is_array($b) && isset($b['MaThietBi'])) $MaThietBi = trim($b['MaThietBi']);

if (!$MaThietBi){ http_response_code(400); echo json_encode(["success"=>false,"error"=>"MISSING_MATHIETBI"], JSON_UNESCAPED_UNICODE); exit; }

/* tồn tại? */
$chk=$conn->prepare("SELECT 1 FROM thietbimaymoc WHERE MaThietBi=? LIMIT 1");
$chk->bind_param("s",$MaThietBi); $chk->execute(); $chk->store_result();
if ($chk->num_rows===0){ $chk->close(); http_response_code(404); echo json_encode(["success"=>false,"error"=>"NOT_FOUND"], JSON_UNESCAPED_UNICODE); exit; }
$chk->close();

/* xóa */
$st=$conn->prepare("DELETE FROM thietbimaymoc WHERE MaThietBi=?");
$st->bind_param("s",$MaThietBi);

if ($st->execute()){
  echo json_encode(["success"=>true,"message"=>"Đã xoá thiết bị","deleted"=>$MaThietBi], JSON_UNESCAPED_UNICODE);
} else {
  http_response_code(500);
  echo json_encode(["success"=>false,"error"=>"DELETE_FAILED","debug"=>$st->error], JSON_UNESCAPED_UNICODE);
}
$st->close(); $conn->close();
