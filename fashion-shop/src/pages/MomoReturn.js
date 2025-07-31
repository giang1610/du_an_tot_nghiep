// src/pages/MomoReturn.jsx
import React, { useEffect, useState } from 'react';
import { useSearchParams, useNavigate } from 'react-router-dom';
import { Container, Spinner, Alert, Row, Col, Card, Button, Badge, Image } from 'react-bootstrap';
import axios from 'axios';
import { useCart } from '../context/CartContext';
import { ArrowLeft } from 'react-bootstrap-icons';

export default function MomoReturn() {
  const [searchParams] = useSearchParams();
  const navigate = useNavigate();
  const { removeSelectedItems } = useCart();

  const [loading, setLoading] = useState(true);
  const [result, setResult] = useState(null);
  const [error, setError] = useState('');

  useEffect(() => {
    const fetchResult = async () => {
      const orderId = searchParams.get('orderId');
      const resultCode = searchParams.get('resultCode');

      if (!orderId || !resultCode) {
        setError('URL không hợp lệ');
        setLoading(false);
        return;
      }

      try {
        const res = await axios.get(`http://localhost:8000/api/payment/momo/return?orderId=${orderId}&resultCode=${resultCode}`);
        setResult(res.data);

        if (res.data?.data?.payment_status === 'paid') {
          await removeSelectedItems();
          localStorage.removeItem('buy_now');
        }
      } catch (err) {
        setError(err.response?.data?.message || 'Lỗi xác minh thanh toán');
      } finally {
        setLoading(false);
      }
    };

    fetchResult();
  }, [searchParams]);

  if (loading) {
    return <div className="text-center mt-5"><Spinner animation="border" /></div>;
  }

  if (error) {
    return <Alert variant="danger" className="mt-4 text-center">{error}</Alert>;
  }

  const order = result?.data;
  const items = order?.items || [];

  return (
    <Container className="py-5">
      <div className="text-center">
        <h3 className={order?.payment_status === 'paid' ? 'text-success' : 'text-danger'}>
          {result?.message}
        </h3>
      </div>

      <Row className="mt-4">
        <Col md={6}>
          <ul className="list-group shadow-sm">
            <li className="list-group-item d-flex justify-content-between">
              <strong>Mã đơn hàng:</strong><span>{order?.order_number}</span>
            </li>
            <li className="list-group-item d-flex justify-content-between">
              <strong>Trạng thái:</strong>
              <Badge bg={order?.status === 'processing' ? 'primary' : 'secondary'}>
                {order?.status}
              </Badge>
            </li>
            <li className="list-group-item d-flex justify-content-between">
              <strong>Thanh toán:</strong>
              <Badge bg={order?.payment_status === 'paid' ? 'success' : 'danger'}>
                {order?.payment_status}
              </Badge>
            </li>
          </ul>
        </Col>

        <Col md={6}>
          <h5 className="mb-3">Sản phẩm</h5>
          {items.length === 0 ? (
            <p>Không có sản phẩm nào trong đơn hàng.</p>
          ) : (
            items.map((item, idx) => {
              const price = (item.sale_price ?? item.price) * item.quantity;
              return (
                <Card key={idx} className="mb-3 shadow-sm">
                  <Card.Body>
                    <Row>
                      <Col xs={3}>
                        <Image
                          src={item?.product_variant?.product?.img || '/placeholder.png'}
                          alt={item?.product_variant?.product?.name}
                          fluid
                          rounded
                        />
                      </Col>
                      <Col xs={9}>
                        <h6>{item?.product_variant?.product?.name}</h6>
                        <p className="mb-1">Màu: {item?.product_variant?.color?.name} | Size: {item?.product_variant?.size?.name}</p>
                        <p className="mb-1">Số lượng: {item.quantity}</p>
                        <p className="text-danger mb-0">
                          Giá: {price.toLocaleString()} ₫
                        </p>
                      </Col>
                    </Row>
                  </Card.Body>
                </Card>
              );
            })
          )}
        </Col>
      </Row>

      <div className="text-center mt-4">
        <Button variant="outline-primary" onClick={() => navigate('/orders')}>
          <ArrowLeft className="me-2" />
          Quay về đơn hàng của tôi
        </Button>
      </div>
    </Container>
  );
}
