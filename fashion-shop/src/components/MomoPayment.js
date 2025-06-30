import axios from 'axios';
import React, { useState } from 'react';

const MomoPayment = ({ cartItems, shippingAddress, customerPhone, notes }) => {
  const [loading, setLoading] = useState(false);

  const calculateTotal = () => {
    let subtotal = 0;
    cartItems.forEach((item) => {
      subtotal += item.price * item.quantity;
    });
    return subtotal;
  };

  const handleMomoPayment = async () => {
    setLoading(true);
    try {
      const token = localStorage.getItem('token');

      const subtotal = calculateTotal();
      const total = subtotal; // nếu chưa tính thêm thuế, phí ship

      const response = await axios.post(
        `${process.env.REACT_APP_API_URL}/payment/momo`,
        {
          shipping_address: shippingAddress,
          customer_phone: customerPhone,
          customer_email: '', // có thể thêm nếu cần
          payment_method: 'momo',
          subtotal,
          total,
          tax: 0,
          shipping: 0,
          notes,
          items: cartItems.map((item) => ({
            product_variant_id: item.product_variant_id,
            quantity: item.quantity,
          })),
        },
        {
          headers: {
            Authorization: `Bearer ${token}`,
          },
        }
      );

      const paymentUrl = response.data.data?.payUrl;

      if (paymentUrl) {
        window.location.href = paymentUrl;
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
