import React, { useEffect, useState } from 'react';
import { useParams, useNavigate } from 'react-router-dom';
import axios from 'axios';
import { Spinner } from 'react-bootstrap';

const ContinuePayment = () => {
    const { method, orderId } = useParams(); // 'momo' hoặc 'vnpay'
    const navigate = useNavigate();
    const [message, setMessage] = useState('Đang chuyển hướng đến cổng thanh toán...');
    const [error, setError] = useState(false);
    const [gone, setGone] = useState(false);

    useEffect(() => {
        const continuePayment = async () => {
            try {
                const token = localStorage.getItem('token');
                const config = {
                    headers: {
                        'Content-Type': 'application/json',
                        ...(token ? { Authorization: `Bearer ${token}` } : {}),
                    },
                };

                let endpoint = '';
                if (method === 'momo') {
                    endpoint = '/momo/retry-payment';
                } else if (method === 'vnpay') {
                    endpoint = '/vnpay/retry-payment';
                } else {
                    setError(true);
                    setMessage('Phương thức thanh toán không hợp lệ.');
                    return;
                }

                const response = await axios.post(
                    `${process.env.REACT_APP_API_URL}${endpoint}`,
                    { order_id: orderId },
                    config
                );

                const paymentUrl = response.data?.data?.payment_url;
                if (paymentUrl) {
                    window.location.href = paymentUrl;
                } else {
                    setError(true);
                    setMessage('Không thể tạo lại liên kết thanh toán.');
                }
            } catch (err) {
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

        continuePayment();
    }, [method, orderId]);

    return (
        <div className="container mt-5 text-center">
            <h4>{message}</h4>

            {!error && !gone && (
                <div className="mt-3">
                    <Spinner animation="border" variant="primary" />
                </div>
            )}

            {(error || gone) && (
                <button onClick={() => navigate('/orders')} className="btn btn-primary mt-3">
                    Quay lại danh sách đơn hàng
                </button>
            )}
        </div>
    );
};

export default ContinuePayment;