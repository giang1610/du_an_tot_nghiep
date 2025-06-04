import React, { useState } from "react";
import { useNavigate, Link } from "react-router-dom";
import { register } from "../services/auth";

const Register = () => {
  const navigate = useNavigate();

  const [name, setName] = useState("");
  const [email, setEmail] = useState("");
  const [password, setPassword] = useState("");
  const [confirmPassword, setConfirmPassword] = useState("");
  const [error, setError] = useState("");

  const handleSubmit = async (e) => {
    e.preventDefault();
    setError("");
    if (password !== confirmPassword) {
      setError("Mật khẩu và xác nhận mật khẩu không khớp");
      return;
    }
    try {
      await register({ name, email, password });
      navigate("/login");
    } catch (err) {
      setError(err.response?.data?.message || "Đăng ký thất bại");
    }
  };

  return (
    <div className="container mt-5" style={{ maxWidth: 400 }}>
      <h3 className="mb-4">Đăng ký</h3>
      {error && <div className="alert alert-danger">{error}</div>}
      <form onSubmit={handleSubmit}>
        <div className="mb-3">
          <label>Họ và tên</label>
          <input
            type="text"
            className="form-control"
            value={name}
            onChange={e => setName(e.target.value)}
            autoFocus
          />
        </div>
        <div className="mb-3">
          <label>Email</label>
          <input
            type="email"
            className="form-control"
            value={email}
            onChange={e => setEmail(e.target.value)}
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
        </div>
        <div className="mb-3">
          <label>Xác nhận mật khẩu</label>
          <input
            type="password"
            className="form-control"
            value={confirmPassword}
            onChange={e => setConfirmPassword(e.target.value)}
          />
        </div>
        <button className="btn btn-success w-100" type="submit">Đăng ký</button>
      </form>
      <div className="mt-3 text-center">
        <>Đã có tài khoản? <Link to="/login">Đăng nhập</Link></>
      </div>
    </div>
  );
};

export default Register;
