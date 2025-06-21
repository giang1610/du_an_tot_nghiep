import React, { useEffect } from 'react';
import { useSelector, useDispatch } from 'react-redux';
import {
  getCart,
  removeCartItem,
  checkout,
} from '../store/cartSlice';

const Cart = () => {
  const dispatch = useDispatch();
  const { items, total, status } = useSelector((state) => state.cart);
  const { user } = useSelector((state) => state.auth);

  useEffect(() => {
    if (user) {
      dispatch(getCart());
    }
  }, [user, dispatch]);

  const handleRemoveItem = (id) => {
    dispatch(removeCartItem(id));
  };

  const handleCheckout = () => {
    dispatch(
      checkout({
        shipping_address: '123 Đường ABC, Thành phố, Quốc gia',
        customer_phone: '1234567890',
      })
    );
  };

  if (!user)
    return (
      <div className="container my-5">
        <div className="alert alert-warning text-center">Vui lòng đăng nhập để xem giỏ hàng.</div>
      </div>
    );

  if (status === 'loading')
    return (
      <div className="container my-5 text-center">
        <div className="spinner-border text-primary" role="status" />
        <p className="mt-3">Đang tải giỏ hàng...</p>
      </div>
    );

  if (items.length === 0)
    return (
      <div className="container my-5">
        <div className="alert alert-info text-center">Giỏ hàng của bạn đang trống.</div>
      </div>
    );

  return (
    <div className="container my-5">
      <h2 className="mb-4">🛒 Giỏ hàng của bạn</h2>
      <div className="list-group mb-4">
        {items.map((item) => {
          const { id, quantity, variant } = item;
          const { product, color, size, current_price } = variant;

          return (
            <div key={id} className="list-group-item">
              <div className="row align-items-center">
                <div className="col-md-8">
                  <h5 className="mb-1">{product.name}</h5>
                  <p className="mb-1">Màu: {color.name}</p>
                  <p className="mb-1">Kích thước: {size.name}</p>
                  <p className="mb-1">Giá: <strong>${current_price}</strong></p>
                  <p className="mb-1">Số lượng: {quantity}</p>
                </div>
                <div className="col-md-4 text-md-end">
                  <button
                    className="btn btn-outline-danger btn-sm"
                    onClick={() => handleRemoveItem(id)}
                  >
                    <i className="bi bi-trash"></i> Xóa
                  </button>
                </div>
              </div>
            </div>
          );
        })}
      </div>

      <div className="d-flex justify-content-between align-items-center border-top pt-3">
        <h4 className="mb-0">Tổng cộng: <strong>${total}</strong></h4>
        <button className="btn btn-success" onClick={handleCheckout}>
          <i className="bi bi-cash-stack me-1"></i> Thanh toán
        </button>
      </div>
    </div>
  );
};

export default Cart;
