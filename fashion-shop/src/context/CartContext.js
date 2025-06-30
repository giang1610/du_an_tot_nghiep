import { createContext, useContext, useState, useEffect, useCallback } from 'react';
import axios from 'axios';

const CartContext = createContext();

export const useCart = () => useContext(CartContext);

export const CartProvider = ({ children }) => {
  const [cart, setCart] = useState([]);
  const [loading, setLoading] = useState(false);

  const token = localStorage.getItem('token');

  // ✅ Dùng useCallback để tránh warning ESLint
  const fetchCart = useCallback(async () => {
    if (!token) return;
    try {
      setLoading(true);
      const res = await axios.get(`${process.env.REACT_APP_API_URL}/cart`, {
        headers: { Authorization: `Bearer ${token}` }
      });
      const selectedItems = res.data.cart_items.filter(item => item.selected);
      setCart(selectedItems);
    } catch (err) {
      console.error('Lỗi fetch giỏ hàng:', err);
    } finally {
      setLoading(false);
    }
  }, [token]);

  const clearCart = () => setCart([]);

  useEffect(() => {
    fetchCart();
  }, [fetchCart]);

  return (
    <CartContext.Provider value={{ cart, setCart, fetchCart, clearCart, loading }}>
      {children}
    </CartContext.Provider>
  );
};
