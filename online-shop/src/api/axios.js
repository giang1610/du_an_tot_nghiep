// src/axiosInstance.js
import axios from "axios";

// Lấy base URL và timeout từ biến môi trường .env
const baseURL = process.env.REACT_APP_URI || "http://localhost:8000/api";
const timeout = +process.env.REACT_APP_TIME_OUT || 20000;

// Tạo instance của Axios
const axiosInstance = axios.create({
  baseURL,
  timeout,
  withCredentials: false, // nếu dùng Bearer token thì KHÔNG cần cookie
});

// Interceptor trước khi gửi request
axiosInstance.interceptors.request.use(
  function (config) {
    config.headers["Content-Type"] = "application/json";

    // Lấy token từ localStorage (nếu có) và gắn vào Authorization
    const token = localStorage.getItem("token");
    if (token) {
      config.headers["Authorization"] = `Bearer ${token}`;
    }

    return config;
  },
  function (error) {
    return Promise.reject(error);
  }
);

// Interceptor sau khi nhận response
axiosInstance.interceptors.response.use(
  function (response) {
    // Nếu response có data, trả về data
    if (response.data) {
      return response.data;
    }
    return response;
  },
  function (error) {
    return Promise.reject(error);
  }
);

export default axiosInstance;
