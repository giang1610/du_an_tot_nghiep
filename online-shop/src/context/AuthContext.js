// src/context/AuthContext.js
import React, { createContext, useContext, useState } from 'react';
import { jwtDecode } from 'jwt-decode';

const AuthContext = createContext();

const isTokenValid = (token) => {
  try {
    const { exp } = jwtDecode(token);
    return exp * 1000 > Date.now();
  } catch {
    return false;
  }
};

export const AuthProvider = ({ children }) => {
  const savedUser = localStorage.getItem('user');
  const savedToken = localStorage.getItem('token');

  const [user, setUser] = useState(() =>
    savedUser && savedToken && isTokenValid(savedToken) ? JSON.parse(savedUser) : null
  );
  const [token, setToken] = useState(() =>
    savedToken && isTokenValid(savedToken) ? savedToken : null
  );

  const login = (userData, token) => {
    setUser(userData);
    setToken(token);
    localStorage.setItem('user', JSON.stringify(userData));
    localStorage.setItem('token', token);
  };

  const logout = () => {
    setUser(null);
    setToken(null);
    localStorage.removeItem('user');
    localStorage.removeItem('token');
  };

  const isAuthenticated = !!user && !!token;

  return (
    <AuthContext.Provider value={{ user, token, isAuthenticated, login, logout }}>
      {children}
    </AuthContext.Provider>
  );
};

export const useAuth = () => useContext(AuthContext);
