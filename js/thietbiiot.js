const API = {
  list:   "/qlvttl/php/api/IoT/api_hien_thi_IoT.php",
  add:    "/qlvttl/php/api/IoT/api_them_IoT.php",
  update: "/qlvttl/php/api/IoT/api_sua_IoT.php",
  remove: "/qlvttl/php/api/IoT/api_xoa_IoT.php"
};

function taiDanhSach() {
  $.getJSON(API.list, res => {
    const tbody = $("#iot-table tbody");
    tbody.empty();
    res.data.forEach(row => {
      const tr = $(`
        <tr>
          <td>${row.MaIoT}</td>
          <td>${row.LoaiCamBien}</td>
          <td>${row.GiaTriDo}</td>
          <td>${row.DonVi}</td>
          <td>${row.ThoiGianDo}</td>
          <td>${row.TrangThai}</td>
          <td>${row.MaVung}</td>
          <td>${row.CanhBaoNguyen}</td>
        </tr>
      `);
      tr.click(() => fillForm(row));
      tbody.append(tr);
    });
  });
}

function fillForm(row) {
  $("#MaIoT").val(row.MaIoT).prop("readonly", true);
  $("#LoaiCamBien").val(row.LoaiCamBien);
  $("#GiaTriDo").val(row.GiaTriDo);
  $("#DonVi").val(row.DonVi);
  $("#ThoiGianDo").val(row.ThoiGianDo);
  $("#TrangThai").val(row.TrangThai);
  $("#MaVung").val(row.MaVung);
  $("#CanhBaoNguyen").val(row.CanhBaoNguyen);
}

function thuThapForm() {
  return {
    MaIoT:          $("#MaIoT").val()?.trim(),
    LoaiCamBien:    $("#LoaiCamBien").val()?.trim(),
    GiaTriDo:       parseFloat($("#GiaTriDo").val()),
    DonVi:          $("#DonVi").val()?.trim(),
    ThoiGianDo:     $("#ThoiGianDo").val(),
    TrangThai:      parseInt($("#TrangThai").val()),
    MaVung:         $("#MaVung").val()?.trim(),
    CanhBaoNguyen:  parseInt($("#CanhBaoNguyen").val())
  };
}

function resetForm() {
  $("#iot-form")[0].reset();
  $("#MaIoT").prop("readonly", false);
}

function them(data) {
  $.ajax({
    url: API.add,
    method: "POST",
    contentType: "application/json",
    data: JSON.stringify(data),
    success: res => {
      alert(res.message || "Đã thêm thiết bị IoT");
      resetForm();
      taiDanhSach();
    },
    error: xhr => {
      console.error("Lỗi thêm:", xhr.responseText);
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
      alert(res.message || "Đã cập nhật thiết bị");
      resetForm();
      taiDanhSach();
    },
    error: xhr => {
      console.error("Lỗi sửa:", xhr.responseText);
      alert("Không thể cập nhật");
    }
  });
}

function xoa(ma) {
  $.ajax({
    url: `${API.remove}?MaIoT=${encodeURIComponent(ma)}`,
    method: "DELETE",
    success: res => {
      alert(res.message || "Đã xoá thiết bị");
      resetForm();
      taiDanhSach();
    },
    error: xhr => {
      console.error("Lỗi xoá:", xhr.responseText);
      alert("Không thể xoá dữ liệu");
    }
  });
}

$("#iot-form").on("submit", function(e) {
  e.preventDefault();
  const data = thuThapForm();
  const isUpdate = $("#MaIoT").prop("readonly");
  isUpdate ? sua(data) : them(data);
});

$("#btn-xoa").click(() => {
  const ma = $("#MaIoT").val();
  if (!ma) return alert("Chọn thiết bị cần xoá.");
  if (!confirm("Xoá thiết bị " + ma + "?")) return;
  xoa(ma);
});

$(document).ready(taiDanhSach);
