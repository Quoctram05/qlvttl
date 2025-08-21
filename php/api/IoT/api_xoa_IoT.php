<?php
include_once __DIR__ . '/../../connect.php';
if ($_SERVER['REQUEST_METHOD'] !== 'DELETE') {
  http_response_code(405);
  echo json_encode(["success"=>false,"message"=>"Chỉ hỗ trợ DELETE"], JSON_UNESCAPED_UNICODE); exit;
}

$MaIoT = isset($_GET['MaIoT']) ? trim($_GET['MaIoT']) : null;
$raw = file_get_contents("php://input");
$b = json_decode($raw, true);
if (!$MaIoT && is_array($b) && isset($b['MaIoT'])) $MaIoT = trim($b['MaIoT']);

if (!$MaIoT){ http_response_code(400); echo json_encode(["success"=>false,"error"=>"MISSING_MAIOT"], JSON_UNESCAPED_UNICODE); exit; }

/* tồn tại? */
$chk=$conn->prepare("SELECT 1 FROM thietbiIoT WHERE MaIoT=? LIMIT 1");
$chk->bind_param("s",$MaIoT); $chk->execute(); $chk->store_result();
if ($chk->num_rows===0){ $chk->close(); http_response_code(404); echo json_encode(["success"=>false,"error"=>"NOT_FOUND"], JSON_UNESCAPED_UNICODE); exit; }
$chk->close();

/* xóa */
$st=$conn->prepare("DELETE FROM thietbiIoT WHERE MaIoT=?");
$st->bind_param("s",$MaIoT);

if ($st->execute()){
  echo json_encode(["success"=>true,"message"=>"Đã xoá thiết bị IoT","deleted"=>$MaIoT], JSON_UNESCAPED_UNICODE);
} else {
  http_response_code(500);
  echo json_encode(["success"=>false,"error"=>"DELETE_FAILED","debug"=>$st->error], JSON_UNESCAPED_UNICODE);
}
$st->close(); $conn->close();
