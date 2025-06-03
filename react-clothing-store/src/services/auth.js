import API from "./api";  // import từ file api.js cùng folder

// Hàm đăng nhập
export const login = (data) => API.post("/login", data);

// Hàm đăng ký
export const register = (data) => API.post("/register", data);

// Hàm gửi link reset mật khẩu
export const sendResetLink = (email) => API.post("/forgot-password", { email });

// Hàm đổi mật khẩu
export const changePassword = (token, data) =>
  API.post(`/reset-password/${token}`, data);
