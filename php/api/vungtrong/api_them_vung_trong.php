<?php
include_once __DIR__ . '/../../connect.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(["success"=>false,"message"=>"Chỉ hỗ trợ POST"], JSON_UNESCAPED_UNICODE);
    exit;
}

$raw = file_get_contents("php://input");
$body = json_decode($raw, true);
if (!is_array($body) || empty($body)) $body = $_POST;

$MaVung  = isset($body['MaVung']) ? trim($body['MaVung']) : null;
$TenVung = isset($body['TenVung']) ? trim($body['TenVung']) : null;
$MaHo    = isset($body['MaHo']) ? trim($body['MaHo']) : null;
$DienTich= isset($body['DienTich']) ? $body['DienTich'] : null;
$LoaiDat = isset($body['LoaiDat']) ? trim($body['LoaiDat']) : null;
$DieuKienTN = isset($body['DieuKienTN']) ? trim($body['DieuKienTN']) : null;
$ViTriToaDo = isset($body['ViTriToaDo']) ? trim($body['ViTriToaDo']) : null;
$MaGiong = isset($body['MaGiong']) && $body['MaGiong']!=="" ? trim($body['MaGiong']) : null;
$TrangThaiVung = isset($body['TrangThaiVung']) ? (int)$body['TrangThaiVung'] : null;

$required = ['MaVung','TenVung','MaHo','DienTich','LoaiDat','TrangThaiVung'];
$missing = [];
foreach ($required as $k) if (!isset($body[$k]) || $body[$k]==='') $missing[]=$k;
if ($missing){ http_response_code(400); echo json_encode(["success"=>false,"error"=>"MISSING_FIELDS","missing"=>$missing], JSON_UNESCAPED_UNICODE); exit; }
if (!preg_match('/^[A-Za-z0-9]{1,5}$/',$MaVung)){ echo json_encode(["success"=>false,"error"=>"INVALID_MAVUNG"], JSON_UNESCAPED_UNICODE); exit; }
if (!preg_match('/^[A-Za-z0-9]{1,5}$/',$MaHo)){ echo json_encode(["success"=>false,"error"=>"INVALID_MAHO"], JSON_UNESCAPED_UNICODE); exit; }
if (!is_numeric($DienTich) || $DienTich<=0){ echo json_encode(["success"=>false,"error"=>"INVALID_DIENTICH"], JSON_UNESCAPED_UNICODE); exit; }
if (!in_array($TrangThaiVung,[0,1,2],true)){ echo json_encode(["success"=>false,"error"=>"INVALID_TRANGTHAI"], JSON_UNESCAPED_UNICODE); exit; }

$stmt=$conn->prepare("SELECT 1 FROM vungtrong WHERE MaVung=? LIMIT 1");
$stmt->bind_param("s",$MaVung); $stmt->execute(); $stmt->store_result();
if ($stmt->num_rows>0){ $stmt->close(); http_response_code(409); echo json_encode(["success"=>false,"error"=>"DUPLICATE_MAVUNG"], JSON_UNESCAPED_UNICODE); exit; }
$stmt->close();

$stmt=$conn->prepare("SELECT 1 FROM honongdan WHERE MaHo=? LIMIT 1");
$stmt->bind_param("s",$MaHo); $stmt->execute(); $stmt->store_result();
if ($stmt->num_rows===0){ $stmt->close(); http_response_code(400); echo json_encode(["success"=>false,"error"=>"FK_MAHO_NOT_FOUND"], JSON_UNESCAPED_UNICODE); exit; }
$stmt->close();

if ($MaGiong){
    $stmt=$conn->prepare("SELECT 1 FROM giongthanhlong WHERE MaGiong=? LIMIT 1");
    $stmt->bind_param("s",$MaGiong); $stmt->execute(); $stmt->store_result();
    if ($stmt->num_rows===0){ $stmt->close(); http_response_code(400); echo json_encode(["success"=>false,"error"=>"FK_MAGIONG_NOT_FOUND"], JSON_UNESCAPED_UNICODE); exit; }
    $stmt->close();
}

$sql="INSERT INTO vungtrong
      (MaVung,TenVung,MaHo,DienTich,LoaiDat,DieuKienTN,ViTriToaDo,MaGiong,TrangThaiVung)
      VALUES (?,?,?,?,?,?,?,?,?)";
$stmt=$conn->prepare($sql);
$DienTichF = (float)$DienTich;
$stmt->bind_param("sssdssssi", $MaVung,$TenVung,$MaHo,$DienTichF,$LoaiDat,$DieuKienTN,$ViTriToaDo,$MaGiong,$TrangThaiVung);
/* Chuỗi types đúng phải là: "sss dssssi" không có khoảng trắng → "sssdssssi" */
$stmt->bind_param("sssdssssi", $MaVung,$TenVung,$MaHo,$DienTichF,$LoaiDat,$DieuKienTN,$ViTriToaDo,$MaGiong,$TrangThaiVung);

if ($stmt->execute()){
    echo json_encode(["success"=>true,"message"=>"Thêm vùng trồng thành công","data"=>[
        "MaVung"=>$MaVung,"TenVung"=>$TenVung,"MaHo"=>$MaHo,"DienTich"=>$DienTichF,
        "LoaiDat"=>$LoaiDat,"DieuKienTN"=>$DieuKienTN,"ViTriToaDo"=>$ViTriToaDo,
        "MaGiong"=>$MaGiong,"TrangThaiVung"=>$TrangThaiVung
    ]], JSON_UNESCAPED_UNICODE);
} else {
    http_response_code(500);
    echo json_encode(["success"=>false,"error"=>"INSERT_FAILED","debug"=>$stmt->error], JSON_UNESCAPED_UNICODE);
}
$stmt->close(); $conn->close();
