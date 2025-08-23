import { useEffect, useState, useCallback, useRef } from 'react';
import { Container, Table, Button, Spinner, Form, Alert } from 'react-bootstrap';
import axios from 'axios';

export default function CartPage() {
  const [cartItems, setCartItems] = useState([]);
  const [total, setTotal] = useState(0);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState('');
  const token = localStorage.getItem('token');
  const updateQuantityTimeout = useRef(null);

  // Thêm nút chọn tất cả
  const [selectAll, setSelectAll] = useState(false);

  const handleSelectAll = () => {
    setSelectAll(!selectAll);
    cartItems.forEach(item => {
      if (item.selected !== !selectAll) toggleSelected(item);
    });
  };
  // Fetch cart and total in one request
  const fetchCartData = useCallback(async () => {
    setLoading(true);
    setError('');
    try {
      const res = await axios.get(`${process.env.REACT_APP_API_URL}/cart`, {
        headers: { Authorization: `Bearer ${token}` }
      });
      setCartItems(res.data.cart_items);

      // Only calculate total for selected items
      const totalRes = await axios.get(`${process.env.REACT_APP_API_URL}/cart/total`, {
        headers: { Authorization: `Bearer ${token}` }
      });
      setTotal(totalRes.data.total);
    } catch (err) {
      setError('Không thể tải giỏ hàng. Vui lòng thử lại.');
      console.error('Fetch cart data failed:', err);
    } finally {
      setLoading(false);
    }
  }, [token]);

  useEffect(() => {
    fetchCartData();
  }, [fetchCartData]);

  // Debounced update quantity
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

        setCartItems(prev =>
          prev.map(i =>
            i.id === item.id ? { ...i, quantity: Number(quantity) } : i
          )
        );

        // Update total
        const totalRes = await axios.get(`${process.env.REACT_APP_API_URL}/cart/total`, {
          headers: { Authorization: `Bearer ${token}` }
        });
        setTotal(totalRes.data.total);
      } catch (err) {
        setError('Cập nhật số lượng thất bại.');
        console.error('Update quantity failed:', err);
      }
    }, 500);
  };

  // Toggle selected
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

      // Update total
      const totalRes = await axios.get(`${process.env.REACT_APP_API_URL}/cart/total`, {
        headers: { Authorization: `Bearer ${token}` }
      });
      setTotal(totalRes.data.total);

    } catch (err) {
      setError('Cập nhật chọn sản phẩm thất bại.');
      console.error('Toggle selected failed:', err);
    }
  };

  // Remove item
  const removeItem = async (itemId) => {
    try {
      await axios.delete(`${process.env.REACT_APP_API_URL}/cart/remove/${itemId}`, {
        headers: { Authorization: `Bearer ${token}` }
      });

      setCartItems(prev => prev.filter(i => i.id !== itemId));

      // Update total
      const totalRes = await axios.get(`${process.env.REACT_APP_API_URL}/cart/total`, {
        headers: { Authorization: `Bearer ${token}` }
      });
      setTotal(totalRes.data.total);

    } catch (err) {
      setError('Xóa sản phẩm thất bại.');
      console.error('Remove item failed:', err);
    }
  };

  // Remove selected items
  const removeSelectedItems = async () => {
    try {
      await axios.delete(`${process.env.REACT_APP_API_URL}/cart/remove-selected`, {
        headers: { Authorization: `Bearer ${token}` }
      });
      fetchCartData();
    } catch (err) {
      setError('Xóa các sản phẩm đã chọn thất bại.');
      console.error('Remove selected items failed:', err);
    }
  };

  // Clear cart
  const clearCart = async () => {
    try {
      await axios.delete(`${process.env.REACT_APP_API_URL}/cart/clear`, {
        headers: { Authorization: `Bearer ${token}` }
      });
      fetchCartData();
    } catch (err) {
      setError('Xóa toàn bộ giỏ hàng thất bại.');
      console.error('Clear cart failed:', err);
    }
  };

  if (loading) return <div className="text-center py-5"><Spinner animation="border" /></div>;

  if (error) return <Alert variant="danger">{error}</Alert>;

  if (!cartItems.length) return (
    <Container className="py-5">
      <Alert variant="info">Giỏ hàng của bạn đang trống.</Alert>
      <div className="text-center mt-3">
        <Button variant="primary" href="/products">Tiếp tục mua sắm</Button>
      </div>
    </Container>
  );

  return (
<Container className="py-5">
    <h2 className="mb-4 text-center">Giỏ hàng của bạn</h2>
    <Table responsive bordered hover>
      <thead>
        <tr>
          <th>
            <Form.Check
              type="checkbox"
              checked={selectAll}
              onChange={handleSelectAll}
              label="Chọn tất cả"
            />
          </th>
          <th>Sản phẩm</th>
          <th>Màu / Size</th>
          <th>Số lượng</th>
          <th>Giá</th>
          <th>Tạm tính</th>
          <th>Thao tác</th>
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
                <img
                  src={item.image || 'https://via.placeholder.com/60'}
                  width={60}
                  alt={item.product_name}
                  style={{ cursor: "pointer" }}
                  onMouseOver={e => e.target.style.width = "100px"}
                  onMouseOut={e => e.target.style.width = "60px"}
                />
                <span>{item.product_name}</span>
              </div>
            </td>
            <td>{item.color} / {item.size}</td>
            <td>
              <Form.Control
                type="number"
                min={1}
                max={item.stock}
                value={item.quantity}
                onChange={(e) => {
                  const val = Number(e.target.value);
                  if (val > item.stock) {
                    setError(`Số lượng vượt quá tồn kho (${item.stock})`);
                    return;
                  }
                  updateQuantity(item, val);
                }}
                style={{ width: '80px' }}
              />
              <div className="text-muted" style={{ fontSize: '12px' }}>
                Tồn kho: {item.stock}
              </div>
            </td>
            <td>{Number(item.price).toLocaleString()}₫</td>
            <td>{(Number(item.price) * Number(item.quantity)).toLocaleString()}₫</td>
            <td>
              <Button
                variant="danger"
                size="sm"
                onClick={() => {
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

    <div className="d-flex flex-column flex-md-row justify-content-between align-items-center mt-4 gap-3">
      <div>
        <Button
          variant="outline-danger"
          size="sm"
          onClick={removeSelectedItems}
          disabled={!cartItems.some(item => item.selected)}
          className="me-2"
        >
          Xóa các sản phẩm đã chọn
        </Button>
        <Button
          variant="outline-secondary"
          size="sm"
          onClick={() => {
            if (window.confirm('Bạn có chắc muốn xóa toàn bộ giỏ hàng?')) {
              clearCart();
            }
          }}
        >
          Xóa toàn bộ giỏ hàng
        </Button>
      </div>
      <h4 className="mb-0">Tổng cộng: <span className="text-success">{total.toLocaleString()}₫</span></h4>
      <Button
        variant="success"
        disabled={!cartItems.some(item => item.selected)}
        onClick={() => {
          window.location.href = '/checkout';
        }}
      >
        Thanh toán sản phẩm đã chọn
      </Button>
    </div>
  </Container>
  );
}