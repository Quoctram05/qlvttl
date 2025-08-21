<?php
include_once __DIR__ . '/../../connect.php';
if ($_SERVER['REQUEST_METHOD'] !== 'PUT') { http_response_code(405); echo json_encode(["success"=>false,"message"=>"Chỉ hỗ trợ PUT"], JSON_UNESCAPED_UNICODE); exit; }

$MaVatTu = isset($_GET['MaVatTu']) ? trim($_GET['MaVatTu']) : null;
$raw=file_get_contents("php://input"); $b=json_decode($raw,true); if(!is_array($b)) $b=[];
if(!$MaVatTu && isset($b['MaVatTu'])) $MaVatTu=trim($b['MaVatTu']);
if(!$MaVatTu){ http_response_code(400); echo json_encode(["success"=>false,"error"=>"MISSING_MAVATTU"], JSON_UNESCAPED_UNICODE); exit; }

$chk=$conn->prepare("SELECT 1 FROM vattunongnghiep WHERE MaVatTu=?"); $chk->bind_param("s",$MaVatTu); $chk->execute(); $chk->store_result();
if($chk->num_rows===0){ $chk->close(); http_response_code(404); echo json_encode(["success"=>false,"error"=>"NOT_FOUND"], JSON_UNESCAPED_UNICODE); exit; }
$chk->close();

$fields=[]; $types=""; $params=[];
if(isset($b['TenVatTu']) && $b['TenVatTu']!==""){ $fields[]="TenVatTu=?"; $types.="s"; $params[]=trim($b['TenVatTu']); }
if(isset($b['LoaiVatTu']) && $b['LoaiVatTu']!==""){ $fields[]="LoaiVatTu=?"; $types.="s"; $params[]=trim($b['LoaiVatTu']); }
if(isset($b['DonViTinh']) && $b['DonViTinh']!==""){ $fields[]="DonViTinh=?"; $types.="s"; $params[]=trim($b['DonViTinh']); }
if(array_key_exists('NgayNhap',$b)){
  if($b['NgayNhap']==="" || $b['NgayNhap']===null){ http_response_code(400); echo json_encode(["success"=>false,"error"=>"INVALID_DATE","message"=>"NgayNhap không được rỗng"], JSON_UNESCAPED_UNICODE); exit; }
  $ns=str_replace('/','-',trim($b['NgayNhap'])); $ts=strtotime($ns);
  if($ts===false){ http_response_code(400); echo json_encode(["success"=>false,"error"=>"INVALID_DATE"], JSON_UNESCAPED_UNICODE); exit; }
  $ns=date('Y-m-d',$ts); $fields[]="NgayNhap=?"; $types.="s"; $params[]=$ns;
}
if(empty($fields)){ http_response_code(400); echo json_encode(["success"=>false,"error"=>"NO_FIELDS"], JSON_UNESCAPED_UNICODE); exit; }

$sql="UPDATE vattunongnghiep SET ".implode(", ",$fields)." WHERE MaVatTu=?";
$types.="s"; $params[]=$MaVatTu;
$st=$conn->prepare($sql); $st->bind_param($types, ...$params);

if($st->execute()) echo json_encode(["success"=>true,"message"=>"Cập nhật vật tư thành công","updated"=>$st->affected_rows], JSON_UNESCAPED_UNICODE);
else { http_response_code(500); echo json_encode(["success"=>false,"error"=>"UPDATE_FAILED","debug"=>$st->error], JSON_UNESCAPED_UNICODE); }
$st->close(); $conn->close();
