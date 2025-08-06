import React, { useEffect, useState } from 'react';
import { useParams, useNavigate } from 'react-router-dom';
import axios from 'axios';
import { Spinner } from 'react-bootstrap';

const ContinuePaymentMomo = () => {
  const { orderId } = useParams();
  const navigate = useNavigate();
  const [message, setMessage] = useState('Đang chuyển hướng đến Momo...');
  const [error, setError] = useState(false);
  const [gone, setGone] = useState(false);

  useEffect(() => {
    const retryPayment = async () => {
      if (!orderId) {
        setError(true);
        setMessage('Không có orderId.');
        return;
      }

      try {
        const token = localStorage.getItem('token');
        const config = {
          headers: {
            'Content-Type': 'application/json',
            ...(token ? { Authorization: `Bearer ${token}` } : {}),
          },
        };

        const res = await axios.post(
          `${process.env.REACT_APP_API_URL}/momo/retry-payment`,
          { order_id: orderId },
          config
        );

        console.log('momo retry-payment response:', res?.data);

        const paymentRaw = res.data?.data?.payment_url;
        // thử nhiều key khả dĩ nếu API trả object
        let paymentUrl = null;
        if (typeof paymentRaw === 'string') {
          paymentUrl = paymentRaw;
        } else if (paymentRaw && typeof paymentRaw === 'object') {
          paymentUrl =
            paymentRaw.payUrl ||
            paymentRaw.url ||
            paymentRaw.redirect_url ||
            paymentRaw.redirect ||
            paymentRaw.deeplink ||
            null;
        }

        if (paymentUrl && typeof paymentUrl === 'string') {
          // dùng replace nếu không muốn back lại trang này tạo vòng lặp
          window.location.replace(paymentUrl);
          return;
        }

        setError(true);
        setMessage('Không thể tạo liên kết thanh toán Momo (URL không hợp lệ).');
        console.error('Invalid payment_url returned from API:', paymentRaw);
      } catch (err) {
        console.error('retryPayment error:', err?.response ?? err);
        if (err.response?.status === 410) {
          setGone(true);
          setMessage(err.response.data.message || 'Đơn hàng đã hết hạn thanh toán.');
        } else if (err.response?.status === 401) {
          setError(true);
          setMessage('Bạn cần đăng nhập để tiếp tục thanh toán.');
        } else {
          setError(true);
          setMessage(err.response?.data?.message || 'Đã xảy ra lỗi khi tiếp tục thanh toán.');
        }
      }
    };

    retryPayment();
  }, [orderId]);

  return (
    <div className="container mt-5 text-center">
      <h4>{message}</h4>
      {!error && !gone && <Spinner animation="border" variant="primary" />}
      {(error || gone) && (
        <button onClick={() => navigate('/orders')} className="btn btn-primary mt-3">
          Quay lại danh sách đơn hàng
        </button>
      )}
    </div>
  );
};

export default ContinuePaymentMomo;
