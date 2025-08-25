$("#btn-logout").click(function () {
  if (confirm("Bạn có chắc chắn muốn đăng xuất không?")) {
    localStorage.removeItem("auth_user");
    localStorage.removeItem("auth_token");
    window.location.href = "/nhom16/qlvttl/index.html"; // hoặc trang login của bạn
  }
});
