// src/components/OrderStatus.jsx
import React from 'react';

const ORDER_STATUS_MAP = {
  pending: 'Chờ xác nhận',
  processing: 'Đang xử lý',
  picking: 'Đang lấy hàng',
  shipper_arrived: 'Shipper đến lấy hàng', // ✅ thêm
  in_warehouse: 'Hàng về kho',             // ✅ thêm
  shipping: 'Đang giao hàng',
  shipped: 'Đã giao',
  cancelled: 'Đã hủy',
  completed: 'Hoàn thành',
};

const ORDER_STATUS_COLOR = {
  pending: 'gray',
  processing: 'orange',
  picking: 'purple',
  shipper_arrived: 'teal', // ✅ màu riêng cho trạng thái mới
  in_warehouse: 'brown',   // ✅ màu riêng cho trạng thái mới
  shipping: 'blue',
  shipped: 'green',
  cancelled: 'red',
  completed: 'green',
};

const OrderStatus = ({ status }) => {
  return (
    <span
      style={{
        color: ORDER_STATUS_COLOR[status] || 'black',
        fontWeight: 'bold',
      }}
    >
      {ORDER_STATUS_MAP[status] || 'Không rõ'}
    </span>
  );
};

export default OrderStatus;
