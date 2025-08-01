import { useState, useCallback, useEffect, useRef } from 'react';
import { Form, Button, Container, Alert, Spinner, Image } from 'react-bootstrap';
import { useAuth } from '../context/AuthContext';
import axios from '../api/axios';

export default function ProfilePage() {
  const { user, token, login } = useAuth();

  const [form, setForm] = useState({
    name: '',
    email: '',
    phone: '',
    address: '',
    avatar: null,
  });
  const [success, setSuccess] = useState('');
  const [error, setError] = useState('');
  const [loading, setLoading] = useState(false);
  const [preview, setPreview] = useState(null);
  const fileInputRef = useRef();

  const defaultAvatar =
    'https://img.freepik.com/premium-vector/man-avatar-profile-picture-isolated-background-avatar-profile-picture-man_1293239-4841.jpg';

  // Load thông tin user ban đầu
  useEffect(() => {
    if (user) {
      setForm({
        name: user.name || '',
        email: user.email || '',
        phone: user.phone || '',
        address: user.address || '',
        avatar: null,
      });
      const existingAvatar = user.img_thumbnail
        ? `${process.env.REACT_APP_IMAGE_BASE_URL}/storage/${user.img_thumbnail}`
        : defaultAvatar;
      setPreview(existingAvatar);
    }
  }, [user]);

  const handleChange = useCallback((e) => {
    const { name, value } = e.target;
    setForm((prev) => ({ ...prev, [name]: value }));
  }, []);

  const handleAvatarClick = () => {
    fileInputRef.current?.click();
  };

  const handleAvatarChange = (e) => {
    const file = e.target.files[0];
    if (!file) return;

    const reader = new FileReader();
    reader.onloadend = () => {
      setForm((prev) => ({ ...prev, avatar: reader.result }));
      setPreview(reader.result);
    };
    reader.readAsDataURL(file);
  };

  const handleSubmit = async (e) => {
    e.preventDefault();
    setSuccess('');
    setError('');
    setLoading(true);

    try {
      const payload = {
        name: form.name,
        email: form.email,
        phone: form.phone,
        address: form.address,
      };

      if (form.avatar) {
        payload.avatar = form.avatar;
      }

      const res = await axios.put('/profile', payload, {
        headers: {
          Authorization: `Bearer ${token}`,
        },
      });

      setSuccess('✅ Cập nhật thành công');
      login(token, res.data.user);
    } catch (err) {
      setError(err.response?.data?.message || '❌ Lỗi kết nối máy chủ');
    } finally {
      setLoading(false);
    }
  };

  if (!user) {
    return (
      <Container className="py-5 text-center">
        <Spinner animation="border" />
        <p className="mt-3">Đang tải dữ liệu người dùng...</p>
      </Container>
    );
  }

  return (
    <Container className="py-5" style={{ maxWidth: 500 }}>
      <h3 className="mb-4 text-center">Cập nhật thông tin</h3>

      <div className="text-center mb-4">
        <Image
          src={preview || defaultAvatar}
          onClick={handleAvatarClick}
          alt="Avatar"
          style={{
            width: '120px',
            height: '120px',
            objectFit: 'cover',
            borderRadius: '50%',
            border: '3px solid #343a40',
            cursor: 'pointer',
            backgroundColor: '#f8f9fa',
          }}
        />
        <Form.Control
          type="file"
          ref={fileInputRef}
          onChange={handleAvatarChange}
          accept="image/*"
          style={{ display: 'none' }}
        />
        <small className="d-block mt-2 text-muted">Bấm vào ảnh để thay đổi</small>
      </div>

      {success && <Alert variant="success" dismissible>{success}</Alert>}
      {error && <Alert variant="danger" dismissible>{error}</Alert>}

      <Form onSubmit={handleSubmit}>
        <Form.Group className="mb-3">
          <Form.Label>Họ tên</Form.Label>
          <Form.Control
            name="name"
            value={form.name}
            onChange={handleChange}
            required
            placeholder="Nhập họ tên"
          />
        </Form.Group>

        <Form.Group className="mb-3">
          <Form.Label>Email</Form.Label>
          <Form.Control
            type="email"
            name="email"
            value={form.email}
            onChange={handleChange}
            required
            placeholder="Nhập email"
          />
        </Form.Group>

        <Form.Group className="mb-3">
          <Form.Label>Số điện thoại</Form.Label>
          <Form.Control
            name="phone"
            value={form.phone}
            onChange={handleChange}
            placeholder="Nhập số điện thoại"
          />
        </Form.Group>

        <Form.Group className="mb-3">
          <Form.Label>Địa chỉ</Form.Label>
          <Form.Control
            name="address"
            value={form.address}
            onChange={handleChange}
            placeholder="Nhập địa chỉ giao hàng"
          />
        </Form.Group>

        <Button type="submit" variant="dark" disabled={loading}>
          {loading ? 'Đang lưu...' : 'Lưu thay đổi'}
        </Button>
      </Form>
    </Container>
  );
}