import React from "react";
import { useCart } from "../contexts/CartContext";

function CartPage() {
  const { cartItems, dispatch } = useCart();

  const total = cartItems.reduce(
    (sum, item) => sum + item.price * item.quantity,
    0
  );

  const handleQuantityChange = (id, quantity) => {
    dispatch({ type: "UPDATE_QUANTITY", payload: { id, quantity } });
  };

  const handleRemove = (id) => {
    dispatch({ type: "REMOVE_FROM_CART", payload: id });
  };

  return (
    <div className="container mt-4">
      <h2>Giỏ hàng</h2>
      {cartItems.length === 0 ? (
        <p>Giỏ hàng trống.</p>
      ) : (
        <>
          {cartItems.map((item) => (
            <div
              key={item.id}
              className="d-flex align-items-center justify-content-between border-bottom py-2"
            >
              <div>
                <h5>{item.name}</h5>
                <p>Giá: {item.price.toLocaleString()}₫</p>
                <input
                  type="number"
                  value={item.quantity}
                  min="1"
                  className="form-control"
                  style={{ width: "80px" }}
                  onChange={(e) =>
                    handleQuantityChange(item.id, parseInt(e.target.value))
                  }
                />
              </div>
              <button
                className="btn btn-danger"
                onClick={() => handleRemove(item.id)}
              >
                Xóa
              </button>
            </div>
          ))}
          <hr />
          <h4>Tổng tiền: {total.toLocaleString()}₫</h4>
          <button className="btn btn-success">Tiến hành thanh toán</button>
        </>
      )}
    </div>
  );
}

export default CartPage;
