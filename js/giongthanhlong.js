const API = {
  list:   "/qlvttl/php/api/giongthanhlong/api_hien_thi_giong_thanh_long.php",
  add:    "/qlvttl/php/api/giongthanhlong/api_them_giong_thanh_long.php",
  update: "/qlvttl/php/api/giongthanhlong/api_sua_giong_thanh_long.php",
  remove: "/qlvttl/php/api/giongthanhlong/api_xoa_giong_thanh_long.php"
};

// ==== 1. HIỂN THỊ DANH SÁCH GIỐNG ====
function taiDanhSach() {
  $.getJSON(API.list, res => {
    const ds = res.data || [];
    const tbody = $("#giong-table tbody");
    tbody.empty();
    ds.forEach(row => {
      const tr = $(`
        <tr>
          <td>${row.MaGiong}</td>
          <td>${row.TenGiong}</td>
          <td>${row.NguonGoc}</td>
          <td>${row.DacDiem}</td>
          <td>${row.NgayApDung}</td>
        </tr>
      `);
      tr.on("click", () => fillForm(row));
      tbody.append(tr);
    });
  });
}

// ==== 2. ĐỔ DỮ LIỆU LÊN FORM ====
function fillForm(row) {
  $("#MaGiong").val(row.MaGiong).prop("readonly", true);
  $("#TenGiong").val(row.TenGiong);
  $("#NguonGoc").val(row.NguonGoc);
  $("#DacDiem").val(row.DacDiem);
  $("#NgayApDung").val(row.NgayApDung);
}

// ==== 3. THU THẬP DỮ LIỆU TỪ FORM ====
function thuThapForm() {
  return {
    MaGiong:     $("#MaGiong").val()?.trim(),
    TenGiong:    $("#TenGiong").val()?.trim(),
    NguonGoc:    $("#NguonGoc").val()?.trim(),
    DacDiem:     $("#DacDiem").val()?.trim(),
    NgayApDung:  $("#NgayApDung").val()?.trim()
  };
}

// ==== 4. RESET FORM ====
function resetForm() {
  $("#giong-form")[0].reset();
  $("#MaGiong").prop("readonly", false);
}

// ==== 5. THÊM GIỐNG ====
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
      console.error("Lỗi thêm:", xhr.responseText);
      alert("Không thể thêm dữ liệu");
    }
  });
}

// ==== 6. SỬA GIỐNG ====
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
      console.error("Lỗi sửa:", xhr.responseText);
      alert("Không thể cập nhật");
    }
  });
}

// ==== 7. XOÁ GIỐNG ====
function xoa(ma) {
  $.ajax({
    url: `${API.remove}?MaGiong=${encodeURIComponent(ma)}`,
    method: "DELETE",
    success: res => {
      alert(res.message || "Đã xoá");
      resetForm();
      taiDanhSach();
    },
    error: xhr => {
      console.error("Lỗi xoá:", xhr.responseText);
      alert("Không thể xoá dữ liệu");
    }
  });
}

// ==== 8. SỰ KIỆN NÚT ====
$("#giong-form").on("submit", function(e) {
  e.preventDefault();
  const data = thuThapForm();
  const isUpdate = $("#MaGiong").prop("readonly");
  isUpdate ? sua(data) : them(data);
});

$("#btn-xoa").on("click", function () {
  const ma = $("#MaGiong").val();
  if (!ma) return alert("Chọn giống cần xoá.");
  if (!confirm(`Xác nhận xoá giống ${ma}?`)) return;
  xoa(ma);
});

// ==== KHỞI CHẠY ====
$(document).ready(taiDanhSach);
