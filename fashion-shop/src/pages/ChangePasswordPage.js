import { useState } from 'react';
import { Form, Button, Container, Alert, InputGroup } from 'react-bootstrap';
import { useAuth } from '../context/AuthContext';
import { Eye, EyeSlash } from 'react-bootstrap-icons';

function PasswordInput({ label, name, value, onChange, show, toggleShow }) {
  return (
    <Form.Group className="mb-3">
      <Form.Label>{label}</Form.Label>
      <InputGroup>
        <Form.Control
          type={show ? 'text' : 'password'}
          name={name}
          value={value}
          onChange={onChange}
          required
        />
        <Button
          variant="outline-secondary"
          onClick={toggleShow}
          tabIndex={-1}
          type="button"
        >
          {show ? <EyeSlash /> : <Eye />}
        </Button>
      </InputGroup>
    </Form.Group>
  );
}

export default function ChangePasswordPage() {
  const { token } = useAuth();
  const [form, setForm] = useState({
    current_password: '',
    new_password: '',
    new_password_confirmation: '',
  });
  const [success, setSuccess] = useState('');
  const [error, setError] = useState('');

  // Gom trạng thái show mật khẩu
  const [show, setShow] = useState({
    current: false,
    new: false,
    confirm: false,
  });

  const toggleShow = (field) => {
    setShow(prev => ({ ...prev, [field]: !prev[field] }));
  };

  const handleChange = e => {
    setForm({ ...form, [e.target.name]: e.target.value });
  };

  const handleSubmit = async e => {
    e.preventDefault();
    setSuccess('');
    setError('');

    try {
      const res = await fetch(`${process.env.REACT_APP_API_URL}/change-password`, {
        method: 'PUT',
        headers: {
          'Content-Type': 'application/json',
          Authorization: `Bearer ${token}`
        },
        body: JSON.stringify(form)
      });

      const data = await res.json();
      if (res.ok) {
        setSuccess('Đổi mật khẩu thành công');
        setForm({
          current_password: '',
          new_password: '',
          new_password_confirmation: ''
        });
      } else {
        setError(data.message || 'Lỗi đổi mật khẩu');
      }
    } catch (err) {
      setError('Lỗi kết nối máy chủ');
    }
  };

  return (
    <Container className="py-5" style={{ maxWidth: 500 }}>
      <h3 className="mb-4">Đổi mật khẩu</h3>
      {success && <Alert variant="success">{success}</Alert>}
      {error && <Alert variant="danger">{error}</Alert>}

      <Form onSubmit={handleSubmit}>
        <PasswordInput
          label="Mật khẩu hiện tại"
          name="current_password"
          value={form.current_password}
          onChange={handleChange}
          show={show.current}
          toggleShow={() => toggleShow('current')}
        />

        <PasswordInput
          label="Mật khẩu mới"
          name="new_password"
          value={form.new_password}
          onChange={handleChange}
          show={show.new}
          toggleShow={() => toggleShow('new')}
        />

        <PasswordInput
          label="Nhập lại mật khẩu"
          name="new_password_confirmation"
          value={form.new_password_confirmation}
          onChange={handleChange}
          show={show.confirm}
          toggleShow={() => toggleShow('confirm')}
        />

        <Button type="submit" variant="dark">Đổi mật khẩu</Button>
      </Form>
    </Container>
  );
}
