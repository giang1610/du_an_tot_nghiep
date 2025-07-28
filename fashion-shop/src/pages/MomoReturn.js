// src/pages/MomoReturn.jsx
import React, { useEffect, useState } from 'react';
import axios from 'axios';
import { Spinner, Alert, Button } from 'react-bootstrap';
import { useSearchParams, useNavigate } from 'react-router-dom';

export default function MomoReturn() {
  const [searchParams] = useSearchParams();
  const [loading, setLoading] = useState(true);
  const [result, setResult] = useState(null);
  const [error, setError] = useState('');
  const navigate = useNavigate();

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

  return (
    <div className="container my-5" style={{ maxWidth: 600 }}>
      <h4>{result?.message}</h4>
      <p>Mã đơn hàng: <strong>{result?.data?.order_number}</strong></p>
      <p>Trạng thái: <strong>{result?.data?.status}</strong></p>
      <p>Thanh toán: <strong>{result?.data?.payment_status}</strong></p>

      <Button onClick={() => navigate('/')}>Về trang chủ</Button>
    </div>
  );
}
