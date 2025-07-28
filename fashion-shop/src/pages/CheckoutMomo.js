// src/pages/CheckoutMomo.jsx
import React, { useEffect, useState } from 'react';
import axios from 'axios';
import { Button, Form, Spinner, Alert } from 'react-bootstrap';
import { useNavigate } from 'react-router-dom';

export default function CheckoutMomo() {
  const [formData, setFormData] = useState({
    shipping_address: '',
    billing_address: '',
    customer_phone: '',
    notes: '',
  });
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState('');
  const navigate = useNavigate();

  const token = localStorage.getItem('token');

  const handleChange = (e) => {
    setFormData(prev => ({
      ...prev,
      [e.target.name]: e.target.value
    }));
  };

  const handleSubmit = async (e) => {
    e.preventDefault();
    setLoading(true);
    setError('');

    try {
      const res = await axios.post(
        'http://localhost:8000/api/orders/momo/process',
        formData,
        {
          headers: {
            Authorization: `Bearer ${token}`
          }
        }
      );

      const paymentUrl = res.data.data.payment_url;
      // Redirect to MoMo
      window.location.href = paymentUrl;

    } catch (err) {
      setError(err.response?.data?.message || 'Lỗi thanh toán');
    } finally {
      setLoading(false);
    }
  };

  return (
    <div className="container my-5" style={{ maxWidth: 600 }}>
      <h3>Thanh toán MoMo</h3>
      {error && <Alert variant="danger">{error}</Alert>}
      <Form onSubmit={handleSubmit}>
        <Form.Group className="mb-3">
          <Form.Label>Địa chỉ giao hàng *</Form.Label>
          <Form.Control
            name="shipping_address"
            value={formData.shipping_address}
            onChange={handleChange}
            required
          />
        </Form.Group>

        <Form.Group className="mb-3">
          <Form.Label>Địa chỉ thanh toán (bỏ trống nếu giống địa chỉ giao hàng)</Form.Label>
          <Form.Control
            name="billing_address"
            value={formData.billing_address}
            onChange={handleChange}
          />
        </Form.Group>

        <Form.Group className="mb-3">
          <Form.Label>Số điện thoại *</Form.Label>
          <Form.Control
            name="customer_phone"
            value={formData.customer_phone}
            onChange={handleChange}
            required
          />
        </Form.Group>

        <Form.Group className="mb-3">
          <Form.Label>Ghi chú</Form.Label>
          <Form.Control
            name="notes"
            value={formData.notes}
            onChange={handleChange}
            as="textarea"
            rows={3}
          />
        </Form.Group>

        <Button type="submit" disabled={loading}>
          {loading ? <Spinner animation="border" size="sm" /> : 'Thanh toán bằng MoMo'}
        </Button>
      </Form>
    </div>
  );
}
