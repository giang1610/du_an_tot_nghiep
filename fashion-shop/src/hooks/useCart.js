import { useRef } from 'react';
import axios from 'axios';
import { useCart } from '../context/CartContext';

export default function useCartActions() {
  const {
    cart,
    setCart,
    total,
    fetchCart,
    clearCart,
    loading
  } = useCart();

  const token = localStorage.getItem('token');
  const updateTimeout = useRef(null);

  const updateQuantity = (item, quantity) => {
    if (updateTimeout.current) clearTimeout(updateTimeout.current);

    updateTimeout.current = setTimeout(async () => {
      try {
        await axios.put(`${process.env.REACT_APP_API_URL}/cart/update/${item.id}`, {
          quantity: Number(quantity),
          color_id: item.color_id,
          size_id: item.size_id
        }, {
          headers: { Authorization: `Bearer ${token}` }
        });

        const updatedCart = cart.map(i =>
          i.id === item.id ? { ...i, quantity: Number(quantity) } : i
        );

        setCart(updatedCart);
      } catch (err) {
        console.error('Lỗi cập nhật số lượng:', err);
      }
    }, 500);
  };

  const toggleSelected = async (item) => {
    try {
      await axios.put(`${process.env.REACT_APP_API_URL}/cart/update-selected/${item.id}`, {
        selected: !item.selected
      }, {
        headers: { Authorization: `Bearer ${token}` }
      });

      const updatedCart = cart.map(i =>
        i.id === item.id ? { ...i, selected: !i.selected } : i
      );

      setCart(updatedCart);
    } catch (err) {
      console.error('Lỗi chọn sản phẩm:', err);
    }
  };

  const removeItem = async (itemId) => {
    try {
      await axios.delete(`${process.env.REACT_APP_API_URL}/cart/remove/${itemId}`, {
        headers: { Authorization: `Bearer ${token}` }
      });

      const updatedCart = cart.filter(i => i.id !== itemId);
      setCart(updatedCart);
    } catch (err) {
      console.error('Lỗi xóa sản phẩm:', err);
    }
  };

  return {
    cartItems: cart,
    total,
    loading,
    updateQuantity,
    toggleSelected,
    removeItem,
    fetchCart,
    clearCart
  };
}
  