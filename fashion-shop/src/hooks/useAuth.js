import { useEffect, useState } from 'react';

export default function useAuth() {
  const [user, setUser] = useState(() => {
    const name = localStorage.getItem('user_name');
    const email = localStorage.getItem('user_email');
    const token = localStorage.getItem('token');
    return token ? { name, email, token } : null;
  });

  const login = (userData) => {
    localStorage.setItem('token', userData.token);
    localStorage.setItem('user_name', userData.name);
    localStorage.setItem('user_email', userData.email);
    setUser({
      name: userData.name,
      email: userData.email,
      token: userData.token
    });
  };

  const logout = () => {
    localStorage.removeItem('token');
    localStorage.removeItem('user_name');
    localStorage.removeItem('user_email');
    setUser(null);
  };

  const isAuthenticated = !!user;

  useEffect(() => {
    const token = localStorage.getItem('token');
    if (!token) setUser(null);
  }, []);

  return {
    user,
    isAuthenticated,
    login,
    logout
  };
}
