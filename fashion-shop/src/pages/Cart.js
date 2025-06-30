import { Container, Table, Button, Spinner, Form, Alert } from 'react-bootstrap';
import useCart from '../hooks/useCart';

export default function CartPage() {
  const {
    cartItems,
    total,
    loading,
    updateQuantity,
    toggleSelected,
    removeItem
  } = useCart();

  if (loading) return <div className="text-center py-5"><Spinner animation="border" /></div>;

  if (!cartItems.length) return <Alert variant="info">Giỏ hàng của bạn đang trống.</Alert>;
  const selectedItems = cartItems.filter(item => item.selected);

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
                  onChange={() => toggleSelected(item.id, item.selected)}
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
                <Button variant="danger" size="sm" onClick={() => removeItem(item.id)}>Xóa</Button>
              </td>
            </tr>
          ))}
        </tbody>
      </Table>

      <h4 className="text-end mt-4">Tổng cộng: {total.toLocaleString()}₫</h4>
      {selectedItems.length > 0 && (
        <div className="text-end">
          <Button variant="success" href="/checkout">Thanh toán</Button>
        </div>
      )}

    </Container>
  );
}
