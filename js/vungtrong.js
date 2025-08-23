const API = {
  list:   "/qlvttl/php/api/vungtrong/api_hien_thi_vung_trong.php",
  add:    "/qlvttl/php/api/vungtrong/api_them_vung_trong.php",
  update: "/qlvttl/php/api/vungtrong/api_sua_vung_trong.php",
  remove: "/qlvttl/php/api/vungtrong/api_xoa_vung_trong.php"
};

function taiDanhSach() {
  $.getJSON(API.list, res => {
    const ds = res.data || [];
    const tbody = $("#vungtrong-table tbody");
    tbody.empty();
    ds.forEach(row => {
      const tr = $(`
        <tr>
          <td>${row.MaVung}</td>
          <td>${row.TenVung}</td>
          <td>${row.MaHo}</td>
          <td>${row.DienTich}</td>
          <td>${row.LoaiDat}</td>
          <td>${row.DieuKienTN}</td>
          <td>${row.ViTriToaDo}</td>
          <td>${row.MaGiong}</td>
          <td>${row.TrangThaiVung}</td>
        </tr>
      `);
      tr.on("click", () => fillForm(row));
      tbody.append(tr);
    });
  });
}

function fillForm(row) {
  $("#MaVung").val(row.MaVung).prop("readonly", true);
  $("#TenVung").val(row.TenVung);
  $("#MaHo").val(row.MaHo);
  $("#DienTich").val(row.DienTich);
  $("#LoaiDat").val(row.LoaiDat);
  $("#DieuKienTN").val(row.DieuKienTN);
  $("#ViTriToaDo").val(row.ViTriToaDo);
  $("#MaGiong").val(row.MaGiong);
  $("#TrangThaiVung").val(row.TrangThaiVung);
}

function thuThapForm() {
  return {
    MaVung: $("#MaVung").val()?.trim(),
    TenVung: $("#TenVung").val()?.trim(),
    MaHo: $("#MaHo").val()?.trim(),
    DienTich: parseFloat($("#DienTich").val()),
    LoaiDat: $("#LoaiDat").val()?.trim(),
    DieuKienTN: $("#DieuKienTN").val()?.trim(),
    ViTriToaDo: $("#ViTriToaDo").val()?.trim(),
    MaGiong: $("#MaGiong").val()?.trim(),
    TrangThaiVung: parseInt($("#TrangThaiVung").val())
  };
}

function resetForm() {
  $("#vungtrong-form")[0].reset();
  $("#MaVung").prop("readonly", false);
}

function them(data) {
  $.ajax({
    url: API.add,
    method: "POST",
    contentType: "application/json",
    data: JSON.stringify(data),
    success: res => {
      alert(res.message || "Thêm thành công");
      resetForm();
      taiDanhSach();
    },
    error: xhr => {
      console.error("Thêm lỗi:", xhr.responseText);
      alert("Không thể thêm dữ liệu");
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
      alert(res.message || "Cập nhật thành công");
      resetForm();
      taiDanhSach();
    },
    error: xhr => {
      console.error("Sửa lỗi:", xhr.responseText);
      alert("Không thể cập nhật");
    }
  });
}

function xoa(ma) {
  $.ajax({
    url: `${API.remove}?MaVung=${encodeURIComponent(ma)}`,
    method: "DELETE",
    success: res => {
      alert(res.message || "Đã xoá");
      resetForm();
      taiDanhSach();
    },
    error: xhr => {
      console.error("Xoá lỗi:", xhr.responseText);
      alert("Không thể xoá dữ liệu");
    }
  });
}

$("#vungtrong-form").on("submit", function(e) {
  e.preventDefault();
  const data = thuThapForm();
  const isUpdate = $("#MaVung").prop("readonly");
  isUpdate ? sua(data) : them(data);
});

$("#btn-xoa").on("click", function () {
  const ma = $("#MaVung").val();
  if (!ma) return alert("Chọn vùng cần xoá.");
  if (!confirm(`Xác nhận xoá vùng ${ma}?`)) return;
  xoa(ma);
});

$(document).ready(taiDanhSach);
