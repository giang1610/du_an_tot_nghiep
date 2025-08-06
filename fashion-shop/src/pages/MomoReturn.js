// src/pages/MomoReturn.jsx
import React, { useEffect, useState } from 'react';
import axios from 'axios';
import { Spinner, Alert, Button, Card, Row, Col, Image } from 'react-bootstrap';
import { useSearchParams, useNavigate } from 'react-router-dom';
import { useCart } from '../context/CartContext';

const formatCurrency = (num) => (num ?? 0).toLocaleString();

export default function MomoReturn() {
  const [searchParams] = useSearchParams();
  const [loading, setLoading] = useState(true);
  const [result, setResult] = useState(null);
  const [error, setError] = useState('');
  const navigate = useNavigate();
  const { removeSelectedItems } = useCart();

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
        console.log('Kết quả API:', res.data);
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

  if (loading) return <div className="text-center my-5"><Spinner animation="border" /></div>;

  if (error) return <Alert variant="danger">{error}</Alert>;

  const items = result?.data?.items || [];
  const total = parseInt(result?.data?.total || 0);

  return (
    <div className="container my-5" style={{ maxWidth: '1100px' }}>
      <h4 className="mb-4 text-success">{result?.message}</h4>
      <Row>
        {/* Cột trái - Sản phẩm */}
        <Col md={8} className="text-start">
          <h5>Sản phẩm trong đơn hàng</h5>
          {items.length === 0 ? (
            <p>Không có sản phẩm.</p>
          ) : (
            <div>
              {items.map((item, idx) => (
                <Card key={idx} className="mb-3 shadow-sm">
                  <Card.Body>
                    <Row>
                      <Col xs={3} md={2}>
                        <Image
                          src={item?.product_variant?.product?.img || '/placeholder.png'}
                          alt={item?.product_variant?.product?.name}
                          fluid
                          rounded
                        />
                      </Col>
                      <Col xs={9} md={10}>
                        <h6>{item?.product_variant?.product?.name}</h6>
                        <p className="mb-1">
                          Màu: {item?.product_variant?.color?.name} | Size: {item?.product_variant?.size?.name}
                        </p>
                        <p className="mb-1">Số lượng: {item.quantity}</p>
                        <p className="mb-0 text-danger">
                          Giá: {formatCurrency(total)}₫
                        </p>
                      </Col>
                    </Row>
                  </Card.Body>
                </Card>
              ))}
            </div>
          )}
        </Col>

        {/* Cột phải - Thông tin đơn hàng */}
        <Col md={4}>
          <Card className="mb-4">
            <Card.Body>
              <p>Mã đơn hàng: <strong>{result?.data?.order_number}</strong></p>
              <p>Trạng thái đơn hàng: <strong className="text-primary">{result?.data?.status}</strong></p>
              <p>Trạng thái thanh toán: <strong>
                {result?.data?.payment_status === 'paid' ? (
                  <span className="text-success">Đã thanh toán</span>
                ) : (
                  <span className="text-danger">Chưa thanh toán</span>
                )}
              </strong></p>
              <hr />
              <p><strong>Tổng cộng:</strong> {formatCurrency(total)}₫</p>
              <div className="text-center mt-3">
                <Button variant="primary" onClick={() => navigate('/')}>Về trang chủ</Button>
              </div>
            </Card.Body>
          </Card>
        </Col>
      </Row>
    </div>
  );
}