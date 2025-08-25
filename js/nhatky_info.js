$(document).ready(function () {
  const MaHo = getLoggedInMaHo(); // Hàm tự định nghĩa tuỳ bạn lưu ở đâu

  if (!MaHo) {
    alert("Không tìm thấy thông tin người dùng. Vui lòng đăng nhập.");
    return;
  }

  $.getJSON(`/nhom16/qlvttl/php/api/nhatkycanhtac/api_thong_tin_nhat_ky.php?MaHo=${MaHo}`, function (res) {
    if (!res.success) {
      alert("Lỗi tải dữ liệu: " + res.error);
      return;
    }

    const tbody = $("#vattu-table tbody");
    tbody.empty();

    res.data.forEach(item => {
      const tr = `
        <tr>
          <td>${item.MaNhatKy}</td>
          <td>${item.NgayThucHien}</td>
          <td>${item.HoatDong}</td>
          <td>${item.TenVatTu}</td>
          <td>${item.LoaiVatTu}</td>
          <td>${item.SL_SuDung}</td>
          <td>${item.TenNCC || "N/A"}</td>
          <td>${item.GiaBan || "N/A"}</td>
          <td>${item.SL_NCC || "N/A"}</td>
          <td>${item.NgayNhap || ""}</td>
        </tr>
      `;
      tbody.append(tr);
    });
  });

  function getLoggedInMaHo() {
    try {
      const user = JSON.parse(localStorage.getItem("auth_user"));
      return user?.MaHo || "";
    } catch (e) {
      return "";
    }
  }
});
