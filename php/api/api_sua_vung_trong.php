<?php
include __DIR__ . "/connect.php";

if ($_SERVER['REQUEST_METHOD'] !== 'PUT') {
    http_response_code(405);
    echo json_encode(["success"=>false,"message"=>"Chỉ hỗ trợ PUT"], JSON_UNESCAPED_UNICODE);
    exit;
}

$MaVung = isset($_GET['MaVung']) ? trim($_GET['MaVung']) : null;
$raw = file_get_contents("php://input");
$body = json_decode($raw, true);
if (!is_array($body)) $body = [];
if (!$MaVung && isset($body['MaVung'])) $MaVung = trim($body['MaVung']);

if (!$MaVung) { http_response_code(400); echo json_encode(["success"=>false,"error"=>"MISSING_MAVUNG"], JSON_UNESCAPED_UNICODE); exit; }

/* Tồn tại chưa */
$chk=$conn->prepare("SELECT 1 FROM vungtrong WHERE MaVung=? LIMIT 1");
$chk->bind_param("s",$MaVung); $chk->execute(); $chk->store_result();
if ($chk->num_rows===0){ $chk->close(); http_response_code(404); echo json_encode(["success"=>false,"error"=>"NOT_FOUND"], JSON_UNESCAPED_UNICODE); exit;}
$chk->close();

/* Build câu update động */
$fields=[]; $types=""; $params=[];

/* TenVung */
if (isset($body['TenVung']) && $body['TenVung']!==""){ $fields[]="TenVung=?"; $types.="s"; $params[] = trim($body['TenVung']); }

/* MaHo */
if (isset($body['MaHo']) && $body['MaHo']!==""){
    $MaHo = trim($body['MaHo']);
    if (!preg_match('/^[A-Za-z0-9]{1,5}$/',$MaHo)){ http_response_code(400); echo json_encode(["success"=>false,"error"=>"INVALID_MAHO"], JSON_UNESCAPED_UNICODE); exit; }
    $s=$conn->prepare("SELECT 1 FROM honongdan WHERE MaHo=? LIMIT 1");
    $s->bind_param("s",$MaHo); $s->execute(); $s->store_result();
    if ($s->num_rows===0){ $s->close(); http_response_code(400); echo json_encode(["success"=>false,"error"=>"FK_MAHO_NOT_FOUND"], JSON_UNESCAPED_UNICODE); exit; }
    $s->close();
    $fields[]="MaHo=?"; $types.="s"; $params[]=$MaHo;
}

/* DienTich */
if (isset($body['DienTich']) && $body['DienTich']!==""){
    if (!is_numeric($body['DienTich']) || $body['DienTich']<=0){
        http_response_code(400); echo json_encode(["success"=>false,"error"=>"INVALID_DIENTICH"], JSON_UNESCAPED_UNICODE); exit;
    }
    $fields[]="DienTich=?"; $types.="d"; $params[]=(float)$body['DienTich'];
}

/* LoaiDat */
if (isset($body['LoaiDat']) && $body['LoaiDat']!==""){ $fields[]="LoaiDat=?"; $types.="s"; $params[]=trim($body['LoaiDat']); }

/* DieuKienTN */
if (isset($body['DieuKienTN'])){ $fields[]="DieuKienTN=?"; $types.="s"; $params[]=(string)$body['DieuKienTN']; }

/* ViTriToaDo */
if (isset($body['ViTriToaDo'])){ $fields[]="ViTriToaDo=?"; $types.="s"; $params[]=(string)$body['ViTriToaDo']; }

/* MaGiong (allow NULL) */
if (array_key_exists('MaGiong',$body)){
    $MaGiong = $body['MaGiong'];
    if ($MaGiong === "" || $MaGiong === null){
        $fields[]="MaGiong=NULL";
    } else {
        $MaGiong = trim($MaGiong);
        $s=$conn->prepare("SELECT 1 FROM giongthanhlong WHERE MaGiong=? LIMIT 1");
        $s->bind_param("s",$MaGiong); $s->execute(); $s->store_result();
        if ($s->num_rows===0){ $s->close(); http_response_code(400); echo json_encode(["success"=>false,"error"=>"FK_MAGIONG_NOT_FOUND"], JSON_UNESCAPED_UNICODE); exit; }
        $s->close();
        $fields[]="MaGiong=?"; $types.="s"; $params[]=$MaGiong;
    }
}

/* TrangThaiVung */
if (isset($body['TrangThaiVung']) && $body['TrangThaiVung']!==""){
    $t = (int)$body['TrangThaiVung'];
    if (!in_array($t,[0,1,2],true)){ http_response_code(400); echo json_encode(["success"=>false,"error"=>"INVALID_TRANGTHAI"], JSON_UNESCAPED_UNICODE); exit; }
    $fields[]="TrangThaiVung=?"; $types.="i"; $params[]=$t;
}

if (empty($fields)){ http_response_code(400); echo json_encode(["success"=>false,"error"=>"NO_FIELDS","message"=>"Không có trường để cập nhật"], JSON_UNESCAPED_UNICODE); exit; }

$sql="UPDATE vungtrong SET ".implode(", ",$fields)." WHERE MaVung=?";
$types .= "s"; $params[] = $MaVung;

$stmt=$conn->prepare($sql);
$stmt->bind_param($types, ...$params);

if ($stmt->execute()){
    echo json_encode(["success"=>true,"message"=>"Cập nhật vùng trồng thành công","updated"=>$stmt->affected_rows], JSON_UNESCAPED_UNICODE);
} else {
    http_response_code(500);
    echo json_encode(["success"=>false,"error"=>"UPDATE_FAILED","debug"=>$stmt->error], JSON_UNESCAPED_UNICODE);
}
$stmt->close(); $conn->close();
