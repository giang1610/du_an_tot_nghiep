import React, { createContext, useContext, useState } from "react";
import { login as loginApi } from "../services/auth";

export const AuthContext = createContext();  // <-- thêm export ở đây

export const AuthProvider = ({ children }) => {
  const [user, setUser] = useState(null);
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState(null);

  const loginUser = async (credentials) => {
    setLoading(true);
    setError(null);
    try {
      const response = await loginApi(credentials);
      setUser(response.data.user);
      setLoading(false);
      return response.data;
    } catch (err) {
      setError(err.response?.data?.message || "Đăng nhập thất bại");
      setLoading(false);
      throw err;
    }
  };

  const logoutUser = () => {
    setUser(null);
    // Xóa token nếu có
  };

  return (
    <AuthContext.Provider
      value={{ user, loginUser, logoutUser, loading, error }}
    >
      {children}
    </AuthContext.Provider>
  );
};

export const useAuth = () => {
  return useContext(AuthContext);
};
