import { useState } from 'react';
import axios from 'axios';
import { Container, Button, Alert } from 'react-bootstrap';

export default function ResendEmailPage() {
  const [message, setMessage] = useState('');
  const [error, setError] = useState('');

  const handleResend = async () => {
    try {
      const token = localStorage.getItem('token');
      await axios.post(`${process.env.REACT_APP_API_URL}/email/resend`, {}, {
        headers: {
          Authorization: `Bearer ${token}`,
        }
      });
      setMessage('Email xác thực đã được gửi lại!');
    } catch (err) {
      setError('Không thể gửi lại email. Vui lòng đăng nhập lại.');
    }
  };

  return (
    <Container className="py-5" style={{ maxWidth: 500 }}>
      <h3 className="mb-4">Xác minh email</h3>
      {message && <Alert variant="success">{message}</Alert>}
      {error && <Alert variant="danger">{error}</Alert>}

      <p>Vui lòng kiểm tra hộp thư đến của bạn. Nếu chưa nhận được, bạn có thể gửi lại:</p>
      <Button variant="dark" onClick={handleResend}>Gửi lại email xác thực</Button>
    </Container>
  );
}
