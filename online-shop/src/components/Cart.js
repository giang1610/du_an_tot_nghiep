// src/components/Cart.js
import React, { useEffect } from 'react';
import { useSelector, useDispatch } from 'react-redux';
import { getCart, addToCart, removeCartItem, checkout } from '../store/cartSlice';

function Cart() {
    const dispatch = useDispatch();
    const { items, total, status } = useSelector((state) => state.cart);
    const { user } = useSelector((state) => state.auth);

    useEffect(() => {
        if (user) {
            dispatch(getCart());
        }
    }, [user, dispatch]);

    const handleCheckout = () => {
        dispatch(checkout({
            shipping_address: '123 Đường ABC, Thành phố, Quốc gia',
            customer_phone: '1234567890',
        }));
    };

    if (status === 'loading') return <div>Đang tải giỏ hàng...</div>;
    if (!user) return <div>Vui lòng đăng nhập để xem giỏ hàng</div>;

    return (
        <div>
            <h2>Giỏ hàng của bạn</h2>
            {items.length === 0 ? (
                <p>Giỏ hàng của bạn đang trống</p>
            ) : (
                <>
                    <ul>
                        {items.map((item) => (
                            <li key={item.id}>
                                <div>
                                    <h4>{item.variant.product.name}</h4>
                                    <p>Màu: {item.variant.color.name}</p>
                                    <p>Kích thước: {item.variant.size.name}</p>
                                    <p>Giá: ${item.variant.current_price}</p>
                                    <p>Số lượng: {item.quantity}</p>
                                    <button onClick={() => dispatch(removeCartItem(item.id))}>
                                        Xóa
                                    </button>
                                </div>
                            </li>
                        ))}
                    </ul>
                    <div>
                        <h3>Tổng cộng: ${total}</h3>
                        <button onClick={handleCheckout}>Thanh toán</button>
                    </div>
                </>
            )}
        </div>
    );
}

export default Cart;