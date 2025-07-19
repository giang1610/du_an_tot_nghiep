import axios from 'axios';
import React, { useState } from 'react';

const MomoPayment = ({ shippingAddress, billingAddress, customerPhone, notes }) => {
  const [loading, setLoading] = useState(false);

  const handleMomoPayment = async () => {
    setLoading(true);
    try {
      const token = localStorage.getItem('token');

      const response = await axios.post(
        `${process.env.REACT_APP_API_URL}/payment/momo`,
        {
          shipping_address: shippingAddress,
          billing_address: billingAddress,
          customer_phone: customerPhone,
          notes: notes,
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
        alert('Không nhận được liên kết thanh toán từ MoMo');
      }
    } catch (error) {
      console.error(error);
      alert('Lỗi khi tạo thanh toán MoMo');
    } finally {
      setLoading(false);
    }
  };

  return (
    <button
      className="btn btn-danger"
      onClick={handleMomoPayment}
      disabled={loading}
    >
      {loading ? 'Đang chuyển hướng...' : 'Thanh toán với MoMo'}
    </button>
  );
};

export default MomoPayment;
