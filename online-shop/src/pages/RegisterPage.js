import React, { useState } from 'react';
import { Container, Form, Button, Alert, Card, Spinner } from 'react-bootstrap';
import axios from 'axios';
import { Link } from 'react-router-dom';

const RegisterPage = () => {
  const [form, setForm] = useState({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
  });
  const [error, setError] = useState('');
  const [success, setSuccess] = useState('');
  const [loading, setLoading] = useState(false);

  const handleChange = (e) =>
    setForm({ ...form, [e.target.name]: e.target.value });

  const handleSubmit = async (e) => {
    e.preventDefault();
    setError('');
    setSuccess('');
    setLoading(true);

    try {
      const res = await axios.post('http://localhost:8000/api/register', form);
      setSuccess('Đăng ký thành công! Vui lòng kiểm tra email để xác nhận.');
      setForm({
        name: '',
        email: '',
        password: '',
        password_confirmation: '',
      });
    } catch (err) {
      if (err.response?.data?.errors) {
        const firstError = Object.values(err.response.data.errors)[0];
        setError(firstError);
      } else if (err.response?.data?.message) {
        setError(err.response.data.message);
      } else {
        setError('Có lỗi xảy ra. Vui lòng thử lại.');
      }
    } finally {
      setLoading(false);
    }
  };

  return (
    <Container
      className="d-flex justify-content-center align-items-center"
      style={{ minHeight: '80vh' }}
    >
      <Card style={{ width: '100%', maxWidth: '500px', padding: '20px' }} className="shadow">
        <Card.Body>
          <h3 className="mb-4 text-center">Đăng Ký</h3>

          {error && <Alert variant="danger">{error}</Alert>}
          {success && <Alert variant="success">{success}</Alert>}

          <Form onSubmit={handleSubmit} noValidate>
            <Form.Group className="mb-3" controlId="name">
              <Form.Label>Họ tên</Form.Label>
              <Form.Control
                name="name"
                value={form.name}
                onChange={handleChange}
                placeholder="Nhập họ tên"
                required
                autoFocus
              />
            </Form.Group>

            <Form.Group className="mb-3" controlId="email">
              <Form.Label>Email</Form.Label>
              <Form.Control
                type="email"
                name="email"
                value={form.email}
                onChange={handleChange}
                placeholder="Nhập email"
                required
              />
            </Form.Group>

            <Form.Group className="mb-3" controlId="password">
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

            <Form.Group className="mb-4" controlId="password_confirmation">
              <Form.Label>Nhập lại mật khẩu</Form.Label>
              <Form.Control
                type="password"
                name="password_confirmation"
                value={form.password_confirmation}
                onChange={handleChange}
                placeholder="Nhập lại mật khẩu"
                required
              />
            </Form.Group>

            <Button
              type="submit"
              variant="primary"
              className="w-100"
              disabled={loading}
            >
              {loading ? (
                <>
                  <Spinner animation="border" size="sm" className="me-2" />
                  Đang đăng ký...
                </>
              ) : (
                'Đăng Ký'
              )}
            </Button>
          </Form>

          <div className="mt-3 text-center">
            <Link to="/login">Đã có tài khoản? Đăng nhập ngay</Link>
          </div>
        </Card.Body>
      </Card>
    </Container>
  );
};

export default RegisterPage;
