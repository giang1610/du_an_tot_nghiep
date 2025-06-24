import React, { useState } from 'react';
import { Container, Row, Col, Card, Button, Form } from 'react-bootstrap';
import { useCart } from '../context/CartContext';
import { useNavigate } from 'react-router-dom';

const CheckoutPage = () => {
  const { cart, total, removeFromCart, updateQuantity, clearCart } = useCart();
  const navigate = useNavigate();

  const [formData, setFormData] = useState({
    name: '',
    address: '',
    phone: '',
  });

  const handleInputChange = (e) => {
    setFormData((prev) => ({
      ...prev,
      [e.target.name]: e.target.value,
    }));
  };

  const handleSubmit = (e) => {
    e.preventDefault();

    if (!formData.name || !formData.address || !formData.phone) {
      alert('Vui lòng điền đầy đủ thông tin!');
      return;
    }

    // Bạn có thể gửi API đặt hàng ở đây
    alert('Đặt hàng thành công!');
    clearCart();
    navigate('/');
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
          {cart.map((item) => (
            <Card key={item.id} className="mb-3 shadow-sm border-0">
              <Card.Body className="d-flex align-items-center">
                <img
                  src={item.image}
                  alt={item.name}
                  style={{ width: 80, height: 80, objectFit: 'contain' }}
                  className="me-3 border rounded"
                />
                <div className="flex-grow-1">
                  <h6>{item.name}</h6>
                  <p className="mb-1 text-muted">
                    Kích cỡ: {item.size}, Màu sắc: {item.color}
                  </p>
                  <div className="d-flex align-items-center gap-2">
                    <Form.Control
                      type="number"
                      value={item.quantity}
                      onChange={(e) => updateQuantity(item.id, parseInt(e.target.value))}
                      style={{ width: 80 }}
                      min={1}
                    />
                    <span>{(item.price * item.quantity).toLocaleString()}₫</span>
                  </div>
                </div>
                <Button
                  variant="outline-danger"
                  size="sm"
                  onClick={() => removeFromCart(item.id)}
                >
                  Xóa
                </Button>
              </Card.Body>
            </Card>
          ))}
        </Col>

        {/* Thông tin đặt hàng */}
        <Col md={4}>
          <h4 className="mb-4">Thông tin đặt hàng</h4>
          <Form onSubmit={handleSubmit}>
            <Form.Group className="mb-3">
              <Form.Label>Họ tên</Form.Label>
              <Form.Control
                type="text"
                name="name"
                value={formData.name}
                onChange={handleInputChange}
                placeholder="Nguyễn Văn A"
              />
            </Form.Group>

            <Form.Group className="mb-3">
              <Form.Label>Số điện thoại</Form.Label>
              <Form.Control
                type="text"
                name="phone"
                value={formData.phone}
                onChange={handleInputChange}
                placeholder="0123 456 789"
              />
            </Form.Group>

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

            <h5 className="mt-4">Tổng tiền: <span className="text-danger">{total.toLocaleString()}₫</span></h5>

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
