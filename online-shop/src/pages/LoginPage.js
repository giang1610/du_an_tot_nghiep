import React, { useState } from 'react';
import { Container, Form, Button, Alert, Card, Spinner } from 'react-bootstrap';
import axios from 'axios';
import { useNavigate, Link } from 'react-router-dom';

const LoginPage = () => {
  const [form, setForm] = useState({ email: '', password: '' });
  const [error, setError] = useState('');
  const [loading, setLoading] = useState(false);
  const navigate = useNavigate();

  const handleChange = (e) => setForm({ ...form, [e.target.name]: e.target.value });

  const handleSubmit = async (e) => {
    e.preventDefault();
    setError('');
    setLoading(true);

    try {
      await axios.get('http://localhost:8000/sanctum/csrf-cookie', { withCredentials: true });
      const res = await axios.post('http://localhost:8000/api/login', form, { withCredentials: true });

      localStorage.setItem('user', JSON.stringify(res.data));
      navigate('/');
    } catch (err) {
      if (err.response) {
        if (err.response.status === 401) setError('Email hoặc mật khẩu không đúng.');
        else if (err.response.status === 403) setError('Vui lòng xác nhận email trước khi đăng nhập.');
        else setError('Đã có lỗi xảy ra. Vui lòng thử lại.');
      } else setError('Không thể kết nối tới server.');
    } finally {
      setLoading(false);
    }
  };

  return (
    <Container
      className="d-flex justify-content-center align-items-center"
      style={{ minHeight: '80vh' }}
    >
      <Card style={{ width: '100%', maxWidth: '420px', padding: '20px' }} className="shadow">
        <Card.Body>
          <h3 className="mb-4 text-center">Đăng Nhập</h3>

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
              <Form.Control
                type="password"
                name="password"
                value={form.password}
                onChange={handleChange}
                placeholder="Nhập mật khẩu"
                required
              />
            </Form.Group>

            <Button type="submit" variant="success" className="w-100" disabled={loading}>
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

          <div className="mt-3 d-flex justify-content-between">
            <Link to="/forgot-password">Quên mật khẩu?</Link>
            <Link to="/register">Chưa có tài khoản? Đăng ký</Link>
          </div>
        </Card.Body>
      </Card>
    </Container>
  );
};

export default LoginPage;
