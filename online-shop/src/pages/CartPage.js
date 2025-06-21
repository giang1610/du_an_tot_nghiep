import React from 'react';
import { Container, Table, Button, Form, Row, Col, Alert } from 'react-bootstrap';
import { useCart } from '../context/CartContext';
import { useNavigate } from 'react-router-dom';

const CartPage = () => {
  const { cart, updateQuantity, removeFromCart, total } = useCart();
  const navigate = useNavigate();

  const formatPrice = (price) =>
    new Intl.NumberFormat('vi-VN').format(price) + ' đ';

  return (
    <Container className="my-5">
      <h2 className="mb-4 text-center fw-bold">
        <span role="img" aria-label="cart">🛒</span> Giỏ Hàng
      </h2>

      {cart.length === 0 ? (
        <Alert variant="info" className="text-center">
          Giỏ hàng của bạn đang trống.
        </Alert>
      ) : (
        <>
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
                {cart.map((item) => {
                  const lineTotal = item.price * item.quantity;
                  return (
                    <tr key={item.id}>
                      <td>
                        <img
                          src={item.image}
                          alt={item.name}
                          className="img-thumbnail"
                          style={{ width: 60, height: 60, objectFit: 'cover' }}
                        />
                      </td>
                      <td className="text-start">
                        <div className="fw-semibold">{item.name}</div>
                        <div className="text-muted small">
                          {item.color && <>Màu: {item.color}</>}{" "}
                          {item.size && <>| Size: {item.size}</>}
                        </div>
                      </td>
                      <td className="text-danger fw-semibold">
                        {formatPrice(item.price)}
                      </td>
                      <td>
                        <Form.Control
                          type="number"
                          min="1"
                          value={item.quantity}
                          onChange={(e) =>
                            updateQuantity(item.id, parseInt(e.target.value) || 1)
                          }
                          className="text-center mx-auto"
                          style={{ width: 80 }}
                        />
                      </td>
                      <td className="fw-bold">{formatPrice(lineTotal)}</td>
                      <td>
                        <Button
                          variant="outline-danger"
                          size="sm"
                          onClick={() => removeFromCart(item.id)}
                        >
                          Xóa
                        </Button>
                      </td>
                    </tr>
                  );
                })}
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
        </>
      )}
    </Container>
  );
};

export default CartPage;
