<?php
include __DIR__ . "/connect.php";
if ($_SERVER['REQUEST_METHOD'] !== 'DELETE') { http_response_code(405); echo json_encode(["success"=>false,"message"=>"Chỉ hỗ trợ DELETE"], JSON_UNESCAPED_UNICODE); exit; }

$MaGiong = isset($_GET['MaGiong']) ? trim($_GET['MaGiong']) : null;
$raw=file_get_contents("php://input"); $b=json_decode($raw,true);
if(!$MaGiong && is_array($b) && isset($b['MaGiong'])) $MaGiong=trim($b['MaGiong']);
if(!$MaGiong){ http_response_code(400); echo json_encode(["success"=>false,"error"=>"MISSING_MAGIONG"], JSON_UNESCAPED_UNICODE); exit; }

$chk=$conn->prepare("SELECT 1 FROM giongthanhlong WHERE MaGiong=?"); $chk->bind_param("s",$MaGiong); $chk->execute(); $chk->store_result();
if($chk->num_rows===0){ $chk->close(); http_response_code(404); echo json_encode(["success"=>false,"error"=>"NOT_FOUND"], JSON_UNESCAPED_UNICODE); exit; }
$chk->close();

$stmt=$conn->prepare("DELETE FROM giongthanhlong WHERE MaGiong=?"); $stmt->bind_param("s",$MaGiong);
if($stmt->execute()) echo json_encode(["success"=>true,"message"=>"Đã xoá giống","deleted"=>$MaGiong], JSON_UNESCAPED_UNICODE);
else { http_response_code(500); echo json_encode(["success"=>false,"error"=>"DELETE_FAILED","debug"=>$stmt->error], JSON_UNESCAPED_UNICODE); }
$stmt->close(); $conn->close();
