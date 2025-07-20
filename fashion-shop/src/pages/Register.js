import { useState, useCallback } from 'react';
import { Form, Button, Container, Alert, InputGroup, Spinner } from 'react-bootstrap';
import { useNavigate, Link } from 'react-router-dom';
import { Eye, EyeSlash } from 'react-bootstrap-icons';

export default function Register() {
  const [form, setForm] = useState({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
  });
  const [error, setError] = useState('');
  const [success, setSuccess] = useState('');
  const [loading, setLoading] = useState(false);
  const [showPassword, setShowPassword] = useState(false);
  const [showConfirmPassword, setShowConfirmPassword] = useState(false);

  const navigate = useNavigate();

  const handleChange = useCallback((e) => {
    const { name, value } = e.target;
    setForm((prev) => ({ ...prev, [name]: value }));
    setError('');
  }, []);

  const handleSubmit = async (e) => {
    e.preventDefault();
    setError('');
    setSuccess('');
    setLoading(true);

    if (form.password !== form.password_confirmation) {
      setError('Mật khẩu và xác nhận mật khẩu không khớp');
      setLoading(false);
      return;
    }

    try {
      const res = await fetch(`${process.env.REACT_APP_API_URL}/register`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(form),
      });

      const data = await res.json();

      if (res.ok) {
        setSuccess('Đăng ký thành công! Vui lòng kiểm tra email để xác minh tài khoản trước khi đăng nhập.');
        setForm({ name: '', email: '', password: '', password_confirmation: '' });
        setTimeout(() => navigate('/login'), 6000);
      } else {
        let message = 'Đăng ký thất bại';
        if (data?.errors) {
          const firstError = Object.values(data.errors)[0][0];
          message = firstError;
        } else if (data?.message) {
          message = data.message;
        }
        setError(message);
      }
    } catch {
      setError('Không thể kết nối đến máy chủ');
    } finally {
      setLoading(false);
    }
  };

  return (
    <Container className="py-5" style={{ maxWidth: 500 }}>
      <h3 className="mb-4">Đăng ký</h3>

      {error && (
        <Alert variant="danger" onClose={() => setError('')} dismissible>
          {error}
        </Alert>
      )}

      {success && (
        <Alert variant="success" onClose={() => setSuccess('')} dismissible>
          {success}
        </Alert>
      )}

      <Form onSubmit={handleSubmit} noValidate>
        <Form.Group className="mb-3" controlId="registerName">
          <Form.Label>Họ tên</Form.Label>
          <Form.Control
            type="text"
            name="name"
            value={form.name}
            onChange={handleChange}
            required
            placeholder="Nhập họ tên"
            disabled={loading}
            minLength={3}
          />
        </Form.Group>

        <Form.Group className="mb-3" controlId="registerEmail">
          <Form.Label>Email</Form.Label>
          <Form.Control
            type="email"
            name="email"
            value={form.email}
            onChange={handleChange}
            required
            placeholder="Nhập email"
            disabled={loading}
          />
        </Form.Group>

        <Form.Group className="mb-3" controlId="registerPassword">
          <Form.Label>Mật khẩu</Form.Label>
          <InputGroup>
            <Form.Control
              type={showPassword ? 'text' : 'password'}
              name="password"
              value={form.password}
              onChange={handleChange}
              required
              placeholder="Nhập mật khẩu"
              disabled={loading}
              minLength={6}
            />
            <Button
              variant="outline-secondary"
              onClick={() => setShowPassword((v) => !v)}
              tabIndex={-1}
              disabled={loading}
            >
              {showPassword ? <EyeSlash /> : <Eye />}
            </Button>
          </InputGroup>
        </Form.Group>

        <Form.Group className="mb-3" controlId="registerPasswordConfirm">
          <Form.Label>Nhập lại mật khẩu</Form.Label>
          <InputGroup>
            <Form.Control
              type={showConfirmPassword ? 'text' : 'password'}
              name="password_confirmation"
              value={form.password_confirmation}
              onChange={handleChange}
              required
              placeholder="Xác nhận mật khẩu"
              disabled={loading}
              minLength={6}
            />
            <Button
              variant="outline-secondary"
              onClick={() => setShowConfirmPassword((v) => !v)}
              tabIndex={-1}
              disabled={loading}
            >
              {showConfirmPassword ? <EyeSlash /> : <Eye />}
            </Button>
          </InputGroup>
        </Form.Group>

        <Button type="submit" variant="dark" className="w-100" disabled={loading}>
          {loading ? (
            <>
              <Spinner
                as="span"
                animation="border"
                size="sm"
                role="status"
                aria-hidden="true"
              />{' '}
              Đang đăng ký...
            </>
          ) : (
            'Đăng ký'
          )}
        </Button>
      </Form>

      <div className="text-center mt-3">
        Đã có tài khoản? <Link to="/login">Đăng nhập</Link>
      </div>
    </Container>
  );
}
