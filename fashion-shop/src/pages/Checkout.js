import { useState, useMemo } from 'react';
import { useNavigate, useLocation } from 'react-router-dom';
import { Container, Form, Button, Alert, Row, Col, Card, Image, Spinner } from 'react-bootstrap';
import { useAuth } from '../context/AuthContext';
import { useCart } from '../context/CartContext';
import axios from 'axios';

const ProductSummary = ({ items }) => {
  if (!items.length) return <p>Bạn chưa chọn sản phẩm nào để đặt hàng.</p>;
  return (
    <>
      {items.map(item => (
        <Card key={item.id || item.product_variant_id || item.variant_id} className="mb-3">
          <Card.Body className="d-flex">
            <Image
              src={item.image}
              alt={item.product_name || item.name}
              width={80}
              height={80}
              className="me-3"
              style={{ objectFit: 'cover' }}
            />
            <div>
              <Card.Title>{item.product_name || item.name}</Card.Title>
              <Card.Text>
                Số lượng: {item.quantity} <br />
                Giá: {item.price.toLocaleString()} đ <br />
                {item.color && <>Màu: {item.color}<br /></>}
                {item.size && <>Size: {item.size}</>}
              </Card.Text>
            </div>
          </Card.Body>
        </Card>
      ))}
    </>
  );
};

export default function Checkout() {
  const { user } = useAuth();
  const { cart, clearCart } = useCart();
  const navigate = useNavigate();
  const location = useLocation();

  const isBuyNow = useMemo(() => new URLSearchParams(location.search).get('buy_now') === '1', [location.search]);

  const buyNowItem = useMemo(() => {
    if (!isBuyNow) return null;
    try {
      const item = localStorage.getItem('buy_now');
      return item ? JSON.parse(item) : null;
    } catch {
      return null;
    }
  }, [isBuyNow]);

  const [form, setForm] = useState({
    name: '',
    phone: '',
    address: '',
    notes: '',
    payment_method: 'cod',
  });

  const [formErrors, setFormErrors] = useState({});
  const [error, setError] = useState('');
  const [success, setSuccess] = useState('');
  const [loading, setLoading] = useState(false);

  const selectedItems = useMemo(() => {
    if (isBuyNow) return buyNowItem ? [buyNowItem] : [];
    return cart.filter(item => item.selected);
  }, [isBuyNow, buyNowItem, cart]);

  const setField = (name, value) => {
    setForm(prev => ({ ...prev, [name]: value }));
    if (formErrors[name]) setFormErrors(prev => ({ ...prev, [name]: '' }));
    if (error) setError('');
  };

  const validate = () => {
    const errors = {};
    if (!form.name.trim()) errors.name = 'Vui lòng nhập họ tên.';
    if (!form.phone.trim()) errors.phone = 'Vui lòng nhập số điện thoại.';
    else if (!/^(0|\+84)\d{9,10}$/.test(form.phone.trim())) errors.phone = 'Số điện thoại không hợp lệ.';
    if (!form.address.trim()) errors.address = 'Vui lòng nhập địa chỉ.';
    setFormErrors(errors);
    return Object.keys(errors).length === 0;
  };

  const handleSubmit = async e => {
    e.preventDefault();
    setSuccess('');
    setError('');
    setFormErrors({});

    if (!validate()) return;

    const token = localStorage.getItem('token') || user?.token;
    if (!token) {
      setError('Bạn cần đăng nhập để đặt hàng.');
      return;
    }
    if (selectedItems.length === 0) {
      setError('Không có sản phẩm nào để đặt hàng.');
      return;
    }

    const itemsPayload = selectedItems.map(item => ({
      product_variant_id: item.product_variant_id || item.variant_id,
      quantity: item.quantity,
      price: item.price,
      size_id: item.size_id || null,
      color_id: item.color_id || null,
    }));

    const payload = {
      shipping_address: form.address,
      billing_address: form.address,
      customer_phone: form.phone,
      notes: form.notes,
      name: form.name,
      payment_method: form.payment_method,
      items: itemsPayload,
    };

    try {
      setLoading(true);
      if (form.payment_method === 'momo') {
        const { data } = await axios.post(
          `${process.env.REACT_APP_API_URL}/payment/momo`,
          payload,
          { headers: { Authorization: `Bearer ${token}` } }
        );

        if (data?.data?.payment_url) {
          localStorage.removeItem('buy_now');
          clearCart();
          window.location.href = data.data.payment_url;
          return;
        }
        setError('Không nhận được liên kết thanh toán MoMo');
      } else {
        const { data } = await axios.post(
          `${process.env.REACT_APP_API_URL}/orders/checkout`,
          payload,
          { headers: { Authorization: `Bearer ${token}` } }
        );

        setSuccess(data.message || 'Đặt hàng thành công!');
        clearCart();
        localStorage.removeItem('buy_now');
        setTimeout(() => navigate('/orders'), 3000);
      }
    } catch (err) {
      const resp = err.response?.data;
      if (resp) {
        if (resp.errors) setFormErrors(resp.errors);
        if (resp.message) setError(resp.message);
      } else {
        setError('Lỗi đặt hàng.');
      }
      console.error(err);
    } finally {
      setLoading(false);
    }
  };

  return (
    <Container className="py-5">
      <h3 className="mb-4">Thanh toán</h3>
      <Row>
        <Col md={6}>
          {error && <Alert variant="danger">{error}</Alert>}
          {success && <Alert variant="success">{success}</Alert>}

          <Form noValidate onSubmit={handleSubmit}>
            {['name', 'phone', 'address'].map(field => (
              <Form.Group className="mb-3" key={field}>
                <Form.Label>
                  {field === 'name' && 'Họ tên'}
                  {field === 'phone' && 'Số điện thoại'}
                  {field === 'address' && 'Địa chỉ'}
                </Form.Label>
                <Form.Control
                  type="text"
                  name={field}
                  value={form[field]}
                  onChange={e => setField(field, e.target.value)}
                  isInvalid={!!formErrors[field]}
                  required
                />
                <Form.Control.Feedback type="invalid">{formErrors[field]}</Form.Control.Feedback>
              </Form.Group>
            ))}

            <Form.Group className="mb-3">
              <Form.Label>Ghi chú</Form.Label>
              <Form.Control
                as="textarea"
                rows={3}
                name="notes"
                value={form.notes}
                onChange={e => setField('notes', e.target.value)}
              />
            </Form.Group>

            <Form.Group className="mb-3">
              <Form.Label>Phương thức thanh toán</Form.Label>
              <Form.Select
                name="payment_method"
                value={form.payment_method}
                onChange={e => setField('payment_method', e.target.value)}
              >
                <option value="cod">Thanh toán khi nhận hàng (COD)</option>
                <option value="banking">Chuyển khoản</option>
                <option value="momo">Thanh toán MoMo</option>
              </Form.Select>
            </Form.Group>

            <Button type="submit" variant="dark" className="w-100" disabled={loading}>
              {loading ? (
                <>
                  <Spinner animation="border" size="sm" className="me-2" />
                  Đang xử lý...
                </>
              ) : form.payment_method === 'momo' ? 'Thanh toán qua MoMo' : 'Xác nhận đặt hàng'}
            </Button>
          </Form>
        </Col>

        <Col md={6}>
          <h5>Sản phẩm trong giỏ</h5>
          <ProductSummary items={selectedItems} />
        </Col>
      </Row>
    </Container>
  );
}
