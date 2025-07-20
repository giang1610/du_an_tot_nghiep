// pages/VnpayReturnPage.js
import React, { useEffect, useState } from 'react';
import axios from 'axios';

const VnpayReturnPage = () => {
  const [result, setResult] = useState(null);

  useEffect(() => {
    const fetchReturn = async () => {
      try {
        const res = await axios.get(`/api/vnpay/return${window.location.search}`);
        setResult({ success: true, data: res.data });
      } catch (err) {
        setResult({ success: false, error: err.response?.data || err.message });
      }
    };

    fetchReturn();
  }, []);

  if (!result) return <div>Đang xử lý kết quả thanh toán...</div>;

  return (
    <div className="p-4">
      {result.success ? (
        <div className="bg-green-100 p-4 rounded">
          <h2 className="text-xl font-bold text-green-800">✅ Thanh toán thành công!</h2>
          <p>Mã đơn hàng: {result.data.data.order_number}</p>
          <p>Trạng thái: {result.data.data.status}</p>
          <p>Mã giao dịch: {result.data.data.transaction_id}</p>
        </div>
      ) : (
        <div className="bg-red-100 p-4 rounded">
          <h2 className="text-xl font-bold text-red-800">❌ Giao dịch không thành công</h2>
          <p>{result.error.message || 'Lỗi không xác định'}</p>
        </div>
      )}
    </div>
  );
};

export default VnpayReturnPage;
