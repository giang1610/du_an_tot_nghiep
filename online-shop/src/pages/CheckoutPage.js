import React, { useState } from 'react';
import {
  Container,
  Row,
  Col,
  Card,
  Button,
  Form
} from 'react-bootstrap';
import { useCart } from '../context/CartContext';
import { useNavigate } from 'react-router-dom';
import axios from 'axios';

const formatPrice = (price) =>
  new Intl.NumberFormat('vi-VN').format(price) + '₫';

const CheckoutPage = () => {
  const { cart, total, removeFromCart, updateQuantity, clearCart } = useCart();
  const navigate = useNavigate();

  const [formData, setFormData] = useState({
    name: '',
    phone: '',
    email: '',
    address: '',
    payment_method: 'cod',
  });

  const handleInputChange = ({ target: { name, value } }) => {
    setFormData((prev) => ({ ...prev, [name]: value }));
  };

  const handleSubmit = async (e) => {
    e.preventDefault();

    const { name, address, phone, email, payment_method } = formData;

    if (!name || !address || !phone || !email) {
      alert('Vui lòng điền đầy đủ thông tin!');
      return;
    }

    const token = localStorage.getItem('token');
    if (!token) {
      alert('Vui lòng đăng nhập để đặt hàng!');
      return;
    }

    const items = cart.map((item) => ({
      product_variant_id: item.product_variant_id,
      quantity: item.quantity,
      price: item.price,
      size_id: item.size_id,
      color_id: item.color_id,
    }));

    try {
      await axios.post(
        `${process.env.REACT_APP_API_URI}/orders`,
        {
          name,
          phone,
          email,
          address,
          payment_method,
          items,
        },
        {
          headers: {
            Authorization: `Bearer ${token}`,
          },
        }
      );

      alert('🎉 Đặt hàng thành công!');
      clearCart();
      navigate('/');
    } catch (err) {
      if (err.response?.status === 422) {
        const errors = err.response.data.errors;
        alert(
          '❌ Lỗi xác thực:\n' +
          Object.values(errors).flat().join('\n')
        );
      } else {
        console.error('Lỗi đặt hàng:', err);
        alert('❌ Có lỗi xảy ra khi đặt hàng!');
      }
    }
  };

  if (cart.length === 0) {
    return (
      <Container className="my-5 text-center">
        <h4>🛒 Giỏ hàng của bạn đang trống</h4>
        <Button variant="primary" onClick={() => navigate('/')}>
          Quay lại mua sắm
        </Button>
      </Container>
    );
  }

  return (
    <Container className="my-5">
      <Row>
        {/* Danh sách sản phẩm */}
        <Col md={8}>
          <h4 className="mb-4">Sản phẩm trong giỏ hàng</h4>
          {cart.map(({ id, name, image, quantity, price, sizeLabel, colorLabel }) => (
            <Card key={id} className="mb-3 shadow-sm border-0">
              <Card.Body className="d-flex align-items-center">
                <img
                  src={image}
                  alt={name}
                  style={{ width: 80, height: 80, objectFit: 'contain' }}
                  className="me-3 border rounded"
                />
                <div className="flex-grow-1">
                  <h6>{name}</h6>
                  <p className="mb-1 text-muted">
                    Kích cỡ: {sizeLabel} | Màu sắc: {colorLabel}
                  </p>
                  <div className="d-flex align-items-center gap-2">
                    <Form.Control
                      type="number"
                      min={1}
                      value={quantity}
                      onChange={(e) =>
                        updateQuantity(id, Math.max(1, parseInt(e.target.value) || 1))
                      }
                      style={{ width: 80 }}
                    />
                    <span>{formatPrice(price * quantity)}</span>
                  </div>
                </div>
                <Button
                  variant="outline-danger"
                  size="sm"
                  onClick={() => removeFromCart(id)}
                >
                  Xóa
                </Button>
              </Card.Body>
            </Card>
          ))}
        </Col>

        {/* Form đặt hàng */}
        <Col md={4}>
          <h4 className="mb-4">Thông tin đặt hàng</h4>
          <Form onSubmit={handleSubmit}>
            {[
              { label: 'Họ tên', name: 'name', type: 'text', placeholder: 'Nguyễn Văn A' },
              { label: 'Email', name: 'email', type: 'email', placeholder: 'abc@gmail.com' },
              { label: 'Số điện thoại', name: 'phone', type: 'text', placeholder: '0123 456 789' },
            ].map(({ label, name, type, placeholder }) => (
              <Form.Group key={name} className="mb-3">
                <Form.Label>{label}</Form.Label>
                <Form.Control
                  type={type}
                  name={name}
                  value={formData[name]}
                  onChange={handleInputChange}
                  placeholder={placeholder}
                />
              </Form.Group>
            ))}

            <Form.Group className="mb-3">
              <Form.Label>Địa chỉ nhận hàng</Form.Label>
              <Form.Control
                as="textarea"
                rows={3}
                name="address"
                value={formData.address}
                onChange={handleInputChange}
                placeholder="Số nhà, đường, phường/xã, quận/huyện, tỉnh/thành"
              />
            </Form.Group>

            <Form.Group className="mb-3">
              <Form.Label>Phương thức thanh toán</Form.Label>
              <Form.Select
                name="payment_method"
                value={formData.payment_method}
                onChange={handleInputChange}
              >
                <option value="cod">Thanh toán khi nhận hàng</option>
                <option value="bank">Chuyển khoản</option>
              </Form.Select>
            </Form.Group>

            <h5 className="mt-4">
              Tổng tiền: <span className="text-danger">{formatPrice(total)}</span>
            </h5>

            <Button type="submit" variant="success" className="mt-3 w-100">
              Xác nhận đặt hàng
            </Button>
          </Form>
        </Col>
      </Row>
    </Container>
  );
};

export default CheckoutPage;
