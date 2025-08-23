const API = {
  list:   "/qlvttl/php/api/nhatkycanhtac/api_hien_thi.php",
  add:    "/qlvttl/php/api/nhatkycanhtac/api_them.php",
  update: "/qlvttl/php/api/nhatkycanhtac/api_sua.php",
  remove: "/qlvttl/php/api/nhatkycanhtac/api_xoa.php"
};

function taiDanhSach() {
  $.getJSON(API.list, res => {
    const tbody = $("#nhatky-table tbody");
    tbody.empty();
    res.data.forEach(row => {
      const tr = $(`
        <tr>
          <td>${row.MaNhatKy}</td>
          <td>${row.MaVung}</td>
          <td>${row.MaHo}</td>
          <td>${row.NgayThucHien}</td>
          <td>${row.HoatDong}</td>
          <td>${row.GhiChu || ''}</td>
        </tr>
      `);
      tr.click(() => fillForm(row));
      tbody.append(tr);
    });
  });
}

function fillForm(row) {
  $("#MaNhatKy").val(row.MaNhatKy).prop("readonly", true);
  $("#MaVung").val(row.MaVung);
  $("#MaHo").val(row.MaHo);
  $("#NgayThucHien").val(row.NgayThucHien);
  $("#HoatDong").val(row.HoatDong);
  $("#GhiChu").val(row.GhiChu);
}

function thuThapForm() {
  return {
    MaNhatKy: $("#MaNhatKy").val().trim(),
    MaVung: $("#MaVung").val().trim(),
    MaHo: $("#MaHo").val().trim(),
    NgayThucHien: $("#NgayThucHien").val(),
    HoatDong: $("#HoatDong").val().trim(),
    GhiChu: $("#GhiChu").val().trim()
  };
}

function resetForm() {
  $("#nhatky-form")[0].reset();
  $("#MaNhatKy").prop("readonly", false);
}

function them(data) {
  $.ajax({
    url: API.add,
    method: "POST",
    contentType: "application/json",
    data: JSON.stringify(data),
    success: res => {
      alert(res.message || "Đã thêm nhật ký");
      resetForm();
      taiDanhSach();
    },
    error: xhr => {
      alert("Không thể thêm dữ liệu");
      console.error("Thêm lỗi:", xhr.responseText);
    }
  });
}

function sua(data) {
  $.ajax({
    url: API.update,
    method: "PUT",
    contentType: "application/json",
    data: JSON.stringify(data),
    success: res => {
      alert(res.message || "Đã cập nhật nhật ký");
      resetForm();
      taiDanhSach();
    },
    error: xhr => {
      alert("Không thể cập nhật");
      console.error("Sửa lỗi:", xhr.responseText);
    }
  });
}

function xoa(ma) {
  $.ajax({
    url: `${API.remove}?MaNhatKy=${encodeURIComponent(ma)}`,
    method: "DELETE",
    success: res => {
      alert(res.message || "Đã xoá");
      resetForm();
      taiDanhSach();
    },
    error: xhr => {
      alert("Không thể xoá");
      console.error("Xoá lỗi:", xhr.responseText);
    }
  });
}

$("#nhatky-form").on("submit", function(e) {
  e.preventDefault();
  const data = thuThapForm();
  const isUpdate = $("#MaNhatKy").prop("readonly");
  isUpdate ? sua(data) : them(data);
});

$("#btn-xoa").click(() => {
  const ma = $("#MaNhatKy").val();
  if (!ma) return alert("Chọn nhật ký cần xoá");
  if (!confirm("Xác nhận xoá nhật ký " + ma + "?")) return;
  xoa(ma);
});

$(document).ready(taiDanhSach);
