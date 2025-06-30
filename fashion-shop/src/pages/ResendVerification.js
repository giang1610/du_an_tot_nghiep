import { useState } from 'react';
import { Form, Button, Container, Alert, Spinner } from 'react-bootstrap';

export default function ResendVerification() {
  const [email, setEmail] = useState('');
  const [success, setSuccess] = useState('');
  const [error, setError] = useState('');
  const [loading, setLoading] = useState(false);

  const handleResend = async (e) => {
    e.preventDefault();
    setSuccess('');
    setError('');
    setLoading(true);

    try {
      const res = await fetch(`${process.env.REACT_APP_API_URL}/resend-verification`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ email }),
      });

      const data = await res.json();

      if (res.ok) {
        setSuccess(data.message || 'Đã gửi lại email xác thực');
        setEmail('');
      } else {
        const msg = data.errors?.email?.[0] || data.message || 'Lỗi không xác định';
        setError(msg);
      }
    } catch {
      setError('Không thể kết nối máy chủ');
    } finally {
      setLoading(false);
    }
  };

  return (
    <Container className="py-5" style={{ maxWidth: 500 }}>
      <h4>Gửi lại email xác thực</h4>

      {error && <Alert variant="danger">{error}</Alert>}
      {success && <Alert variant="success">{success}</Alert>}

      <Form onSubmit={handleResend}>
        <Form.Group className="mb-3">
          <Form.Label>Email đã đăng ký</Form.Label>
          <Form.Control
            type="email"
            value={email}
            onChange={e => setEmail(e.target.value.trim())}
            required
            disabled={loading}
            placeholder="Nhập email của bạn"
          />
        </Form.Group>

        <Button type="submit" variant="dark" className="w-100" disabled={loading}>
          {loading ? (
            <>
              <Spinner animation="border" size="sm" className="me-2" />
              Đang gửi...
            </>
          ) : (
            'Gửi lại email xác thực'
          )}
        </Button>
      </Form>
    </Container>
  );
}
