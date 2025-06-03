import React, { useState } from "react";

import { useAuth } from "../hooks/useAuth";
import { useNavigate, Link } from "react-router-dom";
import { login } from "../services/auth";




const Login = () => {
  const { loginUser } = useAuth();
  const navigate = useNavigate();

  const [email, setEmail] = useState("");
  const [password, setPassword] = useState("");
  const [error, setError] = useState("");

  const handleSubmit = async (e) => {
    e.preventDefault();
    setError("");
    try {
      const userData = await login({ email, password });
      loginUser(userData);
      navigate("/");
    } catch (err) {
      setError(err.response?.data?.message || "Đăng nhập thất bại");
    }
  };

  return (
    <div className="container mt-5" style={{ maxWidth: 400 }}>
      <h3 className="mb-4">Đăng nhập</h3>
      {error && <div className="alert alert-danger">{error}</div>}
      <form onSubmit={handleSubmit}>
        <div className="mb-3">
          <label>Email</label>
          <input
            type="email"
            className="form-control"
            value={email}
            onChange={e => setEmail(e.target.value)}
            autoFocus
          />
        </div>
        <div className="mb-3">
          <label>Mật khẩu</label>
          <input
            type="password"
            className="form-control"
            value={password}
            onChange={e => setPassword(e.target.value)}
          />
        <Link to="/forgot-password">Quên mật khẩu?</Link>
        </div>
        <button className="btn btn-primary w-100" type="submit">Đăng nhập</button>
      </form>
      <div className="mt-3 text-center">
       <>Bạn chưa có tài khoản?<Link to="/register">Đăng ký</Link></>
      </div>
    </div>
  );
};

export default Login;
