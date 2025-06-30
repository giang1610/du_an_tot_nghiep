import axios from 'axios';
import React, { useEffect, useState } from 'react';
import { useLocation } from 'react-router-dom';

const MomoReturn = () => {
  const location = useLocation();
  const [message, setMessage] = useState('Đang xác minh kết quả...');
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    let isMounted = true;

    const verifyPayment = async () => {
      const params = new URLSearchParams(location.search);
      const orderId = params.get('orderId');
      const resultCode = params.get('resultCode');

      if (!orderId || !resultCode) {
        if (isMounted) {
          setMessage('Thông tin không hợp lệ.');
          setLoading(false);
        }
        return;
      }

      try {
        const url = `http://localhost:8000/api/payment/momo-return?orderId=${encodeURIComponent(orderId)}&resultCode=${encodeURIComponent(resultCode)}`;
        const res = await axios.get(url);
        if (isMounted) {
          setMessage(res.data.message || 'Xác minh thành công.');
        }
      } catch {
        if (isMounted) {
          setMessage('Không thể xác minh kết quả thanh toán.');
        }
      } finally {
        if (isMounted) setLoading(false);
      }
    };

    verifyPayment();

    return () => {
      isMounted = false;
    };
  }, [location.search]);

  return (
    <div className="container text-center py-5">
      <h3>Kết quả thanh toán MoMo</h3>
      {loading ? <div className="spinner-border text-primary" role="status"><span className="visually-hidden">Loading...</span></div> : <p>{message}</p>}
    </div>
  );
};

export default MomoReturn;
