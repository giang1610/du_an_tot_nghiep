import React from 'react';
import { useSelector } from 'react-redux';
import { Link } from 'react-router-dom';

const CheckoutSuccess = () => {
  const { order } = useSelector((state) => state.cart);

  if (!order) {
    return (
      <div className="container my-5 text-center">
        <h2 className="text-danger mb-3">Không tìm thấy đơn hàng</h2>
        <Link to="/" className="btn btn-primary">
          Quay lại trang chủ
        </Link>
      </div>
    );
  }

  return (
    <div className="container my-5">
      <div className="alert alert-success text-center">
        <h2 className="mb-3">🎉 Đặt hàng thành công!</h2>
        <p className="mb-1">Mã đơn hàng của bạn là: <strong>{order.order_number}</strong></p>
        <p className="mb-3">Tổng cộng: <strong>${order.total}</strong></p>
      </div>

      <div className="card mb-4">
        <div className="card-header">
          <h4 className="mb-0">📦 Danh sách sản phẩm</h4>
        </div>
        <ul className="list-group list-group-flush">
          {order.items.map((item) => (
            <li key={item.id} className="list-group-item">
              <div className="d-flex justify-content-between">
                <div>
                  <strong>{item.variant.product.name}</strong>
                  <div className="small text-muted">
                    {item.quantity} x ${item.price}
                  </div>
                </div>
                <div className="fw-bold">
                  ${item.quantity * item.price}
                </div>
              </div>
            </li>
          ))}
        </ul>
      </div>

      <div className="text-center">
        <p className="mb-3">📧 Chúng tôi đã gửi email xác nhận đến địa chỉ của bạn.</p>
        <Link to="/" className="btn btn-success">
          Tiếp tục mua sắm
        </Link>
      </div>
    </div>
  );
};

export default CheckoutSuccess;
