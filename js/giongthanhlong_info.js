$(document).ready(function () {
  const MaHo = localStorage.getItem("auth_user")
    ? JSON.parse(localStorage.getItem("auth_user")).MaHo
    : null;

  if (!MaHo) {
    alert("Không tìm thấy mã hộ! Vui lòng đăng nhập.");
    return;
  }

  $.getJSON(`/nhom16/qlvttl/php/api/giongthanhlong/api_thong_tin_giong.php?MaHo=${MaHo}`, function (res) {
    if (!res.success || !res.data.length) {
      alert("Không có dữ liệu giống thanh long.");
      return;
    }

    const tbody = $("#giong-table tbody");
    tbody.empty();

    res.data.forEach(g => {
      const row = `
        <tr>
          <td>${g.MaGiong}</td>
          <td>${g.TenGiong}</td>
          <td>${g.NguonGoc}</td>
          <td>${g.DacDiem}</td>
          <td>${g.NgayApDung}</td>
        </tr>
      `;
      tbody.append(row);
    });
  }).fail(function (xhr) {
    console.error("Lỗi tải dữ liệu:", xhr.responseText);
    alert("Lỗi khi tải danh sách giống!");
  });
});
