// src/pages/CartPage.jsx
import React from 'react';
import {
  Container,
  Table,
  Button,
  Form,
  Row,
  Col,
  Alert,
} from 'react-bootstrap';
import { useCart } from '../context/CartContext';
import { useNavigate } from 'react-router-dom';

const formatPrice = (price) =>
  new Intl.NumberFormat('vi-VN').format(price) + ' đ';

const CartPage = () => {
  const { cart, updateQuantity, removeFromCart, total } = useCart();
  const navigate = useNavigate();

  const handleQuantityChange = (id, value) => {
    const quantity = Math.max(1, parseInt(value) || 1);
    updateQuantity(id, quantity);
  };

  if (!Array.isArray(cart) || cart.length === 0) {
    return (
      <Container className="my-5">
        <h2 className="mb-4 text-center fw-bold">
          <span role="img" aria-label="cart">🛒</span> Giỏ Hàng
        </h2>
        <Alert variant="info" className="text-center">
          Giỏ hàng của bạn đang trống.
        </Alert>
      </Container>
    );
  }

  return (
    <Container className="my-5">
      <h2 className="mb-4 text-center fw-bold">
        <span role="img" aria-label="cart">🛒</span> Giỏ Hàng
      </h2>

      <div className="table-responsive">
        <Table bordered hover className="align-middle text-center">
          <thead className="table-light">
            <tr>
              <th>Hình ảnh</th>
              <th>Sản phẩm</th>
              <th>Giá</th>
              <th>Số lượng</th>
              <th>Tổng</th>
              <th>Hành động</th>
            </tr>
          </thead>
          <tbody>
            {cart.map(({ id, name, image, price, quantity, size, color }) => (
              <tr key={id}>
                <td>
                  <img
                    src={image}
                    alt={name}
                    className="img-thumbnail"
                    style={{ width: 60, height: 60, objectFit: 'cover' }}
                  />
                </td>
                <td className="text-start">
                  <div className="fw-semibold">{name}</div>
                  <div className="text-muted small">
                    {color && <>Màu: {color}</>}
                    {color && size && ' | '}
                    {size && <>Size: {size}</>}
                  </div>
                </td>
                <td className="text-danger fw-semibold">{formatPrice(price)}</td>
                <td>
                  <Form.Control
                    type="number"
                    min="1"
                    value={quantity}
                    onChange={(e) => handleQuantityChange(id, e.target.value)}
                    className="text-center mx-auto"
                    style={{ width: 80 }}
                  />
                </td>
                <td className="fw-bold">{formatPrice(price * quantity)}</td>
                <td>
                  <Button
                    variant="outline-danger"
                    size="sm"
                    onClick={() => removeFromCart(id)}
                  >
                    Xóa
                  </Button>
                </td>
              </tr>
            ))}
          </tbody>
        </Table>
      </div>

      <Row className="justify-content-end mt-4">
        <Col xs="12" md="6" lg="4">
          <div className="border rounded p-3 shadow-sm bg-light">
            <h5 className="fw-bold text-end">Tổng cộng:</h5>
            <h4 className="text-danger text-end mb-3">{formatPrice(total)}</h4>
            <Button
              variant="success"
              size="lg"
              className="w-100"
              onClick={() => navigate('/checkout')}
            >
              Tiến hành thanh toán
            </Button>
          </div>
        </Col>
      </Row>
    </Container>
  );
};

export default CartPage;
