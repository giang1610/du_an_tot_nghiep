// components/ShippingForm.js
import React, { useState } from 'react';
import axios from 'axios';

const ShippingForm = () => {
  const [formData, setFormData] = useState({
    shipping_address: '',
    billing_address: '',
    customer_phone: '',
    notes: '',
  });

  const [loading, setLoading] = useState(false);

  const handleChange = e => {
    setFormData(prev => ({ ...prev, [e.target.name]: e.target.value }));
  };

  const handleSubmit = async e => {
    e.preventDefault();
    setLoading(true);
    try {
      const token = localStorage.getItem('token'); // JWT token
      const res = await axios.post(
        '/api/vnpay/process-payment',
        formData,
        {
          headers: {
            Authorization: `Bearer ${token}`
          }
        }
      );

      const { payment_url } = res.data.data;
      window.location.href = payment_url; // redirect to VNPay
    } catch (error) {
      console.error(error.response?.data || error.message);
      alert('Lỗi khi khởi tạo thanh toán VNPay.');
    } finally {
      setLoading(false);
    }
  };

  return (
    <form onSubmit={handleSubmit} className="p-4 border rounded">
      <h2 className="text-xl font-bold mb-4">Thông tin giao hàng</h2>

      <input
        name="shipping_address"
        placeholder="Địa chỉ giao hàng"
        required
        className="block w-full mb-2 p-2 border"
        onChange={handleChange}
      />
      <input
        name="billing_address"
        placeholder="Địa chỉ thanh toán (có thể bỏ trống)"
        className="block w-full mb-2 p-2 border"
        onChange={handleChange}
      />
      <input
        name="customer_phone"
        placeholder="Số điện thoại"
        required
        className="block w-full mb-2 p-2 border"
        onChange={handleChange}
      />
      <textarea
        name="notes"
        placeholder="Ghi chú"
        className="block w-full mb-4 p-2 border"
        onChange={handleChange}
      ></textarea>

      <button
        type="submit"
        disabled={loading}
        className="bg-blue-600 text-white px-4 py-2 rounded"
      >
        {loading ? 'Đang xử lý...' : 'Thanh toán với VNPay'}
      </button>
    </form>
  );
};

export default ShippingForm;
