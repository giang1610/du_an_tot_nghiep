// src/pages/VnpayReturn.js
import { useEffect, useState } from 'react';
import { useSearchParams } from 'react-router-dom';
import { Container, Alert, Spinner } from 'react-bootstrap';

export default function VnpayReturn() {
  const [searchParams] = useSearchParams();
  const [status, setStatus] = useState('loading');

  useEffect(() => {
    const message = searchParams.get('message');
    const orderId = searchParams.get('order_id');
    const orderNumber = searchParams.get('order_number');
    const paymentStatus = searchParams.get('payment_status');

    if (paymentStatus === 'paid') {
      setStatus('success');
    } else {
      setStatus('failed');
    }
  }, [searchParams]);

  return (
    <Container className="py-5 text-center">
      {status === 'loading' && (
        <>
          <Spinner animation="border" />
          <p>Đang kiểm tra trạng thái thanh toán...</p>
        </>
      )}
      {status === 'success' && (
        <Alert variant="success">
          🎉 Thanh toán VNPay thành công! Đơn hàng của bạn đang được xử lý.
        </Alert>
      )}
      {status === 'failed' && (
        <Alert variant="danger">
          ❌ Thanh toán thất bại hoặc bị hủy. Vui lòng thử lại.
        </Alert>
      )}
    </Container>
  );
}
