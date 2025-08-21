<?php
include_once __DIR__ . '/../../connect.php';
if ($_SERVER['REQUEST_METHOD'] !== 'POST') { http_response_code(405); echo json_encode(["success"=>false,"message"=>"Chỉ hỗ trợ POST"], JSON_UNESCAPED_UNICODE); exit; }

$raw=file_get_contents("php://input"); $b=json_decode($raw,true); if(!is_array($b)||empty($b)) $b=$_POST;

$MaVatTu   = isset($b['MaVatTu']) ? trim($b['MaVatTu']) : null;
$TenVatTu  = isset($b['TenVatTu']) ? trim($b['TenVatTu']) : null;
$LoaiVatTu = isset($b['LoaiVatTu']) ? trim($b['LoaiVatTu']) : null;
$DonViTinh = isset($b['DonViTinh']) ? trim($b['DonViTinh']) : null;
$NgayNhap  = isset($b['NgayNhap']) ? trim($b['NgayNhap']) : null;

$missing=[]; foreach(['MaVatTu','TenVatTu','LoaiVatTu','DonViTinh','NgayNhap'] as $k) if(empty($$k)) $missing[]=$k;
if($missing){ http_response_code(400); echo json_encode(["success"=>false,"error"=>"MISSING_FIELDS","missing"=>$missing], JSON_UNESCAPED_UNICODE); exit; }
if(!preg_match('/^[A-Za-z0-9]{1,5}$/',$MaVatTu)){ echo json_encode(["success"=>false,"error"=>"INVALID_MAVATTU"], JSON_UNESCAPED_UNICODE); exit; }

$NgayNhap=str_replace('/','-',$NgayNhap); $ts=strtotime($NgayNhap);
if($ts===false){ http_response_code(400); echo json_encode(["success"=>false,"error"=>"INVALID_DATE"], JSON_UNESCAPED_UNICODE); exit; }
$NgayNhap=date('Y-m-d',$ts);

$st=$conn->prepare("SELECT 1 FROM vattunongnghiep WHERE MaVatTu=? LIMIT 1");
$st->bind_param("s",$MaVatTu); $st->execute(); $st->store_result();
if($st->num_rows>0){ $st->close(); http_response_code(409); echo json_encode(["success"=>false,"error"=>"DUPLICATE_MAVATTU"], JSON_UNESCAPED_UNICODE); exit; }
$st->close();

$sql="INSERT INTO vattunongnghiep (MaVatTu, TenVatTu, LoaiVatTu, DonViTinh, NgayNhap) VALUES (?,?,?,?,?)";
$st=$conn->prepare($sql); $st->bind_param("sssss", $MaVatTu,$TenVatTu,$LoaiVatTu,$DonViTinh,$NgayNhap);
if($st->execute()) echo json_encode(["success"=>true,"message"=>"Thêm vật tư thành công","data"=>compact('MaVatTu','TenVatTu','LoaiVatTu','DonViTinh','NgayNhap')], JSON_UNESCAPED_UNICODE);
else { http_response_code(500); echo json_encode(["success"=>false,"error"=>"INSERT_FAILED","debug"=>$st->error], JSON_UNESCAPED_UNICODE); }
$st->close(); $conn->close();
