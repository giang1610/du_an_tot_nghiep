import React, { useState } from "react";

import { Link } from "react-router-dom";
import { sendResetLink } from "../services/auth";


const ForgotPassword = () => {
  const [email, setEmail] = useState("");
  const [message, setMessage] = useState("");
  const [error, setError] = useState("");

  const handleSubmit = async (e) => {
    e.preventDefault();
    setError("");
    setMessage("");
    try {
      await sendResetLink(email);
      setMessage("Link đặt lại mật khẩu đã được gửi vào email.");
    } catch (err) {
      setError(err.response?.data?.message || "Gửi link thất bại");
    }
  };

  return (
    <div className="container mt-5" style={{ maxWidth: 400 }}>
      <h3 className="mb-4">Quên mật khẩu</h3>
      {message && <div className="alert alert-success">{message}</div>}
      {error && <div className="alert alert-danger">{error}</div>}
      <form onSubmit={handleSubmit}>
        <div className="mb-3">
          <label>Email đăng ký</label>
          <input
            type="email"
            className="form-control"
            value={email}
            onChange={e => setEmail(e.target.value)}
            autoFocus
          />
        </div>
        <button className="btn btn-warning w-100" type="submit">Gửi link đặt lại mật khẩu</button>
      </form>
      <div className="mt-3 text-center">
        <Link to="/login">Quay lại đăng nhập</Link>
      </div>
    </div>
  );
};

export default ForgotPassword;