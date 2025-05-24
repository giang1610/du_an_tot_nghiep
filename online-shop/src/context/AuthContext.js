// src/context/AuthContext.js
import React, { createContext, useContext, useState, useEffect } from 'react';

// Tạo Context để quản lý auth
const AuthContext = createContext();

// Provider chứa state và các hàm login/logout
export const AuthProvider = ({ children }) => {
  // Khởi tạo user và token từ localStorage nếu có
  const [user, setUser] = useState(() => {
    const savedUser = localStorage.getItem('user');
    return savedUser ? JSON.parse(savedUser) : null;
  });

  const [token, setToken] = useState(() => localStorage.getItem('token') || null);

  // Hàm gọi khi đăng nhập thành công
  const login = (userData, token) => {
    setUser(userData);
    setToken(token);
    localStorage.setItem('user', JSON.stringify(userData));
    localStorage.setItem('token', token);
  };

  // Hàm gọi khi logout
  const logout = () => {
    setUser(null);
    setToken(null);
    localStorage.removeItem('user');
    localStorage.removeItem('token');
  };

  // Đồng bộ lại khi reload trang
  useEffect(() => {
    const savedUser = localStorage.getItem('user');
    const savedToken = localStorage.getItem('token');
    if (savedUser && savedToken) {
      setUser(JSON.parse(savedUser));
      setToken(savedToken);
    }
  }, []);

  return (
    <AuthContext.Provider value={{ user, token, login, logout }}>
      {children}
    </AuthContext.Provider>
  );
};

// Hook tiện lợi để sử dụng context trong component khác
export const useAuth = () => useContext(AuthContext);
