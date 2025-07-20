import { createContext, useContext, useState, useEffect, useCallback } from 'react';
import axios from 'axios';

const CartContext = createContext();
export const useCart = () => useContext(CartContext);

export const CartProvider = ({ children }) => {
  const [cart, setCart] = useState([]);
  const [total, setTotal] = useState(0);
  const [loading, setLoading] = useState(false);

  const token = localStorage.getItem('token');

  // Tính tổng tiền của sản phẩm đã chọn
  const calculateTotal = useCallback((items) => {
    const selectedItems = items.filter(i => i.selected);
    const totalAmount = selectedItems.reduce((sum, item) => sum + item.quantity * item.price, 0);
    setTotal(totalAmount);
  }, []);

  // Lấy giỏ hàng từ server
  const fetchCart = useCallback(async () => {
    if (!token) return;
    setLoading(true);
    try {
      const res = await axios.get(`${process.env.REACT_APP_API_URL}/cart`, {
        headers: { Authorization: `Bearer ${token}` }
      });
      const items = res.data.cart_items || [];
      
      setCart(items);
      calculateTotal(items);
    } catch (err) {
      console.error('Lỗi fetch cart:', err);
    } finally {
      setLoading(false);
    }
  }, [token, calculateTotal]);

  // Xoá toàn bộ giỏ hàng (nếu cần dùng)
  const clearCart = async () => {
    setCart([]);
    setTotal(0);
    if (!token) return;

    try {
      await axios.delete(`${process.env.REACT_APP_API_URL}/cart/clear`, {
        headers: { Authorization: `Bearer ${token}` }
      });
    } catch (err) {
      console.error('❌ Lỗi khi xóa toàn bộ giỏ hàng:', err);
    }
  };

  // ✅ Xoá các sản phẩm đã chọn
  const removeSelectedItems = async () => {
    if (!token) return;

    try {
      await axios.delete(`${process.env.REACT_APP_API_URL}/cart/remove-selected`, {
        headers: { Authorization: `Bearer ${token}` }
      });
      await fetchCart(); // cập nhật lại giỏ hàng sau khi xoá
    } catch (err) {
      console.error('❌ Lỗi khi xoá sản phẩm đã chọn:', err);
    }
  };

  useEffect(() => {
    fetchCart();
  }, [fetchCart]);

  useEffect(() => {
    calculateTotal(cart);
  }, [cart, calculateTotal]);

  return (
    <CartContext.Provider
      value={{
        cart,
        setCart,
        total,
        loading,
        fetchCart,
        clearCart,
        removeSelectedItems, // ✅ expose hàm này ra context
      }}
    >
      {children}
    </CartContext.Provider>
  );
};
