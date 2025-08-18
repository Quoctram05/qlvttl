<?php
include __DIR__ . "/connect.php";
if ($_SERVER['REQUEST_METHOD'] !== 'DELETE') { http_response_code(405); echo json_encode(["success"=>false,"message"=>"Chỉ hỗ trợ DELETE"], JSON_UNESCAPED_UNICODE); exit; }

$MaVatTu = isset($_GET['MaVatTu']) ? trim($_GET['MaVatTu']) : null;
$raw=file_get_contents("php://input"); $b=json_decode($raw,true);
if(!$MaVatTu && is_array($b) && isset($b['MaVatTu'])) $MaVatTu=trim($b['MaVatTu']);
if(!$MaVatTu){ http_response_code(400); echo json_encode(["success"=>false,"error"=>"MISSING_MAVATTU"], JSON_UNESCAPED_UNICODE); exit; }

$chk=$conn->prepare("SELECT 1 FROM vattunongnghiep WHERE MaVatTu=?"); $chk->bind_param("s",$MaVatTu); $chk->execute(); $chk->store_result();
if($chk->num_rows===0){ $chk->close(); http_response_code(404); echo json_encode(["success"=>false,"error"=>"NOT_FOUND"], JSON_UNESCAPED_UNICODE); exit; }
$chk->close();

$st=$conn->prepare("DELETE FROM vattunongnghiep WHERE MaVatTu=?"); $st->bind_param("s",$MaVatTu);
if($st->execute()) echo json_encode(["success"=>true,"message"=>"Đã xoá vật tư","deleted"=>$MaVatTu], JSON_UNESCAPED_UNICODE);
else { http_response_code(500); echo json_encode(["success"=>false,"error"=>"DELETE_FAILED","debug"=>$st->error], JSON_UNESCAPED_UNICODE); }
$st->close(); $conn->close();
