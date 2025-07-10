import { useEffect, useState, useCallback, useRef } from 'react';
import { Container, Table, Button, Spinner, Form, Alert } from 'react-bootstrap';
import axios from 'axios';

export default function CartPage() {
  const [cartItems, setCartItems] = useState([]);
  const [total, setTotal] = useState(0);
  const [loading, setLoading] = useState(true);
  const token = localStorage.getItem('token');
  const updateQuantityTimeout = useRef(null);

  // Gộp fetch giỏ hàng và tổng tiền
  const fetchCartData = useCallback(async () => {
    setLoading(true);
    try {
      const res = await axios.get(`${process.env.REACT_APP_API_URL}/cart`, {
        headers: { Authorization: `Bearer ${token}` }
      });
      setCartItems(res.data.cart_items);

      const totalRes = await axios.get(`${process.env.REACT_APP_API_URL}/cart/total`, {
        headers: { Authorization: `Bearer ${token}` }
      });
      setTotal(totalRes.data.total);
    } catch (err) {
      console.error('Fetch cart data failed:', err);
    } finally {
      setLoading(false);
    }
  }, [token]);

  useEffect(() => {
    fetchCartData();
  }, [fetchCartData]);

  // Cập nhật số lượng có debounce 500ms để tránh gọi API liên tục
  const updateQuantity = (item, quantity) => {
    if (updateQuantityTimeout.current) clearTimeout(updateQuantityTimeout.current);

    updateQuantityTimeout.current = setTimeout(async () => {
      try {
        await axios.put(`${process.env.REACT_APP_API_URL}/cart/update/${item.id}`, {
          quantity: Number(quantity),
          color_id: item.color_id,
          size_id: item.size_id
        }, {
          headers: { Authorization: `Bearer ${token}` }
        });

        // Cập nhật local state luôn cho mượt UI
        setCartItems(prev =>
          prev.map(i =>
            i.id === item.id ? { ...i, quantity: Number(quantity) } : i
          )
        );

        // Cập nhật tổng tiền
        const totalRes = await axios.get(`${process.env.REACT_APP_API_URL}/cart/total`, {
          headers: { Authorization: `Bearer ${token}` }
        });
        setTotal(totalRes.data.total);
      } catch (err) {
        console.error('Update quantity failed:', err);
      }
    }, 500);
  };

  // Chọn/bỏ chọn sản phẩm
  const toggleSelected = async (item) => {
    try {
      await axios.put(`${process.env.REACT_APP_API_URL}/cart/update-selected/${item.id}`, {
        selected: !item.selected
      }, {
        headers: { Authorization: `Bearer ${token}` }
      });

      setCartItems(prev =>
        prev.map(i => i.id === item.id ? { ...i, selected: !i.selected } : i)
      );

      // Cập nhật tổng tiền
      const totalRes = await axios.get(`${process.env.REACT_APP_API_URL}/cart/total`, {
        headers: { Authorization: `Bearer ${token}` }
      });
      setTotal(totalRes.data.total);

    } catch (err) {
      console.error('Toggle selected failed:', err);
    }
  };

  // Xóa sản phẩm
  const removeItem = async (itemId) => {
    try {
      await axios.delete(`${process.env.REACT_APP_API_URL}/cart/remove/${itemId}`, {
        headers: { Authorization: `Bearer ${token}` }
      });

      setCartItems(prev => prev.filter(i => i.id !== itemId));

      // Cập nhật tổng tiền
      const totalRes = await axios.get(`${process.env.REACT_APP_API_URL}/cart/total`, {
        headers: { Authorization: `Bearer ${token}` }
      });
      setTotal(totalRes.data.total);

    } catch (err) {
      console.error('Remove item failed:', err);
    }
  };

  if (loading) return <div className="text-center py-5"><Spinner animation="border" /></div>;

  if (!cartItems.length) return <Alert variant="info">Giỏ hàng của bạn đang trống.</Alert>;

  return (
    <Container className="py-5">
      <h2 className="mb-4">Giỏ hàng</h2>
      <Table responsive bordered hover>
        <thead>
          <tr>
            <th>Chọn</th>
            <th>Sản phẩm</th>
            <th>Màu / Size</th>
            <th>Số lượng</th>
            <th>Giá</th>
            <th>Tạm tính</th>
            <th></th>
          </tr>
        </thead>
        <tbody>
          {cartItems.map(item => (
            <tr key={item.id}>
              <td className="text-center">
                <Form.Check
                  type="checkbox"
                  checked={item.selected}
                  onChange={() => toggleSelected(item)}
                />
              </td>
              <td>
                <div className="d-flex align-items-center gap-2">
                  <img src={item.image || 'https://via.placeholder.com/60'} width={60} alt={item.product_name} />
                  <span>{item.product_name}</span>
                </div>
              </td>
              <td>{item.color} / {item.size}</td>
              <td>
                <Form.Control
                  type="number"
                  min={1}
                  value={item.quantity}
                  onChange={(e) => updateQuantity(item, e.target.value)}
                  style={{ width: '80px' }}
                />
              </td>
              <td>{Number(item.price).toLocaleString()}₫</td>
              <td>{Number(item.subtotal).toLocaleString()}₫</td>
              <td>
                <Button
                  variant="danger"
                  size="sm"
                  onClick={() => {
                    if (!item.selected) {
                      alert('Vui lòng chọn sản phẩm trước khi xóa.');
                      return;
                    }
                    if (window.confirm('Bạn có chắc muốn xóa sản phẩm này khỏi giỏ hàng?')) {
                      removeItem(item.id);
                    }
                  }}
                >
                  Xóa
                </Button>


              </td>
            </tr>
          ))}
        </tbody>
      </Table>

      <h4 className="text-end mt-4">Tổng cộng: {total.toLocaleString()}₫</h4>
      <div className="text-end">
        <Button
          variant="success"
          disabled={!cartItems.some(item => item.selected)}
          onClick={() => {
            // Gửi danh sách sản phẩm được chọn nếu cần (ví dụ dùng Context / Redux hoặc localStorage)
            window.location.href = '/checkout';
          }}
        >
          Thanh toán
        </Button>
      </div>

    </Container>
  );
}