// src/components/payment/PaymentVNPay.jsx
import React, { useState } from 'react';
import axios from 'axios';

const PaymentVNPay = ({ shippingAddress, billingAddress, customerPhone, notes }) => {
  const [loading, setLoading] = useState(false);

  const handleVnpayPayment = async () => {
    setLoading(true);
    try {
      const token = localStorage.getItem('token');

      const response = await axios.post(
        `${process.env.REACT_APP_API_URL}/vnpay/pay`,
        {
          shipping_address: shippingAddress,
          billing_address: billingAddress || shippingAddress,
          customer_phone: customerPhone,
          notes: notes || '',
        },
        {
          headers: {
            Authorization: `Bearer ${token}`,
          },
        }
      );

      const { payment_url } = response.data.data;
      if (payment_url) {
        window.location.href = payment_url;
      } else {
        alert('Không nhận được liên kết thanh toán từ VNPay');
      }
    } catch (error) {
      console.error('Lỗi thanh toán VNPay:', error.response?.data || error.message);
      alert(error.response?.data?.message || 'Không thể khởi tạo thanh toán VNPay.');
    } finally {
      setLoading(false);
    }
  };

  return (
    <button
      className="btn btn-primary w-100 mt-3"
      onClick={handleVnpayPayment}
      disabled={loading}
    >
      {loading ? 'Đang chuyển hướng...' : 'Thanh toán với VNPay'}
    </button>
  );
};

export default PaymentVNPay;
