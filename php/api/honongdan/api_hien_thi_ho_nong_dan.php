<?php
// Kết nối CSDL
include_once __DIR__ . '/../../connect.php';



// Chỉ xử lý nếu method là GET
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    // Câu lệnh truy vấn tất cả hộ nông dân
    $sql = "SELECT MaHo, TenChuHo, CCCD, 
                   CASE WHEN GioiTinh = 0 THEN 'Nam' ELSE 'Nữ' END AS GioiTinh,
                   NgaySinh, SDT, DiaChi
            FROM honongdan";
    
    $result = $conn->query($sql);

    if ($result && $result->num_rows > 0) {
        $data = [];
        while ($row = $result->fetch_assoc()) {
            $data[] = $row;
        }

        echo json_encode([
            "success" => true,
            "data" => $data
        ], JSON_UNESCAPED_UNICODE);

    } else {
        echo json_encode([
            "success" => false,
            "message" => "Không tìm thấy dữ liệu hộ nông dân"
        ], JSON_UNESCAPED_UNICODE);
    }
} else {
    // Nếu không phải GET
    echo json_encode([
        "success" => false,
        "message" => "Phương thức không được hỗ trợ"
    ], JSON_UNESCAPED_UNICODE);
}

// Đóng kết nối
$conn->close();
?>
