
import axios from 'axios';

const axiosInstance = axios.create({
  baseURL: process.env.REACT_APP_API_URL || 'http://127.0.0.1:8000/api',
  timeout: +process.env.REACT_APP_API_TIME_OUT || 20000,
});

axiosInstance.interceptors.request.use(
  function (config) {
    config.headers['Content-Type'] = 'application/json';
    return config;
  },
  function (error) {
    return Promise.reject(error);
  }
);

axiosInstance.interceptors.response.use(
  function (response) {
    return response;
  },
  function (error) {
    return Promise.reject(error);
  }
);

export default axiosInstance;
