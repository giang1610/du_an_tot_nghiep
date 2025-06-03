import React, { useState } from 'react';
import { Container, Form, Button, Alert } from 'react-bootstrap';
import axios from 'axios';

const CheckoutPage = () => {
  const cart = JSON.parse(localStorage.getItem('cart')) || [];
  const [formData, setFormData] = useState({ name: '', email: '', address: '' });
  const [success, setSuccess] = useState('');
  const [error, setError] = useState('');

  const total = cart.reduce((sum, item) => sum + item.price * item.quantity, 0);

  const handleSubmit = async (e) => {
    e.preventDefault();
    setError('');
    if (cart.length === 0) {
      setError('Giỏ hàng trống');
      return;
    }

    try {
      await axios.post('http://localhost:8000/api/orders', {
        ...formData,
        cart
      });

      localStorage.removeItem('cart');
      setSuccess('Đặt hàng thành công! Vui lòng kiểm tra email xác nhận.');
    } catch (err) {
      setError('Có lỗi xảy ra khi đặt hàng.');
    }
  };

  return (
    <Container className="my-5">
      <h3>Đặt hàng</h3>
      {error && <Alert variant="danger">{error}</Alert>}
      {success ? (
        <Alert variant="success">{success}</Alert>
      ) : (
        <Form onSubmit={handleSubmit}>
          <Form.Group className="mb-3">
            <Form.Label>Họ tên</Form.Label>
            <Form.Control
              type="text"
              value={formData.name}
              onChange={(e) => setFormData({ ...formData, name: e.target.value })}
              required
            />
          </Form.Group>
          <Form.Group className="mb-3">
            <Form.Label>Email</Form.Label>
            <Form.Control
              type="email"
              value={formData.email}
              onChange={(e) => setFormData({ ...formData, email: e.target.value })}
              required
            />
          </Form.Group>
          <Form.Group className="mb-3">
            <Form.Label>Địa chỉ giao hàng</Form.Label>
            <Form.Control
              as="textarea"
              rows={3}
              value={formData.address}
              onChange={(e) => setFormData({ ...formData, address: e.target.value })}
              required
            />
          </Form.Group>
          <h5>Tổng tiền: {total.toLocaleString()} đ</h5>
          <Button type="submit" variant="primary">Xác nhận đặt hàng</Button>
        </Form>
      )}
    </Container>
  );
};

export default CheckoutPage;
