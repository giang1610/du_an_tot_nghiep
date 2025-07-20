// src/components/OrderStatus.jsx
import React from 'react';

const ORDER_STATUS_MAP = {
  pending: 'Chờ xác nhận',
  processing: 'Đang xử lý',
  picking: 'Đang lấy hàng',
  shipping: 'Đang giao hàng',
  shipped: 'Đã giao',
  cancelled: 'Đã hủy',
  completed: 'Hoàn thành',
};

const ORDER_STATUS_COLOR = {
  pending: 'gray',
  processing: 'orange',
  picking: 'purple',
  shipping: 'blue',
  shipped: 'green',
  cancelled: 'red',
  completed: 'green',
};

const OrderStatus = ({ status }) => {
  return (
    <span style={{ color: ORDER_STATUS_COLOR[status] || 'black', fontWeight: 'bold' }}>
      {ORDER_STATUS_MAP[status] || 'Không rõ'}
    </span>
  );
};

export default OrderStatus;
