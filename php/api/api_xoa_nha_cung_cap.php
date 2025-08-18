<?php
include __DIR__ . "/connect.php";

if ($_SERVER['REQUEST_METHOD'] !== 'DELETE') {
  http_response_code(405);
  echo json_encode(["success"=>false,"message"=>"Chỉ hỗ trợ DELETE"], JSON_UNESCAPED_UNICODE); exit;
}

$MaNCC = isset($_GET['MaNCC']) ? trim($_GET['MaNCC']) : null;
$raw = file_get_contents("php://input");
$b = json_decode($raw, true);
if (!$MaNCC && is_array($b) && isset($b['MaNCC'])) $MaNCC = trim($b['MaNCC']);

if (!$MaNCC) { http_response_code(400); echo json_encode(["success"=>false,"error"=>"MISSING_MANCC"], JSON_UNESCAPED_UNICODE); exit; }

/* tồn tại? */
$chk=$conn->prepare("SELECT 1 FROM nhacungcapvattu WHERE MaNCC=? LIMIT 1");
$chk->bind_param("s",$MaNCC); $chk->execute(); $chk->store_result();
if ($chk->num_rows===0){ $chk->close(); http_response_code(404); echo json_encode(["success"=>false,"error"=>"NOT_FOUND"], JSON_UNESCAPED_UNICODE); exit; }
$chk->close();

/* xóa */
$st=$conn->prepare("DELETE FROM nhacungcapvattu WHERE MaNCC=?");
$st->bind_param("s",$MaNCC);

if ($st->execute()){
  echo json_encode(["success"=>true,"message"=>"Đã xoá nhà cung cấp","deleted"=>$MaNCC], JSON_UNESCAPED_UNICODE);
} else {
  http_response_code(500);
  echo json_encode(["success"=>false,"error"=>"DELETE_FAILED","debug"=>$st->error], JSON_UNESCAPED_UNICODE);
}
$st->close(); $conn->close();
