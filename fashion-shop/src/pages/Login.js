import { useState, useCallback, useEffect } from 'react';
import { useNavigate, Link, useSearchParams } from 'react-router-dom';
import { Form, Button, Alert, InputGroup, Spinner } from 'react-bootstrap';
import { useAuth } from '../context/AuthContext';
import { Eye, EyeSlash, BoxArrowInRight } from 'react-bootstrap-icons';

// 1. IMPORT HÌNH ẢNH CỦA BẠN
// Bỏ comment dòng dưới và thay đổi đường dẫn tới file ảnh của bạn
// import loginImage from 'https://24hstore.vn/upload_images/images/hinh-nen-iphone-doc-dep/hinh-nen-iphone-doc-dao.jpg'; 

// Import file CSS
import '../css/login.css';

export default function Login() {
  const [form, setForm] = useState({ email: '', password: '' });
  const [error, setError] = useState('');
  const [verifiedMsg, setVerifiedMsg] = useState('');
  const [loading, setLoading] = useState(false);
  const [showPassword, setShowPassword] = useState(false);
  const navigate = useNavigate();
  const { login } = useAuth();
  const [searchParams] = useSearchParams();

  // Logic xử lý form không thay đổi
  useEffect(() => {
    const email = searchParams.get('email');
    const message = searchParams.get('message');
    if (email) setForm(prev => ({ ...prev, email }));
    if (message === 'email_verified') setVerifiedMsg(' Email đã được xác minh. Vui lòng đăng nhập.');
    if (message === 'already_verified') setVerifiedMsg('ℹEmail này đã được xác minh từ trước.');
  }, [searchParams]);

  const validateEmail = (email) => /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
  const handleChange = useCallback((e) => setForm(f => ({ ...f, [e.target.name]: e.target.value })), []);

  const handleSubmit = useCallback(async (e) => {
    e.preventDefault();
    setError('');
    if (!validateEmail(form.email) || !form.password) {
      setError('Vui lòng nhập email và mật khẩu hợp lệ.');
      return;
    }
    setLoading(true);
    try {
      const res = await fetch(`${process.env.REACT_APP_API_URL}/login`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(form),
        credentials: 'include',
      });
      const data = await res.json();
      if (res.ok) {
        login(data.token, data.user);
        navigate('/');
      } else {
        setError(data.message || 'Lỗi đăng nhập');
      }
    } catch {
      setError('Không thể kết nối đến máy chủ.');
    } finally {
      setLoading(false);
    }
  }, [form, login, navigate]);

  return (
    <div className="login-page-wrapper">
      <div className="login-container">
        {/* --- PHẦN HÌNH ẢNH (BÊN TRÁI) --- */}
        <div className="login-image-section">
          <img src='https://24hstore.vn/upload_images/images/hinh-nen-iphone-doc-dep/hinh-nen-iphone-doc-dao.jpg' ></img>
          <div className="image-placeholder"></div>
        </div>

        {/* --- PHẦN FORM (BÊN PHẢI) --- */}
        <div className="login-form-section">
          <div className="logo-container">
            <h2 className="logo-text ">Đăng nhập</h2>
          </div>
          {verifiedMsg && <Alert variant="success" className="custom-alert">{verifiedMsg}</Alert>}
          {error && <Alert variant="danger" className="custom-alert">{error}</Alert>}

          <Form onSubmit={handleSubmit} noValidate>
            <Form.Group className="mb-3" controlId="loginEmail">
              <Form.Label>Email address</Form.Label>
              <Form.Control
                type="email"
                name="email"
                value={form.email}
                onChange={handleChange}
                required
                autoComplete="username"
              />
            </Form.Group>

            <Form.Group className="mb-4" controlId="loginPassword">
              <Form.Label>Password</Form.Label>
              <InputGroup>
                <Form.Control
                  type={showPassword ? 'text' : 'password'}
                  name="password"
                  value={form.password}
                  onChange={handleChange}
                  required
                  autoComplete="current-password"
                />
                <Button variant="outline-secondary" onClick={() => setShowPassword(v => !v)}>
                  {showPassword ? <EyeSlash /> : <Eye />}
                </Button>
              </InputGroup>
            </Form.Group>

            <Button type="submit" className="w-100 login-button" disabled={loading}>
              {loading ? (
                <>
                  <Spinner as="span" animation="border" size="sm" role="status" aria-hidden="true" />
                  <span className="ms-2">Dang sử lý...</span>
                </>
              ) : (
                'LOGIN'
              )}
            </Button>
          </Form>

          <div className="login-links">
            <Link to="/forgot-password">Forgot password?</Link>
            <p>Don't have an account? <Link to="/register" className="link-highlight">Register here</Link></p>
            <div className="terms-policy">
              <Link to="/terms">Terms of use</Link>
              <span>·</span>
              <Link to="/privacy">Privacy policy</Link>
            </div>
          </div>
        </div>
      </div>
    </div>
  );
}