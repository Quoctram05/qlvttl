<?php
include_once __DIR__ . '/../../connect.php';
if ($_SERVER['REQUEST_METHOD'] !== 'PUT') { http_response_code(405); echo json_encode(["success"=>false,"message"=>"Chỉ hỗ trợ PUT"], JSON_UNESCAPED_UNICODE); exit; }

$MaGiong = isset($_GET['MaGiong']) ? trim($_GET['MaGiong']) : null;
$raw=file_get_contents("php://input"); $b=json_decode($raw,true); if(!is_array($b)) $b=[];
if(!$MaGiong && isset($b['MaGiong'])) $MaGiong=trim($b['MaGiong']);
if(!$MaGiong){ http_response_code(400); echo json_encode(["success"=>false,"error"=>"MISSING_MAGIONG"], JSON_UNESCAPED_UNICODE); exit; }

$chk=$conn->prepare("SELECT 1 FROM giongthanhlong WHERE MaGiong=?"); $chk->bind_param("s",$MaGiong); $chk->execute(); $chk->store_result();
if($chk->num_rows===0){ $chk->close(); http_response_code(404); echo json_encode(["success"=>false,"error"=>"NOT_FOUND"], JSON_UNESCAPED_UNICODE); exit;}
$chk->close();

$fields=[]; $types=""; $params=[];
if(isset($b['TenGiong']) && $b['TenGiong']!==""){ $fields[]="TenGiong=?"; $types.="s"; $params[]=trim($b['TenGiong']); }
if(isset($b['NguonGoc'])){ $fields[]="NguonGoc=?"; $types.="s"; $params[]=(string)$b['NguonGoc']; }
if(isset($b['DacDiem'])){ $fields[]="DacDiem=?"; $types.="s"; $params[]=(string)$b['DacDiem']; }
if(array_key_exists('NgayApDung',$b)){
  if($b['NgayApDung']==="" || $b['NgayApDung']===null){ $fields[]="NgayApDung=NULL"; }
  else { $ns=str_replace('/','-',trim($b['NgayApDung'])); $ts=strtotime($ns); if($ts===false){ http_response_code(400); echo json_encode(["success"=>false,"error"=>"INVALID_DATE"], JSON_UNESCAPED_UNICODE); exit; } $ns=date('Y-m-d',$ts); $fields[]="NgayApDung=?"; $types.="s"; $params[]=$ns; }
}
if(empty($fields)){ http_response_code(400); echo json_encode(["success"=>false,"error"=>"NO_FIELDS"], JSON_UNESCAPED_UNICODE); exit; }

$sql="UPDATE giongthanhlong SET ".implode(", ",$fields)." WHERE MaGiong=?";
$types.="s"; $params[]=$MaGiong;
$stmt=$conn->prepare($sql); $stmt->bind_param($types, ...$params);

if($stmt->execute()) echo json_encode(["success"=>true,"message"=>"Cập nhật giống thành công","updated"=>$stmt->affected_rows], JSON_UNESCAPED_UNICODE);
else { http_response_code(500); echo json_encode(["success"=>false,"error"=>"UPDATE_FAILED","debug"=>$stmt->error], JSON_UNESCAPED_UNICODE); }
$stmt->close(); $conn->close();
