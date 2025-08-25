$(document).ready(function () {
  // Lấy MaHo từ localStorage (giả sử đã lưu khi đăng nhập)
  const user = JSON.parse(localStorage.getItem("auth_user") || "{}");
  const maHo = user.MaHo;

  if (!maHo) {
    alert("Bạn chưa đăng nhập hoặc không có mã hộ.");
    return;
  }

  $.getJSON("/nhom16/qlvttl/php/api/vungtrong/api_thong_tin_vung_trong.php?MaHo=" + maHo, function (res) {
    if (!res.success) {
      alert("Không thể tải dữ liệu: " + (res.error || "Lỗi không xác định"));
      return;
    }

    const tbody = $("#thietbi-table tbody");
    tbody.empty();

    res.data.forEach(row => {
      const tr = $(`
        <tr>
          <td>${row.MaVung}</td>
          <td>${row.TenVung}</td>
          <td>${row.DienTich}</td>
          <td>${row.LoaiDat}</td>
          <td>${row.DieuKienTN}</td>
          <td>${row.MaThietBi || ''}</td>
          <td>${row.TenThietBi || ''}</td>
          <td>${row.LoaiThietBi || ''}</td>
          <td>${row.SoLuong || ''}</td>
          <td>${row.NgayNhap || ''}</td>
          <td>${row.TrangThaiSuDung || ''}</td>
          <td>${row.NhaSanXuat || ''}</td>
        </tr>
      `);
      tbody.append(tr);
    });
  });
});
