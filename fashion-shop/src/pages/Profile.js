import { useState, useCallback } from 'react';
import { Form, Button, Container, Alert, Spinner } from 'react-bootstrap';
import { useAuth } from '../context/AuthContext';

export default function ProfilePage() {
  const { user, token, login } = useAuth();
  const [form, setForm] = useState({ name: user.name, email: user.email });
  const [success, setSuccess] = useState('');
  const [error, setError] = useState('');
  const [loading, setLoading] = useState(false);

  const handleChange = useCallback((e) => {
    const { name, value } = e.target;
    setForm(prev => ({ ...prev, [name]: value }));
  }, []);

  const handleSubmit = async (e) => {
    e.preventDefault();
    setSuccess('');
    setError('');
    setLoading(true);

    try {
      const res = await fetch(`${process.env.REACT_APP_API_URL}/profile`, {
        method: 'PUT',
        headers: {
          'Content-Type': 'application/json',
          Authorization: `Bearer ${token}`,
        },
        body: JSON.stringify(form),
      });

      const data = await res.json();

      if (res.ok) {
        setSuccess('Cập nhật thành công');
<<<<<<< HEAD
        login(token, data.user); 
=======
        login(token, data.user); // Cập nhật user context
>>>>>>> ad45c50f6c3d737e3470ec1213e51e61a1cf0c95
      } else {
        setError(data.message || 'Lỗi cập nhật');
      }
    } catch {
      setError('Lỗi kết nối máy chủ');
    } finally {
      setLoading(false);
    }
  };

  return (
    <Container className="py-5" style={{ maxWidth: 500 }}>
      <h3 className="mb-4">Cập nhật thông tin</h3>

      {success && <Alert variant="success" onClose={() => setSuccess('')} dismissible>{success}</Alert>}
      {error && <Alert variant="danger" onClose={() => setError('')} dismissible>{error}</Alert>}

      <Form onSubmit={handleSubmit} noValidate>
        <Form.Group className="mb-3" controlId="profileName">
          <Form.Label>Họ tên</Form.Label>
          <Form.Control
            name="name"
            value={form.name}
            onChange={handleChange}
            required
            disabled={loading}
            placeholder="Nhập họ tên"
          />
        </Form.Group>

        <Form.Group className="mb-3" controlId="profileEmail">
          <Form.Label>Email</Form.Label>
          <Form.Control
            type="email"
            name="email"
            value={form.email}
            onChange={handleChange}
            required
            disabled={loading}
            placeholder="Nhập email"
          />
        </Form.Group>

        <Button type="submit" variant="dark" disabled={loading}>
          {loading ? <><Spinner as="span" animation="border" size="sm" role="status" aria-hidden="true" /> Đang lưu...</> : 'Lưu thay đổi'}
        </Button>
      </Form>
    </Container>
  );
}
