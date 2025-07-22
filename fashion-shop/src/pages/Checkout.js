// Checkout.jsx
import { useState, useMemo, useEffect } from 'react';
import { useNavigate, useLocation } from 'react-router-dom';
import {
  Container, Form, Button, Alert, Row, Col, Card, Image, Spinner, Badge
} from 'react-bootstrap';
import { useAuth } from '../context/AuthContext';
import { useCart } from '../context/CartContext';
import axios from 'axios';
import VoucherInput from '../components/VoucherInput';

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
  const { user, token } = useAuth();
  const { cart, removeSelectedItems } = useCart();
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

  const selectedItems = useMemo(() => {
    if (isBuyNow) return buyNowItem ? [buyNowItem] : [];
    return Array.isArray(cart) ? cart.filter(item => item.selected) : [];
  }, [isBuyNow, buyNowItem, cart]);

  const [form, setForm] = useState({
    name: '', phone: '', address: '', email: '', notes: '', payment_method: 'cod'
  });

  const [formErrors, setFormErrors] = useState({});
  const [error, setError] = useState('');
  const [success, setSuccess] = useState('');
  const [loading, setLoading] = useState(false);

  const [productVoucherCode, setProductVoucherCode] = useState('');
  const [shippingVoucherCode, setShippingVoucherCode] = useState('');

  const [productVoucherInfo, setProductVoucherInfo] = useState(null);
  const [shippingVoucherInfo, setShippingVoucherInfo] = useState(null);

  const totals = useMemo(() => {
    const subtotal = selectedItems.reduce((sum, item) => sum + item.quantity * item.price, 0);
    const tax = subtotal * 0.1;
    let shipping = 20000;
    let discount = 0;

    if (productVoucherInfo) {
      if (productVoucherInfo.type === 'percent') discount = (subtotal * productVoucherInfo.value) / 100;
      else if (productVoucherInfo.type === 'fixed') discount = productVoucherInfo.value;
    }

    if (shippingVoucherInfo) {
      if (shippingVoucherInfo.type === 'fixed') shipping = Math.max(0, shipping - shippingVoucherInfo.value);
      else if (shippingVoucherInfo.type === 'percent') shipping = shipping * (1 - shippingVoucherInfo.value / 100);
    }

    const total = subtotal + tax + shipping - discount;
    return { subtotal, tax, shipping, discount, total };
  }, [selectedItems, productVoucherInfo, shippingVoucherInfo]);

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

  const applyVoucher = async (type) => {
    if (type === 'remove_product') return setProductVoucherInfo(null);
    if (type === 'remove_shipping') return setShippingVoucherInfo(null);

    setError('');
    const code = type === 'product' ? productVoucherCode : shippingVoucherCode;
    if (!code.trim()) return setError('Vui lòng nhập mã giảm giá.');

    const totalAmount = selectedItems.reduce((sum, item) => sum + item.quantity * item.price, 0);

    try {
      const res = await axios.post(
        `${process.env.REACT_APP_API_URL}/vouchers/apply`,
        { code, total: totalAmount },
        { headers: { Authorization: `Bearer ${token}` } }
      );

      const voucher = res.data;
      if (!voucher || !voucher.value) return setError('Mã giảm giá không hợp lệ.');

      if (voucher.applies_to === 'shipping') {
        setShippingVoucherInfo(voucher);
        setSuccess('Áp dụng mã miễn phí vận chuyển thành công!');
      } else {
        setProductVoucherInfo(voucher);
        setSuccess('Áp dụng mã giảm giá sản phẩm thành công!');
      }
    } catch (err) {
      console.error('❌ Voucher Error:', err);
      setError(err.response?.data?.message || 'Không thể áp dụng mã giảm giá.');
    }
  };

  const handleSubmit = async e => {
    e.preventDefault();
    setSuccess('');
    setError('');
    setFormErrors({});

    if (!validate()) return;

    const token = localStorage.getItem('token') || user?.token;
    if (!token) return setError('Bạn cần đăng nhập để đặt hàng.');
    if (selectedItems.length === 0) return setError('Không có sản phẩm nào để đặt hàng.');

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
      customer_email: form.email,
      notes: form.notes,
      name: form.name,
      payment_method: form.payment_method,
      items: itemsPayload,
      subtotal: totals.subtotal,
      tax: totals.tax,
      shipping: totals.shipping,
      discount: totals.discount,
      total: totals.total,
      voucher_codes: {
        product: productVoucherInfo?.code || null,
        shipping: shippingVoucherInfo?.code || null
      }
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
        localStorage.removeItem('buy_now');
        await removeSelectedItems();
        setTimeout(() => navigate('/orders'), 3000);
      }
    } catch (error) {
      console.error('❌ Lỗi:', error);
      setError('Đặt hàng thất bại. Vui lòng thử lại.');
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => {
    const token = localStorage.getItem('token') || user?.token;
    if (!token || !user) return;

    const fetchUserInfo = async () => {
      try {
        const res = await axios.get(`${process.env.REACT_APP_API_URL}/user`, {
          headers: { Authorization: `Bearer ${token}` },
        });
        const userData = res.data;
        setForm(prev => ({
          ...prev,
          name: userData.name || '',
          phone: userData.phone || '',
          address: userData.address || '',
          email: userData.email || '',
        }));
      } catch (err) {
        console.error('❌ Không lấy được thông tin user:', err);
      }
    };

    fetchUserInfo();
  }, [user]);

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
              <Form.Label>Email</Form.Label>
              <Form.Control
                type="email"
                name="email"
                value={form.email}
                onChange={e => setField('email', e.target.value)}
                disabled
              />
            </Form.Group>

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
                <option value="momo">Thanh toán MoMo</option>
              </Form.Select>
            </Form.Group>

            <Button type="submit" variant="dark" className="w-100" disabled={loading}>
              {loading ? (
                <>
                  <Spinner animation="border" size="sm" className="me-2" />
                  Đang xử lý...
                </>
              ) : form.payment_method === 'momo'
                ? 'Thanh toán qua MoMo'
                : 'Xác nhận đặt hàng'}
            </Button>
          </Form>
        </Col>

        <Col md={6}>
          <h5>Sản phẩm trong giỏ</h5>
          <ProductSummary items={selectedItems} />

          {selectedItems.length > 0 && (
            <>
              <hr />
              <VoucherInput
                type="product"
                code={productVoucherCode}
                setCode={setProductVoucherCode}
                onApply={applyVoucher}
                info={productVoucherInfo}
                label="Mã giảm giá sản phẩm"
                variant="success"
                 token={token}
              />
              <VoucherInput
                type="shipping"
                code={shippingVoucherCode}
                setCode={setShippingVoucherCode}
                onApply={applyVoucher}
                info={shippingVoucherInfo}
                label="Mã miễn phí vận chuyển"
                variant="primary"
                 token={token}
              />

              <p>Tạm tính: {totals.subtotal.toLocaleString()} đ</p>
              <p>Phí vận chuyển: {totals.shipping.toLocaleString()} đ</p>
              <p>Thuế: {totals.tax.toLocaleString()} đ</p>
              {totals.discount > 0 && (
                <p className="text-success">Giảm giá: -{totals.discount.toLocaleString()} đ</p>
              )}
              <h5 className="fw-bold">Tổng cộng: {totals.total.toLocaleString()} đ</h5>
            </>
          )}
        </Col>
      </Row>
    </Container>
  );
}