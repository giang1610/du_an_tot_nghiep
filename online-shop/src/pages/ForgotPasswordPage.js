import React, { useState } from 'react';
import { Container, Form, Button, Alert, Card, Spinner } from 'react-bootstrap';
import axios from 'axios';

const ForgotPasswordPage = () => {
  const [email, setEmail] = useState('');
  const [message, setMessage] = useState({ type: '', text: '' });
  const [loading, setLoading] = useState(false);

  const isValidEmail = (email) =>
    /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);

  const handleSubmit = async (e) => {
    e.preventDefault();
    setMessage({ type: '', text: '' });

    if (!isValidEmail(email)) {
      setMessage({ type: 'danger', text: 'Vui lòng nhập email hợp lệ.' });
      return;
    }

    setLoading(true);

    try {
      await axios.post(`${process.env.REACT_APP_API_URI}/forgot-password`, { email });
      setMessage({
        type: 'success',
        text: '✅ Email đặt lại mật khẩu đã được gửi. Vui lòng kiểm tra hộp thư.',
      });
      setEmail('');
    } catch (err) {
      const msg = axios.isAxiosError(err)
        ? err.response?.data?.message || 'Lỗi máy chủ. Vui lòng thử lại.'
        : 'Đã xảy ra lỗi.';

      setMessage({ type: 'danger', text: msg });
    } finally {
      setLoading(false);
    }
  };

  return (
    <Container
      className="d-flex justify-content-center align-items-center py-5"
      style={{ minHeight: '100vh' }}
    >
      <Card className="p-4 shadow-lg w-100" style={{ maxWidth: '420px' }}>
        <Card.Body>
          <div className="text-center mb-4">
            <h3 className="fw-bold">🔐 Quên Mật Khẩu</h3>
            <p className="text-muted small">
              Nhập email của bạn để nhận hướng dẫn đặt lại mật khẩu.
            </p>
          </div>

          {message.text && (
            <Alert variant={message.type}>{message.text}</Alert>
          )}

          <Form onSubmit={handleSubmit} noValidate>
            <Form.Group className="mb-3" controlId="email">
              <Form.Label>Địa chỉ Email</Form.Label>
              <Form.Control
                type="email"
                value={email}
                onChange={(e) => setEmail(e.target.value)}
                placeholder="Nhập email đăng ký"
                required
                autoFocus
                disabled={loading}
              />
            </Form.Group>

            <Button
              type="submit"
              variant="primary"
              className="w-100 fw-semibold"
              disabled={loading || !email}
            >
              {loading ? (
                <>
                  <Spinner animation="border" size="sm" className="me-2" />
                  Đang gửi...
                </>
              ) : (
                'Gửi Email Đặt Lại Mật Khẩu'
              )}
            </Button>
          </Form>
        </Card.Body>
      </Card>
    </Container>
  );
};

export default ForgotPasswordPage;
