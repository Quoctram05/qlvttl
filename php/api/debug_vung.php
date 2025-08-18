<?php
// Enable error reporting để debug
error_reporting(E_ALL);
ini_set('display_errors', 0); // Không hiển thị lỗi ra màn hình
ini_set('log_errors', 1);

include __DIR__ . "/connect.php";
header('Content-Type: application/json; charset=utf-8');

// Function để log lỗi
function logError($message, $context = []) {
    error_log("[IoT API] " . $message . " | Context: " . json_encode($context));
}

// Sử dụng đúng schema
$DB = 'qlvttl_dvt';

// Chỉ cho phép POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(["success" => false, "error" => "METHOD_NOT_ALLOWED"], JSON_UNESCAPED_UNICODE);
    exit;
}

// Kiểm tra kết nối database
if (!$conn) {
    logError("Database connection failed", ["error" => mysqli_connect_error()]);
    http_response_code(500);
    echo json_encode(["success" => false, "error" => "DB_CONNECTION_FAILED"], JSON_UNESCAPED_UNICODE);
    exit;
}

// Đọc dữ liệu đầu vào
$raw = file_get_contents("php://input");
$data = json_decode($raw, true);
if (!is_array($data) || empty($data)) {
    $data = $_POST;
}

// Chuẩn hóa key về lowercase
$in = [];
foreach ($data as $k => $v) {
    $in[strtolower($k)] = $v;
}

// Hàm chuẩn hóa mã (loại bỏ NBSP và whitespace, uppercase)
function normalize_code($s) {
    $s = (string)$s;
    $s = str_replace("\xC2\xA0", ' ', $s); // Loại bỏ NBSP
    $s = preg_replace('/\s+/u', '', $s);   // Loại bỏ tất cả whitespace
    return strtoupper(trim($s));
}

// Lấy và chuẩn hóa dữ liệu
$MaIoT = normalize_code($in['maiot'] ?? '');
$LoaiCamBien = trim((string)($in['loaicambien'] ?? ''));
$GiaTriDo = isset($in['giatrido']) ? trim((string)$in['giatrido']) : null;
$DonVi = isset($in['donvi']) ? trim((string)$in['donvi']) : null;
$ThoiGianDo = isset($in['thoigiando']) ? trim((string)$in['thoigiando']) : null;
$TrangThai = isset($in['trangthai']) ? (int)$in['trangthai'] : null;
$MaVung = normalize_code($in['mavung'] ?? '');
$CanhBaoNguyen = isset($in['canhbaonguyen']) ? (int)$in['canhbaonguyen'] : 0;

// Validate dữ liệu bắt buộc
$missing = [];
$required_fields = ['MaIoT', 'LoaiCamBien', 'TrangThai', 'MaVung'];
foreach ($required_fields as $field) {
    if ($$field === '' || $$field === null) {
        $missing[] = $field;
    }
}

if (!empty($missing)) {
    http_response_code(400);
    echo json_encode([
        "success" => false,
        "error" => "MISSING_FIELDS",
        "missing" => $missing,
        "received_data" => $in
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

// Validate format
if (!preg_match('/^[A-Z0-9]{1,10}$/', $MaIoT)) {
    http_response_code(400);
    echo json_encode(["success" => false, "error" => "INVALID_MAIOT_FORMAT"], JSON_UNESCAPED_UNICODE);
    exit;
}

if (!preg_match('/^[A-Z0-9]{1,10}$/', $MaVung)) {
    http_response_code(400);
    echo json_encode(["success" => false, "error" => "INVALID_MAVUNG_FORMAT"], JSON_UNESCAPED_UNICODE);
    exit;
}

if (!in_array($TrangThai, [0, 1, 2], true)) {
    http_response_code(400);
    echo json_encode(["success" => false, "error" => "INVALID_TRANGTHAI"], JSON_UNESCAPED_UNICODE);
    exit;
}

if (!in_array($CanhBaoNguyen, [0, 1], true)) {
    http_response_code(400);
    echo json_encode(["success" => false, "error" => "INVALID_CANHBAO"], JSON_UNESCAPED_UNICODE);
    exit;
}

// Bắt đầu transaction
$conn->autocommit(false);

try {
    // Kiểm tra database hiện tại
    $dbResult = $conn->query("SELECT DATABASE() AS current_db");
    $currentDB = $dbResult ? $dbResult->fetch_assoc()['current_db'] : 'unknown';

    // 1. Kiểm tra MaIoT đã tồn tại chưa
    $stmt = $conn->prepare("SELECT MaIoT FROM `{$DB}`.`thietbiiot` WHERE MaIoT = ? LIMIT 1");
    if (!$stmt) {
        throw new Exception("Prepare failed for duplicate check: " . $conn->error);
    }
    
    $stmt->bind_param("s", $MaIoT);
    $stmt->execute();
    $stmt->store_result();
    
    if ($stmt->num_rows > 0) {
        $stmt->close();
        throw new Exception("DUPLICATE_MAIOT");
    }
    $stmt->close();

    // 2. Kiểm tra MaVung có tồn tại trong bảng vungtrong không (với LOCK)
    $stmt = $conn->prepare("SELECT MaVung, HEX(MaVung) as hex_value, 
                           CHARACTER_SET_NAME, COLLATION_NAME 
                           FROM `{$DB}`.`vungtrong` v
                           JOIN information_schema.COLUMNS c ON c.TABLE_NAME = 'vungtrong' 
                           AND c.COLUMN_NAME = 'MaVung' AND c.TABLE_SCHEMA = ?
                           WHERE v.MaVung = ? 
                           FOR UPDATE");
    if (!$stmt) {
        throw new Exception("Prepare failed for FK check: " . $conn->error);
    }
    
    $stmt->bind_param("ss", $DB, $MaVung);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        // Debug: Lấy tất cả MaVung có sẵn + charset info
        $allVungQuery = $conn->query("
            SELECT v.MaVung, HEX(v.MaVung) as hex_value,
                   c.CHARACTER_SET_NAME, c.COLLATION_NAME,
                   LENGTH(v.MaVung) as length_bytes,
                   CHAR_LENGTH(v.MaVung) as length_chars
            FROM `{$DB}`.`vungtrong` v
            JOIN information_schema.COLUMNS c ON c.TABLE_NAME = 'vungtrong' 
            AND c.COLUMN_NAME = 'MaVung' AND c.TABLE_SCHEMA = '{$DB}'
            ORDER BY v.MaVung
        ");
        $allVung = [];
        if ($allVungQuery) {
            while ($row = $allVungQuery->fetch_assoc()) {
                $allVung[] = $row;
            }
        }
        
        $stmt->close();
        throw new Exception(json_encode([
            "error" => "FK_MAVUNG_NOT_FOUND",
            "searching_for" => $MaVung,
            "searching_hex" => bin2hex($MaVung),
            "searching_length" => strlen($MaVung),
            "available_vung" => $allVung,
            "total_vung_count" => count($allVung)
        ]));
    }
    
    $foundVung = $result->fetch_assoc();
    $stmt->close();
    
    // Log để debug
    logError("Found MaVung", [
        "found" => $foundVung,
        "searching" => $MaVung,
        "match" => ($foundVung['MaVung'] === $MaVung)
    ]);

    // 3. Thực hiện INSERT với debug
    $insertSQL = "INSERT INTO `{$DB}`.`thietbiiot` 
                  (MaIoT, LoaiCamBien, GiaTriDo, DonVi, ThoiGianDo, TrangThai, MaVung, CanhBaoNguyen) 
                  VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
    
    logError("About to INSERT", [
        "sql" => $insertSQL,
        "values" => [
            "MaIoT" => $MaIoT,
            "MaVung" => $MaVung,
            "MaVung_hex" => bin2hex($MaVung),
            "TrangThai" => $TrangThai,
            "CanhBaoNguyen" => $CanhBaoNguyen
        ]
    ]);
    
    $stmt = $conn->prepare($insertSQL);
    if (!$stmt) {
        throw new Exception("Prepare failed for insert: " . $conn->error);
    }
    
    // Debug: Double-check MaVung ngay trước INSERT
    $doubleCheck = $conn->query("SELECT COUNT(*) as count FROM `{$DB}`.`vungtrong` WHERE MaVung = '{$MaVung}'");
    $checkResult = $doubleCheck->fetch_assoc();
    logError("Double check before INSERT", [
        "mavung_exists" => $checkResult['count'],
        "mavung_value" => $MaVung,
        "found_vung_earlier" => $foundVung
    ]);
    
    $stmt->bind_param("ssssiisi", $MaIoT, $LoaiCamBien, $GiaTriDo, $DonVi, $ThoiGianDo, $TrangThai, $MaVung, $CanhBaoNguyen);
    
    if (!$stmt->execute()) {
        $mysqlError = $stmt->error;
        $stmt->close();
        
        // Log MySQL error chi tiết
        logError("MySQL INSERT failed", [
            "mysql_error" => $mysqlError,
            "errno" => $stmt->errno ?? $conn->errno,
            "values_bound" => [
                "MaIoT" => $MaIoT,
                "LoaiCamBien" => $LoaiCamBien,
                "MaVung" => $MaVung,
                "TrangThai" => $TrangThai
            ]
        ]);
        
        throw new Exception("INSERT_FAILED: " . $mysqlError);
    }
    
    $insertedId = $conn->insert_id;
    $stmt->close();

    // Commit transaction
    $conn->commit();
    
    // Trả về kết quả thành công
    echo json_encode([
        "success" => true,
        "message" => "Thêm thiết bị IoT thành công",
        "data" => [
            "id" => $insertedId,
            "MaIoT" => $MaIoT,
            "LoaiCamBien" => $LoaiCamBien,
            "GiaTriDo" => $GiaTriDo,
            "DonVi" => $DonVi,
            "ThoiGianDo" => $ThoiGianDo,
            "TrangThai" => $TrangThai,
            "MaVung" => $MaVung,
            "CanhBaoNguyen" => $CanhBaoNguyen
        ],
        "db_info" => [
            "database" => $currentDB,
            "found_vung" => $foundVung
        ]
    ], JSON_UNESCAPED_UNICODE);

} catch (Exception $e) {
    // Rollback nếu có lỗi
    $conn->rollback();
    
    $errorMessage = $e->getMessage();
    logError("Exception caught", [
        "error" => $errorMessage,
        "file" => $e->getFile(),
        "line" => $e->getLine(),
        "trace" => $e->getTraceAsString()
    ]);
    
    // Kiểm tra nếu là JSON error message
    $jsonError = json_decode($errorMessage, true);
    if (json_last_error() === JSON_ERROR_NONE) {
        http_response_code(400);
        echo json_encode(array_merge([
            "success" => false,
            "db_info" => ["database" => $currentDB ?? 'unknown']
        ], $jsonError), JSON_UNESCAPED_UNICODE);
    } else {
        // Lỗi thông thường
        if (strpos($errorMessage, "DUPLICATE_MAIOT") !== false) {
            http_response_code(409);
        } else {
            http_response_code(500);
        }
        
        echo json_encode([
            "success" => false,
            "error" => $errorMessage,
            "debug_info" => [
                "database" => $currentDB ?? 'unknown',
                "input_mavung" => $MaVung ?? 'not_set',
                "input_mavung_hex" => isset($MaVung) ? bin2hex($MaVung) : 'not_set',
                "input_maiot" => $MaIoT ?? 'not_set',
                "php_version" => PHP_VERSION,
                "mysql_version" => $conn->server_info ?? 'unknown'
            ]
        ], JSON_UNESCAPED_UNICODE);
    }
} catch (Throwable $t) {
    // Catch cả Error và Exception
    logError("Fatal error", [
        "error" => $t->getMessage(),
        "file" => $t->getFile(),
        "line" => $t->getLine()
    ]);
    
    http_response_code(500);
    echo json_encode([
        "success" => false,
        "error" => "FATAL_ERROR",
        "message" => $t->getMessage()
    ], JSON_UNESCAPED_UNICODE);
} finally {
    // Đảm bảo đóng kết nối
    try {
        $conn->autocommit(true);
        if (isset($stmt) && $stmt) {
            $stmt->close();
        }
        $conn->close();
    } catch (Exception $cleanup_error) {
        logError("Cleanup error", ["error" => $cleanup_error->getMessage()]);
    }
}
?>