import React from 'react';
import { Container, Table, Button, Form } from 'react-bootstrap';
import { useCart } from '../context/CartContext';
import { useNavigate } from 'react-router-dom';

const CartPage = () => {
  const { cart, updateQuantity, removeFromCart, total } = useCart();
  const navigate = useNavigate();

  return (
    <Container className="my-5">
      <h2 className="mb-4 text-center">Giỏ Hàng</h2>

      {cart.length === 0 ? (
        <p className="text-center">Giỏ hàng của bạn đang trống.</p>
      ) : (
        <>
          <Table bordered hover responsive>
            <thead>
              <tr>
                <th>Sản phẩm</th>
                <th>Giá</th>
                <th>Số lượng</th>
                <th>Tổng</th>
                <th>Hành động</th>
              </tr>
            </thead>
            <tbody>
              {cart.map(item => (
                <tr key={item.id}>
                  <td>{item.name}</td>
                  <td>{item.price.toLocaleString()} đ</td>
                  <td>
                    <Form.Control
                      type="number"
                      min="1"
                      value={item.quantity}
                      onChange={(e) => updateQuantity(item.id, parseInt(e.target.value))}
                      style={{ width: '80px' }}
                    />
                  </td>
                  <td>{(item.price * item.quantity).toLocaleString()} đ</td>
                  <td>
                    <Button variant="danger" size="sm" onClick={() => removeFromCart(item.id)}>Xóa</Button>
                  </td>
                </tr>
              ))}
            </tbody>
          </Table>

          <div className="text-end fs-5 fw-bold mb-4">
            Tổng cộng: {total.toLocaleString()} đ
          </div>

          <div className="text-end">
            <Button variant="success" onClick={() => navigate('/checkout')}>
              Thanh Toán
            </Button>
          </div>
        </>
      )}
    </Container>
  );
};

export default CartPage;
