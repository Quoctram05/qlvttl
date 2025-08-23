<?php
// === KẾT NỐI DATABASE ===
header("Content-Type: application/json; charset=utf-8");
require_once __DIR__ . "/../../connect.php";

// === NHẬN DỮ LIỆU TỪ BODY JSON ===
$in = json_decode(file_get_contents("php://input"), true) ?: [];

$MaNhatKy     = trim($in["MaNhatKy"]     ?? "");
$MaVung       = trim($in["MaVung"]       ?? "");
$MaHo         = trim($in["MaHo"]         ?? "");
$NgayThucHien = trim($in["NgayThucHien"] ?? "");
$HoatDong     = trim($in["HoatDong"]     ?? "");
$GhiChu       = trim($in["GhiChu"]       ?? "");

// === KIỂM TRA DỮ LIỆU BẮT BUỘC ===
if ($MaNhatKy === "" || $MaVung === "" || $MaHo === "" || $NgayThucHien === "" || $HoatDong === "") {
  echo json_encode([
    "success" => false,
    "error" => "MISSING_FIELDS",
    "message" => "Vui lòng nhập đầy đủ các trường bắt buộc"
  ]);
  exit;
}

// === CHUẨN BỊ CÂU LỆNH SQL ===
$stmt = $conn->prepare("
  INSERT INTO nhatkycanhtac (MaNhatKy, MaVung, MaHo, NgayThucHien, HoatDong, GhiChu)
  VALUES (?, ?, ?, ?, ?, ?)
");
$stmt->bind_param("ssssss", $MaNhatKy, $MaVung, $MaHo, $NgayThucHien, $HoatDong, $GhiChu);

// === THỰC THI & PHẢN HỒI ===
if ($stmt->execute()) {
  echo json_encode([
    "success" => true,
    "message" => "✅ Đã thêm nhật ký canh tác",
    "inserted_id" => $MaNhatKy
  ]);
} else {
  echo json_encode([
    "success" => false,
    "error" => "INSERT_FAILED",
    "message" => "❌ Không thể thêm dữ liệu",
    "debug" => $stmt->error
  ]);
}

$stmt->close();
$conn->close();
