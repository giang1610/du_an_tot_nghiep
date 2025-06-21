import React, { useState } from 'react';
import {
  Container,
  Form,
  Button,
  Alert,
  Card,
  Spinner,
  InputGroup,
} from 'react-bootstrap';
import axios from 'axios';
import { useNavigate, Link } from 'react-router-dom';
import { Eye, EyeSlash } from 'react-bootstrap-icons';
import { useAuth } from '../context/AuthContext';

const LoginPage = () => {
  const [form, setForm] = useState({ email: '', password: '' });
  const [error, setError] = useState('');
  const [loading, setLoading] = useState(false);
  const [showPassword, setShowPassword] = useState(false);
  const navigate = useNavigate();
  const { login } = useAuth();

  const handleChange = ({ target: { name, value } }) =>
    setForm((prev) => ({ ...prev, [name]: value }));

  const handleSubmit = async (e) => {
    e.preventDefault();
    setError('');
    setLoading(true);

    try {
      const { data } = await axios.post('http://localhost:8000/api/login', form);
      login(data.user, data.token);
      navigate('/');
    } catch (err) {
      const status = err?.response?.status;
      const message =
        status === 401
          ? 'Email hoặc mật khẩu không đúng.'
          : status === 403
          ? 'Vui lòng xác nhận email trước khi đăng nhập.'
          : status
          ? 'Đã có lỗi xảy ra. Vui lòng thử lại.'
          : 'Không thể kết nối tới máy chủ.';
      setError(message);
    } finally {
      setLoading(false);
    }
  };

  return (
    <Container className="d-flex justify-content-center align-items-center py-5" style={{ minHeight: '100vh' }}>
      <Card className="shadow-lg w-100" style={{ maxWidth: 420 }}>
        <Card.Body className="p-4">
          <h3 className="mb-3 text-center fw-bold">🔐 Đăng Nhập</h3>
          <p className="text-muted text-center small mb-4">
            Vui lòng nhập thông tin để truy cập tài khoản của bạn
          </p>

          {error && <Alert variant="danger">{error}</Alert>}

          <Form onSubmit={handleSubmit} noValidate>
            <Form.Group className="mb-3" controlId="email">
              <Form.Label>Email</Form.Label>
              <Form.Control
                type="email"
                name="email"
                value={form.email}
                onChange={handleChange}
                placeholder="Nhập email"
                required
                autoFocus
              />
            </Form.Group>

            <Form.Group className="mb-4" controlId="password">
              <Form.Label>Mật khẩu</Form.Label>
              <InputGroup>
                <Form.Control
                  type={showPassword ? 'text' : 'password'}
                  name="password"
                  value={form.password}
                  onChange={handleChange}
                  placeholder="Nhập mật khẩu"
                  required
                />
                <Button
                  variant="outline-secondary"
                  onClick={() => setShowPassword((prev) => !prev)}
                  aria-label={showPassword ? 'Ẩn mật khẩu' : 'Hiện mật khẩu'}
                  tabIndex={-1}
                >
                  {showPassword ? <EyeSlash /> : <Eye />}
                </Button>
              </InputGroup>
            </Form.Group>

            <Button
              type="submit"
              variant="primary"
              className="w-100 fw-semibold"
              disabled={loading}
            >
              {loading ? (
                <>
                  <Spinner animation="border" size="sm" className="me-2" />
                  Đang đăng nhập...
                </>
              ) : (
                'Đăng Nhập'
              )}
            </Button>
          </Form>

          <div className="d-flex justify-content-between mt-3 small">
            <Link to="/forgot-password">Quên mật khẩu?</Link>
            <Link to="/register">Chưa có tài khoản? Đăng ký</Link>
          </div>
        </Card.Body>
      </Card>
    </Container>
  );
};

export default LoginPage;
