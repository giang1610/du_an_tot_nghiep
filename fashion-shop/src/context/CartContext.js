import { createContext, useContext, useState, useEffect, useCallback } from 'react';
import axios from 'axios';

const CartContext = createContext();
export const useCart = () => useContext(CartContext);

export const CartProvider = ({ children }) => {
  const [cart, setCart] = useState([]);
  const [total, setTotal] = useState(0);
  const [loading, setLoading] = useState(false);

  const token = localStorage.getItem('token');

  const calculateTotal = useCallback((items) => {
    const selectedItems = items.filter(i => i.selected);
    const totalAmount = selectedItems.reduce((sum, item) => sum + item.quantity * item.price, 0);
    setTotal(totalAmount);
  }, []);

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

  const clearCart = async () => {
    setCart([]);
    setTotal(0);

    if (!token) return;

    try {
      await axios.delete(`${process.env.REACT_APP_API_URL}/cart/clear`, {
        headers: { Authorization: `Bearer ${token}` }
      });
    } catch (err) {
      console.error('❌ Lỗi khi xóa giỏ hàng trên server:', err);
    }
  };

  useEffect(() => {
    fetchCart();
  }, [fetchCart]);

  useEffect(() => {
    calculateTotal(cart);
  }, [cart, calculateTotal]);

  return (
    <CartContext.Provider value={{ cart, setCart, total, fetchCart, clearCart, loading }}>
      {children}
    </CartContext.Provider>
  );
};
