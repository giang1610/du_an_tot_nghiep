import { useCallback, useEffect, useState, useRef } from 'react';
import axios from 'axios';

export default function useCart() {
  const [cartItems, setCartItems] = useState([]);
  const [total, setTotal] = useState(0);
  const [loading, setLoading] = useState(true);
  const updateTimeout = useRef(null);
  const token = localStorage.getItem('token');

  const fetchCart = useCallback(async () => {
    setLoading(true);
    try {
      const res = await axios.get(`${process.env.REACT_APP_API_URL}/cart`, {
        headers: { Authorization: `Bearer ${token}` }
      });
      setCartItems(res.data.cart_items || []);
      const totalRes = await axios.get(`${process.env.REACT_APP_API_URL}/cart/total`, {
        headers: { Authorization: `Bearer ${token}` }
      });
      setTotal(totalRes.data.total);
    } catch (err) {
      console.error('Lỗi khi tải giỏ hàng:', err);
    } finally {
      setLoading(false);
    }
  }, [token]);

  useEffect(() => {
    fetchCart();
  }, [fetchCart]);

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

        setCartItems(prev => prev.map(i => i.id === item.id ? { ...i, quantity: Number(quantity) } : i));
        const totalRes = await axios.get(`${process.env.REACT_APP_API_URL}/cart/total`, {
          headers: { Authorization: `Bearer ${token}` }
        });
        setTotal(totalRes.data.total);
      } catch (err) {
        console.error('Lỗi cập nhật số lượng:', err);
      }
    }, 500);
  };

  const toggleSelected = async (itemId, currentSelected) => {
    try {
      await axios.put(`${process.env.REACT_APP_API_URL}/cart/update-selected/${itemId}`, {
        selected: !currentSelected
      }, {
        headers: { Authorization: `Bearer ${token}` }
      });

      setCartItems(prev =>
        prev.map(i => i.id === itemId ? { ...i, selected: !i.selected } : i)
      );

      const totalRes = await axios.get(`${process.env.REACT_APP_API_URL}/cart/total`, {
        headers: { Authorization: `Bearer ${token}` }
      });
      setTotal(totalRes.data.total);
    } catch (err) {
      console.error('Lỗi chọn sản phẩm:', err);
    }
  };

  const removeItem = async (itemId) => {
    try {
      await axios.delete(`${process.env.REACT_APP_API_URL}/cart/remove/${itemId}`, {
        headers: { Authorization: `Bearer ${token}` }
      });
      setCartItems(prev => prev.filter(i => i.id !== itemId));
      const totalRes = await axios.get(`${process.env.REACT_APP_API_URL}/cart/total`, {
        headers: { Authorization: `Bearer ${token}` }
      });
      setTotal(totalRes.data.total);
    } catch (err) {
      console.error('Lỗi xóa sản phẩm:', err);
    }
  };
  const clearCart = async () => {
  try {
    await fetchCart(); // Cập nhật lại từ server (sau khi backend đã xoá)
  } catch (err) {
    console.error('Lỗi khi làm mới giỏ hàng:', err);
  }
};


  return {
    cartItems,
    total,
    loading,
    updateQuantity,
    toggleSelected,
    removeItem,
    fetchCart,
    clearCart
  };
}
