  import { useState, useCallback, useEffect } from 'react';
  import { useNavigate, Link, useSearchParams } from 'react-router-dom';
  import { Form, Button, Container, Alert, InputGroup } from 'react-bootstrap';
  import { useAuth } from '../context/AuthContext';
  import { Eye, EyeSlash } from 'react-bootstrap-icons';

  export default function Login() {
    const [form, setForm] = useState({ email: '', password: '' });
    const [error, setError] = useState('');
    const [verifiedMsg, setVerifiedMsg] = useState('');
    const [loading, setLoading] = useState(false);
    const [showPassword, setShowPassword] = useState(false);
    const navigate = useNavigate();
    const { login } = useAuth();

    const [searchParams] = useSearchParams();

    // ✅ Lấy email & message từ URL khi lần đầu vào trang
    useEffect(() => {
      const email = searchParams.get('email');
      const message = searchParams.get('message');

      if (email) {
        setForm(prev => ({ ...prev, email }));
      }
      if (message === 'email_verified') {
        setVerifiedMsg('✅ Xác minh email thành công. Bạn có thể đăng nhập.');
      } else if (message === 'already_verified') {
        setVerifiedMsg('ℹ️ Email đã được xác minh trước đó.');
      }
    }, [searchParams]);

    const validateEmail = (email) =>
      /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);

    const handleChange = useCallback((e) => {
      setForm((f) => ({ ...f, [e.target.name]: e.target.value }));
      setError('');
    }, []);

    const handleSubmit = useCallback(
      async (e) => {
        e.preventDefault();
        setError('');

        if (!validateEmail(form.email)) {
          setError('Email không hợp lệ');
          return;
        }
        if (!form.password.trim()) {
          setError('Vui lòng nhập mật khẩu');
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
          setError('Không thể kết nối đến máy chủ');
        } finally {
          setLoading(false);
        }
      },
      [form, login, navigate]
    );

    return (
      <Container className="py-5" style={{ maxWidth: 400 }}>
        <h3 className="mb-4">Đăng nhập</h3>

        {verifiedMsg && <Alert variant="success">{verifiedMsg}</Alert>}
        {error && <Alert variant="danger">{error}</Alert>}

        <Form onSubmit={handleSubmit} noValidate>
          <Form.Group className="mb-3" controlId="loginEmail">
            <Form.Label>Email</Form.Label>
            <Form.Control
              type="email"
              name="email"
              value={form.email}
              onChange={handleChange}
              required
              isInvalid={!!error && !validateEmail(form.email)}
              placeholder="Nhập email"
              autoComplete="username"
            />
            <Form.Control.Feedback type="invalid">
              Email không hợp lệ
            </Form.Control.Feedback>
          </Form.Group>

          <Form.Group className="mb-3" controlId="loginPassword">
            <Form.Label>Mật khẩu</Form.Label>
            <InputGroup>
              <Form.Control
                type={showPassword ? 'text' : 'password'}
                name="password"
                value={form.password}
                onChange={handleChange}
                required
                placeholder="Nhập mật khẩu"
                autoComplete="current-password"
              />
              <Button
                variant="outline-secondary"
                onClick={() => setShowPassword((v) => !v)}
                tabIndex={-1}
                aria-label={showPassword ? 'Ẩn mật khẩu' : 'Hiện mật khẩu'}
              >
                {showPassword ? <EyeSlash /> : <Eye />}
              </Button>
            </InputGroup>
          </Form.Group>

          <Button type="submit" variant="dark" className="w-100" disabled={loading}>
            {loading ? 'Đang đăng nhập...' : 'Đăng nhập'}
          </Button>
        </Form>

        <div className="text-center mt-3">
          <Link to="/forgot-password">Quên mật khẩu</Link> &nbsp;|&nbsp;
          <Link to="/resend-verification">Gửi lại xác thực</Link>
        </div>

        <div className="text-center mt-3">
          Chưa có tài khoản? <Link to="/register">Đăng ký</Link>
        </div>
      </Container>
    );
  }
