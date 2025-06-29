import React, { useState } from 'react';
import {
  Container,
  Form,
  Button,
  Alert,
  Card,
  InputGroup,
  Spinner,
} from 'react-bootstrap';
import { Eye, EyeSlash } from 'react-bootstrap-icons';
import { Link, useNavigate } from 'react-router-dom';
import axios from 'axios';

const RegisterPage = () => {
  const navigate = useNavigate();

  const [form, setForm] = useState({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
  });

  const [showPass, setShowPass] = useState({
    password: false,
    password_confirmation: false,
  });

  const [loading, setLoading] = useState(false);
  const [error, setError] = useState('');
  const [success, setSuccess] = useState('');

  const handleChange = (e) =>
    setForm({ ...form, [e.target.name]: e.target.value });

  const toggleShowPass = (field) =>
    setShowPass((prev) => ({ ...prev, [field]: !prev[field] }));

  const handleSubmit = async (e) => {
    e.preventDefault();
    setError('');
    setSuccess('');
    setLoading(true);

    try {
      const res = await axios.post(
        `${process.env.REACT_APP_API_URI}/register`,
        form
      );

      if (res.status === 200 || res.status === 201) {
        setSuccess('Đăng ký thành công! Vui lòng kiểm tra email.');
        setTimeout(() => navigate('/login'), 2000);
      } else {
        setError('Đăng ký thất bại, thử lại.');
      }
    } catch (err) {
      const errors = err.response?.data?.errors;
      setError(
        errors
          ? Object.values(errors).flat().join(' ')
          : err.response?.data?.message || 'Đã xảy ra lỗi.'
      );
    } finally {
      setLoading(false);
    }
  };

  const renderPasswordInput = (label, name) => (
    <Form.Group className="mb-3">
      <Form.Label>{label}</Form.Label>
      <InputGroup>
        <Form.Control
          type={showPass[name] ? 'text' : 'password'}
          name={name}
          value={form[name]}
          onChange={handleChange}
          placeholder={label}
          required
        />
        <Button
          variant="outline-secondary"
          onClick={() => toggleShowPass(name)}
          tabIndex={-1}
        >
          {showPass[name] ? <EyeSlash /> : <Eye />}
        </Button>
      </InputGroup>
    </Form.Group>
  );

  return (
    <Container
      className="d-flex justify-content-center align-items-center"
      style={{ minHeight: '80vh' }}
    >
      <Card
        style={{ width: '100%', maxWidth: '500px', padding: '20px' }}
        className="shadow"
      >
        <Card.Body>
          <h3 className="mb-4 text-center">Đăng Ký</h3>

          {error && <Alert variant="danger">{error}</Alert>}
          {success && <Alert variant="success">{success}</Alert>}

          <Form onSubmit={handleSubmit} noValidate>
            <Form.Group className="mb-3">
              <Form.Label>Họ tên</Form.Label>
              <Form.Control
                name="name"
                value={form.name}
                onChange={handleChange}
                placeholder="Nhập họ tên"
                required
              />
            </Form.Group>

            <Form.Group className="mb-3">
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

            {renderPasswordInput('Mật khẩu', 'password')}
            {renderPasswordInput('Nhập lại mật khẩu', 'password_confirmation')}

            <Button type="submit" variant="primary" className="w-100" disabled={loading}>
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
            <Link to="/login">Đã có tài khoản? Đăng nhập</Link>
          </div>
        </Card.Body>
      </Card>
    </Container>
  );
};

export default RegisterPage;
