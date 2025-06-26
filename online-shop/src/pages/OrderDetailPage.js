import React, { useEffect, useState } from 'react';
import { useParams, useNavigate } from 'react-router-dom';
import axios from 'axios';
import { Container, Row, Col, Card, Button, Spinner, Alert } from 'react-bootstrap';
import { useAuth } from '../context/AuthContext';

const OrderDetailPage = () => {
  const { id } = useParams();
  const { user } = useAuth();
  const navigate = useNavigate();
  const [order, setOrder] = useState(null);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState('');

  useEffect(() => {
    const fetchOrder = async () => {
      const token =
        user?.token ||
        localStorage.getItem('token') ||
        sessionStorage.getItem('token');

      if (!token) {
        setError('Không có quyền truy cập. Vui lòng đăng nhập lại.');
        setLoading(false);
        return;
      }

      try {
        const res = await axios.get(`${process.env.REACT_APP_API_URI}/orders/${id}`, {
          headers: {
            Authorization: `Bearer ${token}`,
          },
        });

        setOrder(res.data.order);
      } catch (err) {
        const msg =
          err?.response?.data?.message || 'Không thể tải thông tin đơn hàng.';
        setError(msg);
        console.error('Lỗi API:', err);
      } finally {
        setLoading(false);
      }
    };

    fetchOrder();
  }, [id, user]);

  if (loading) {
    return (
      <Container className="my-5 text-center">
        <Spinner animation="border" />
        <p className="mt-2">Đang tải thông tin đơn hàng...</p>
      </Container>
    );
  }

  if (error) {
    return (
      <Container className="my-5 text-center">
        <Alert variant="danger">{error}</Alert>
        <Button variant="primary" onClick={() => navigate('/orders')}>
          Quay lại danh sách đơn hàng
        </Button>
      </Container>
    );
  }

  if (!order) return null;

  return (
    <Container className="my-5">
      <h3>Đơn hàng #{order.order_number}</h3>
      <p>Ngày đặt: {new Date(order.created_at).toLocaleString()}</p>
      <p>
        Trạng thái: <strong>{order.status}</strong>
      </p>
      <hr />

      <Row>
        <Col md={8}>
          <h5>Sản phẩm đã đặt</h5>
          {order.items.map((item) => (
            <Card key={item.id} className="mb-3 shadow-sm border-0">
              <Card.Body className="d-flex align-items-center">
                <img
                  src={
                    item.product_variant?.product?.image || '/no-image.jpg'
                  }
                  alt={item.product_variant?.product?.name || 'Sản phẩm'}
                  style={{ width: 80, height: 80, objectFit: 'cover' }}
                  className="me-3 border rounded"
                />
                <div>
                  <h6>{item.product_variant?.product?.name}</h6>
                  <p className="mb-1 text-muted">
                    Kích cỡ: {item.size?.name || 'Không có'}, Màu sắc: {item.color?.name || 'Không có'}
                  </p>


                  <p className="mb-0">Số lượng: {item.quantity}</p>
                  <p className="mb-0">
                    Giá: {item.price.toLocaleString()}₫
                  </p>
                </div>
              </Card.Body>
            </Card>
          ))}
        </Col>

        <Col md={4}>
          <h5>Thông tin giao hàng</h5>
          <p>
            <strong>Họ tên:</strong> {order.name}
          </p>
          <p>
            <strong>Số điện thoại:</strong> {order.customer_phone}
          </p>
          <p>
            <strong>Email:</strong> {order.customer_email}
          </p>
          <p>
            <strong>Địa chỉ:</strong> {order.shipping_address}
          </p>
          <hr />
          <h5>
            Tổng tiền:{' '}
            <span className="text-danger">
              {order.total.toLocaleString()}₫
            </span>
          </h5>
          <Button variant="primary" onClick={() => navigate('/orders')}>
            Quay lại đơn hàng
          </Button>
        </Col>
      </Row>
    </Container>
  );
};

export default OrderDetailPage;
