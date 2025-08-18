<?php
include __DIR__ . "/connect.php";
if ($_SERVER['REQUEST_METHOD'] !== 'POST') { http_response_code(405); echo json_encode(["success"=>false,"message"=>"Chỉ hỗ trợ POST"], JSON_UNESCAPED_UNICODE); exit; }

$raw = file_get_contents("php://input"); $b = json_decode($raw, true); if (!is_array($b) || empty($b)) $b = $_POST;

$MaGiong  = isset($b['MaGiong']) ? trim($b['MaGiong']) : null;
$TenGiong = isset($b['TenGiong']) ? trim($b['TenGiong']) : null;
$NguonGoc = isset($b['NguonGoc']) ? trim($b['NguonGoc']) : null;
$DacDiem  = isset($b['DacDiem'])  ? trim($b['DacDiem'])  : null;
$NgayApDung = isset($b['NgayApDung']) ? trim($b['NgayApDung']) : null;

$missing=[]; foreach (['MaGiong','TenGiong'] as $k) if(empty($$k)) $missing[]=$k;
if ($missing){ http_response_code(400); echo json_encode(["success"=>false,"error"=>"MISSING_FIELDS","missing"=>$missing], JSON_UNESCAPED_UNICODE); exit; }
if (!preg_match('/^[A-Za-z0-9]{1,5}$/', $MaGiong)){ echo json_encode(["success"=>false,"error"=>"INVALID_MAGIONG"], JSON_UNESCAPED_UNICODE); exit; }

if ($NgayApDung!==""){ $NgayApDung=str_replace('/','-',$NgayApDung); $ts=strtotime($NgayApDung); if($ts===false){ http_response_code(400); echo json_encode(["success"=>false,"error"=>"INVALID_DATE"], JSON_UNESCAPED_UNICODE); exit; } $NgayApDung=date('Y-m-d',$ts); } else { $NgayApDung=null; }

$stmt=$conn->prepare("SELECT 1 FROM giongthanhlong WHERE MaGiong=? LIMIT 1");
$stmt->bind_param("s",$MaGiong); $stmt->execute(); $stmt->store_result();
if($stmt->num_rows>0){ $stmt->close(); http_response_code(409); echo json_encode(["success"=>false,"error"=>"DUPLICATE_MAGIONG"], JSON_UNESCAPED_UNICODE); exit; }
$stmt->close();

$sql="INSERT INTO giongthanhlong (MaGiong, TenGiong, NguonGoc, DacDiem, NgayApDung) VALUES (?,?,?,?,?)";
$stmt=$conn->prepare($sql);
$stmt->bind_param("sssss", $MaGiong,$TenGiong,$NguonGoc,$DacDiem,$NgayApDung);
if($stmt->execute()) echo json_encode(["success"=>true,"message"=>"Thêm giống thành công","data"=>compact('MaGiong','TenGiong','NguonGoc','DacDiem','NgayApDung')], JSON_UNESCAPED_UNICODE);
else { http_response_code(500); echo json_encode(["success"=>false,"error"=>"INSERT_FAILED","debug"=>$stmt->error], JSON_UNESCAPED_UNICODE); }
$stmt->close(); $conn->close();
