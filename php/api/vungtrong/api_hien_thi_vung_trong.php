<?php
include_once __DIR__ . '/../../connect.php';
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(["success"=>false,"message"=>"Chỉ hỗ trợ GET"], JSON_UNESCAPED_UNICODE);
    exit;
}

/* Nếu có MaVung=? thì trả chi tiết 1 vùng; nếu không -> trả danh sách */
$MaVung = isset($_GET['MaVung']) ? trim($_GET['MaVung']) : null;

if ($MaVung) {
    $sql = "SELECT v.MaVung, v.TenVung, v.MaHo, h.TenChuHo,
                   v.DienTich, v.LoaiDat, v.DieuKienTN, v.ViTriToaDo,
                   v.MaGiong, g.TenGiong, v.TrangThaiVung
            FROM vungtrong v
            JOIN honongdan h ON v.MaHo = h.MaHo
            LEFT JOIN giongthanhlong g ON v.MaGiong = g.MaGiong
            WHERE v.MaVung = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $MaVung);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($row = $res->fetch_assoc()) {
        echo json_encode(["success"=>true,"data"=>$row], JSON_UNESCAPED_UNICODE);
    } else {
        http_response_code(404);
        echo json_encode(["success"=>false,"message"=>"Không tìm thấy vùng trồng"], JSON_UNESCAPED_UNICODE);
    }
    $stmt->close();
    $conn->close();
    exit;
}

$sql = "SELECT v.MaVung, v.TenVung, v.MaHo, h.TenChuHo,
               v.DienTich, v.LoaiDat, v.DieuKienTN, v.ViTriToaDo,
               v.MaGiong, g.TenGiong, v.TrangThaiVung
        FROM vungtrong v
        JOIN honongdan h ON v.MaHo = h.MaHo
        LEFT JOIN giongthanhlong g ON v.MaGiong = g.MaGiong
        ORDER BY v.MaVung";
$res = $conn->query($sql);
$data = [];
while ($row = $res->fetch_assoc()) $data[] = $row;

echo json_encode(["success"=>true,"data"=>$data], JSON_UNESCAPED_UNICODE);
$conn->close();
