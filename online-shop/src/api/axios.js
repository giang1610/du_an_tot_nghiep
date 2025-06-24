import axios from 'axios';

// Thiết lập mặc định
const baseURL = process.env.REACT_APP_API_URI || 'http://localhost:8000/api';
const timeout = Number(process.env.REACT_APP_TIME_OUT) || 20000;

// Tạo instance
const axiosInstance = axios.create({
  baseURL,
  timeout,
  withCredentials: true,
  headers: {
    'Content-Type': 'application/json',
  },
});

// Interceptor request
axiosInstance.interceptors.request.use(
  (config) => config,
  (error) => Promise.reject(error)
);

// Interceptor response
axiosInstance.interceptors.response.use(
  (response) => response?.data ?? response,
  (error) => Promise.reject(error)
);

export default axiosInstance;
