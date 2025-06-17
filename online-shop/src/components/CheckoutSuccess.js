// src/components/CheckoutSuccess.js
import React from 'react';
import { useSelector } from 'react-redux';
import { Link } from 'react-router-dom';

function CheckoutSuccess() {
  const { order } = useSelector((state) => state.cart);

  if (!order) {
    return (
      <div>
        <h2>Không tìm thấy đơn hàng</h2>
        <Link to="/">Quay lại trang chủ</Link>
      </div>
    );
  }

  return (
    <div>
      <h2>Đặt hàng thành công!</h2>
      <p>Mã đơn hàng của bạn là: {order.order_number}</p>
      <p>Tổng cộng: ${order.total}</p>
      
      <h3>Các sản phẩm:</h3>
      <ul>
        {order.items.map((item) => (
          <li key={item.id}>
            {item.variant.product.name} - {item.quantity} x ${item.price}
          </li>
        ))}
      </ul>

      <p>Chúng tôi đã gửi email xác nhận đến địa chỉ của bạn.</p>
      <Link to="/">Tiếp tục mua sắm</Link>
    </div>
  );
}

export default CheckoutSuccess;