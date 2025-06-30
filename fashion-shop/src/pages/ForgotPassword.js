import { useState } from 'react';
import { Form, Button, Container, Spinner, Alert } from 'react-bootstrap';

export default function ForgotPassword() {
  const [email, setEmail] = useState('');
  const [error, setError] = useState('');
  const [success, setSuccess] = useState('');
  const [loading, setLoading] = useState(false);

  const validateEmail = (value) => /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value);

  const handleChange = (e) => {
    const value = e.target.value;
    setEmail(value);
    setError('');
    setSuccess('');
    if (value && !validateEmail(value)) {
      setError('Email không hợp lệ');
    }
  };

  const handleSubmit = async (e) => {
    e.preventDefault();
    setError('');
    setSuccess('');

    if (!validateEmail(email)) {
      setError('Vui lòng nhập đúng định dạng email');
      return;
    }

    setLoading(true);
    try {
      const res = await fetch(`${process.env.REACT_APP_API_URL}/forgot-password`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ email }),
      });
      const data = await res.json();

      if (res.ok) {
        setSuccess(data.message || 'Đã gửi liên kết khôi phục mật khẩu');
        setEmail('');
      } else {
        setError(data.errors?.email?.[0] || data.message || 'Lỗi không xác định');
      }
    } catch {
      setError('Không thể kết nối máy chủ');
    } finally {
      setLoading(false);
    }
  };

  return (
    <Container className="py-5" style={{ maxWidth: 500 }}>
      <h4 className="mb-4">Khôi phục mật khẩu</h4>

      {success && <Alert variant="success">{success}</Alert>}

      <Form onSubmit={handleSubmit} noValidate>
        <Form.Group className="mb-3" controlId="email">
          <Form.Label>Email đã đăng ký</Form.Label>
          <Form.Control
            type="email"
            placeholder="Nhập địa chỉ email"
            value={email}
            onChange={handleChange}
            isInvalid={!!error}
            required
            autoComplete="email"
          />
          <Form.Control.Feedback type="invalid">{error}</Form.Control.Feedback>
        </Form.Group>

        <Button type="submit" variant="dark" className="w-100" disabled={loading}>
          {loading ? (
            <>
              <Spinner animation="border" size="sm" className="me-2" />
              Đang gửi...
            </>
          ) : (
            'Gửi liên kết khôi phục'
          )}
        </Button>
      </Form>
    </Container>
  );
}
