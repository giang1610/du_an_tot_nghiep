import React, { useState } from "react";
import { useParams, useNavigate } from "react-router-dom";

const ChangePassword = () => {
  const { token } = useParams();
  const navigate = useNavigate();

  const [password, setPassword] = useState("");
  const [confirmPassword, setConfirmPassword] = useState("");
  const [error, setError] = useState("");
  const [message, setMessage] = useState("");

  const handleSubmit = async (e) => {
    e.preventDefault();
    setError("");
    setMessage("");
    if (password !== confirmPassword) {
      setError("Mật khẩu và xác nhận mật khẩu không khớp");
      return;
    }
    try {
      await ChangePassword(token, password);
      setMessage("Đổi mật khẩu thành công! Đang chuyển về đăng nhập...");
      setTimeout(() => navigate("/login"), 3000);
    } catch (err) {
      setError(err.response?.data?.message || "Đổi mật khẩu thất bại");
    }
  };

  return (
    <div className="container mt-5" style={{ maxWidth: 400 }}>
      <h3 className="mb-4">Đổi mật khẩu</h3>
      {message && <div className="alert alert-success">{message}</div>}
      {error && <div className="alert alert-danger">{error}</div>}
      <form onSubmit={handleSubmit}>
        <div className="mb-3">
          <label>Mật khẩu mới</label>
          <input
            type="password"
            className="form-control"
            value={password}
            onChange={e => setPassword(e.target.value)}
            autoFocus
          />
        </div>
        <div className="mb-3">
          <label>Xác nhận mật khẩu mới</label>
          <input
            type="password"
            className="form-control"
            value={confirmPassword}
            onChange={e => setConfirmPassword(e.target.value)}
          />
        </div>
        <button className="btn btn-primary w-100" type="submit">Đổi mật khẩu</button>
      </form>
    </div>
  );
};

export default ChangePassword;
