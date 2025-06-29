import React, { useState } from 'react';
import { Container, Form, Button, Alert, Card, Spinner } from 'react-bootstrap';
import axios from 'axios';

const ForgotPasswordPage = () => {
  const [email, setEmail] = useState('');
  const [success, setSuccess] = useState('');
  const [error, setError] = useState('');
  const [loading, setLoading] = useState(false);

  const handleSubmit = async (e) => {
    e.preventDefault();
    setError('');
    setSuccess('');
    setLoading(true);

    try {
      await axios.post(`${process.env.REACT_APP_API_URI}/forgot-password`, { email });
      setSuccess('✅ Email đặt lại mật khẩu đã được gửi. Vui lòng kiểm tra hộp thư.');
      setEmail('');
    } catch (err) {
      const message = axios.isAxiosError(err)
        ? err.response?.data?.message || 'Lỗi máy chủ. Vui lòng thử lại.'
        : 'Đã xảy ra lỗi.';
      setError(message);
    } finally {
      setLoading(false);
    }
  };

  return (
    <Container className="d-flex justify-content-center align-items-center py-5" style={{ minHeight: '100vh' }}>
      <Card className="p-4 shadow-lg w-100" style={{ maxWidth: '420px' }}>
        <Card.Body>
          <div className="text-center mb-4">
            <h3 className="fw-bold">🔐 Quên Mật Khẩu</h3>
            <p className="text-muted small">
              Nhập email của bạn để nhận hướng dẫn đặt lại mật khẩu.
            </p>
          </div>

          {error && <Alert variant="danger">{error}</Alert>}
          {success && <Alert variant="success">{success}</Alert>}

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
