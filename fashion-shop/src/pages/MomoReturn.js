import axios from 'axios';
import React, { useEffect, useState } from 'react';
import { useLocation, useNavigate } from 'react-router-dom';

const MomoReturn = () => {
  const location = useLocation();
  const navigate = useNavigate();
  const [message, setMessage] = useState('Đang xác minh kết quả...');
  const [order, setOrder] = useState(null);
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
        const url = `http://localhost:8000/api/payment/momo/return?orderId=${encodeURIComponent(orderId)}&resultCode=${encodeURIComponent(resultCode)}`;
        const res = await axios.get(url);
        if (isMounted) {
          setMessage(res.data.message || 'Xác minh thành công.');
          setOrder(res.data.data); // lưu lại thông tin đơn hàng
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

  const handleBack = () => {
    navigate('/orders');
  };

  return (
    <div className="container py-5" style={{maxWidth: 600}}>
      <h3 className="mb-4 text-center">Kết quả thanh toán MoMo</h3>
      {loading ? (
        <div className="d-flex justify-content-center align-items-center" style={{height: 150}}>
          <div className="spinner-border text-primary" role="status">
            <span className="visually-hidden">Loading...</span>
          </div>
        </div>
      ) : (
        <>
          <div className="alert alert-info text-center">{message}</div>
          {order ? (
            <div className="card shadow-sm mb-4">
              <div className="card-body">
                <h5 className="card-title mb-3">Thông tin đơn hàng</h5>
                <p><strong>Mã đơn hàng:</strong> #{order.order_number}</p>
                <p>
                  <strong>Trạng thái thanh toán:</strong>{' '}
                  {order.payment_status === 'paid' ? (
                    <span className="badge bg-success">Đã thanh toán</span>
                  ) : (
                    <span className="badge bg-warning text-dark">Chưa thanh toán</span>
                  )}
                </p>
              </div>
            </div>
          ) : (
            <div className="alert alert-warning">Không tìm thấy thông tin đơn hàng.</div>
          )}
          <div className="text-center">
            <button className="btn btn-primary" onClick={handleBack}>
              Quay lại trang đơn hàng
            </button>
          </div>
        </>
      )}
    </div>
  );
};

export default MomoReturn;