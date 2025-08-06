import { useState, useMemo, useEffect } from 'react';
import { useNavigate, useLocation } from 'react-router-dom';
import {
  Container, Form, Button, Alert, Row, Col, Card, Image, Spinner
} from 'react-bootstrap';
import { useAuth } from '../context/AuthContext';
import { useCart } from '../context/CartContext';
import axios from 'axios';
import { toast } from "react-toastify";

// Helper
const formatCurrency = (num) => (num ?? 0).toLocaleString();

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
                Giá: {formatCurrency(item.price)} VNĐ <br />
                {item.color && <>Màu: {item.color}<br /></>}
                {item.size && <>Size: {item.size}<br /></>}

                {item.stock === 0 && (
                  <span className="text-danger fw-bold">Sản phẩm đã hết hàng</span>
                )}
                {item.quantity > item.stock && item.stock > 0 && (
                  <span className="text-warning fw-bold">
                    Chỉ còn {item.stock} sản phẩm trong kho
                  </span>
                )}
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
  const [availableProductVouchers, setAvailableProductVouchers] = useState([]);
  const [availableShippingVouchers, setAvailableShippingVouchers] = useState([]);

  // base shipping fee - keep in sync with backend default
  const BASE_SHIPPING = 20000;

  const totals = useMemo(() => {
    const subtotal = selectedItems.reduce((sum, item) => sum + item.quantity * (item.price ?? 0), 0);
    const tax = subtotal * 0.1;
    let shipping = BASE_SHIPPING;
    let productDiscount = 0;

    if (productVoucherInfo) {
      if (productVoucherInfo.type === 'percent') productDiscount = (subtotal * productVoucherInfo.value) / 100;
      else if (productVoucherInfo.type === 'fixed') productDiscount = productVoucherInfo.value;
    }

    if (shippingVoucherInfo) {
      if (shippingVoucherInfo.type === 'fixed') shipping = Math.max(0, shipping - shippingVoucherInfo.value);
      else if (shippingVoucherInfo.type === 'percent') shipping = shipping * (1 - (shippingVoucherInfo.value ?? 0) / 100);
    }

    const total = subtotal + tax + shipping - productDiscount;
    return { subtotal, tax, shipping, discount: productDiscount, total: Math.max(0, total) };
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

  // Reset applied vouchers when cart/buy-now items change (to avoid stale vouchers)
  useEffect(() => {
    setProductVoucherCode('');
    setShippingVoucherCode('');
    setProductVoucherInfo(null);
    setShippingVoucherInfo(null);
  }, [isBuyNow, buyNowItem, cart?.length]);

  const applyVoucher = async (type) => {
    setError('');
    setSuccess('');

    if (type === 'remove_product') {
      setProductVoucherCode('');
      setProductVoucherInfo(null);
      return;
    }
    if (type === 'remove_shipping') {
      setShippingVoucherCode('');
      setShippingVoucherInfo(null);
      return;
    }

    const code = type === 'product' ? productVoucherCode : shippingVoucherCode;
    if (!code || !code.trim()) {
      return setError('Vui lòng chọn mã giảm giá.');
    }

    const token = localStorage.getItem('token');
    if (!token) return setError('Bạn cần đăng nhập để áp dụng mã giảm giá.');

    // total to send to /vouchers/apply
    const amountContext = type === 'product' ? totals.subtotal : totals.shipping;

    try {
      // We send type so backend can validate (product/shipping)
      const res = await axios.post(
        `${process.env.REACT_APP_API_URL}/vouchers/apply`,
        { code: code.trim(), total: amountContext, type },
        { headers: { Authorization: `Bearer ${token}` } }
      );

      // Expect backend to return voucher object: { code, type: 'percent'|'fixed', value, applies_to }
      const voucher = res.data;

      if (!voucher || (!voucher.type && !voucher.applies_to && voucher.value == null)) {
        return setError('Mã giảm giá không hợp lệ.');
      }

      if (type === 'product') {
        setProductVoucherInfo(voucher);
        setSuccess('Áp dụng mã giảm giá sản phẩm thành công!');
      } else {
        setShippingVoucherInfo(voucher);
        setSuccess('Áp dụng mã miễn phí vận chuyển thành công!');
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
    if (selectedItems.some(item => item.stock === 0 || item.quantity > item.stock)) {
      return setError('Có sản phẩm đã hết hàng hoặc vượt quá số lượng tồn kho. Vui lòng kiểm tra lại.');
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
      customer_email: form.email,
      notes: form.notes,
      name: form.name,
      payment_method: form.payment_method,
      items: itemsPayload,
      subtotal: totals.subtotal,
      tax: totals.tax,
      shipping: totals.shipping,
      discount_amount: totals.discount,
      total: totals.total,
      // send both voucher codes to backend (null if none)
      product_voucher_code: productVoucherInfo?.code ?? null,
      shipping_voucher_code: shippingVoucherInfo?.code ?? null,
      // helpful flag so backend knows if it's buy-now (optional)
      buy_now: isBuyNow ? 1 : 0,
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
          // remove buy_now local and selected cart items *before* redirect to avoid leftover state
          localStorage.removeItem('buy_now');
          // Only remove cart items when not buy-now (removeSelectedItems likely handles selected items)
          if (!isBuyNow) await removeSelectedItems();
          window.location.href = data.data.payment_url;
          return;
        } else {
          setError('Không nhận được liên kết thanh toán MoMo');
        }
      } else if (form.payment_method === 'vnpay') {
        const { data } = await axios.post(
          `${process.env.REACT_APP_API_URL}/vnpay/pay`,
          payload,
          { headers: { Authorization: `Bearer ${token}` } }
        );

        if (data?.data?.payment_url) {
          localStorage.removeItem('buy_now');
          if (!isBuyNow) await removeSelectedItems();
          window.location.href = data.data.payment_url;
        } else {
          toast.error("Không nhận được liên kết thanh toán VNPay");
        }
      } else {
        // COD / orders/checkout
        const { data } = await axios.post(
          `${process.env.REACT_APP_API_URL}/orders/checkout`,
          payload,
          { headers: { Authorization: `Bearer ${token}` } }
        );
        setSuccess(data.message || 'Đặt hàng thành công!');
        localStorage.removeItem('buy_now');
        if (!isBuyNow) await removeSelectedItems();
        setTimeout(() => navigate('/orders'), 3000);
      }
    } catch (error) {
      console.error('❌ Lỗi khi gọi API:', error);
      console.error('Status:', error.response?.status);
      console.error('Response data:', error.response?.data);
      setError(error.response?.data?.message || error.message || 'Đặt hàng thất bại. Vui lòng thử lại.');
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

  useEffect(() => {
    const token = localStorage.getItem('token') || user?.token;
    if (!token) return;

    const fetchVouchers = async () => {
      try {
        const res1 = await axios.get(`${process.env.REACT_APP_API_URL}/vouchers?type=product`, {
          headers: { Authorization: `Bearer ${token}` }
        });
        setAvailableProductVouchers(res1.data || []);

        const res2 = await axios.get(`${process.env.REACT_APP_API_URL}/vouchers?type=shipping`, {
          headers: { Authorization: `Bearer ${token}` }
        });
        setAvailableShippingVouchers(res2.data || []);
      } catch (err) {
        console.error('❌ Không lấy được danh sách voucher:', err);
      }
    };

    fetchVouchers();
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
                value={form.payment_method}
                onChange={(e) =>
                  setForm((prev) => ({
                    ...prev,
                    payment_method: e.target.value,
                  }))
                }
              >
                <option value="cod">Thanh toán khi nhận hàng (COD)</option>
                <option value="momo">Thanh toán MoMo</option>
                <option value="vnpay">Thanh toán VNPay</option>
              </Form.Select>
            </Form.Group>

            <Button type="submit" disabled={loading}>
              {loading ? (
                <>
                  <Spinner animation="border" size="sm" className="me-2" />
                  Đang xử lý...
                </>
              ) : form.payment_method === 'momo' ? (
                'Thanh toán qua MoMo'
              ) : form.payment_method === 'vnpay' ? (
                'Thanh toán qua VNPay'
              ) : (
                'Xác nhận đặt hàng'
              )}
            </Button>

          </Form>
        </Col>

        <Col md={6}>
          <h5>Sản phẩm trong giỏ</h5>
          <ProductSummary items={selectedItems} />

          {selectedItems.length > 0 && (
            <>
              <hr />

              <Form.Group className="mb-3">
                <Form.Label>Mã giảm giá sản phẩm</Form.Label>
                <Form.Select
                  value={productVoucherCode}
                  onChange={e => setProductVoucherCode(e.target.value)}
                >
                  <option value="">-- Không áp dụng --</option>
                  {availableProductVouchers.map(voucher => (
                    <option key={voucher.code} value={voucher.code}>
                      {voucher.code} - {voucher.type === 'percent'
                        ? `${voucher.value ?? 0}%`
                        : `${formatCurrency(voucher.value)} VNĐ`}
                    </option>
                  ))}
                </Form.Select>
                <div className="d-flex gap-2 mt-2">
                  <Button variant="success" size="sm" onClick={() => applyVoucher('product')} disabled={!productVoucherCode || loading}>
                    Áp dụng
                  </Button>
                  {productVoucherInfo && (
                    <div className="mt-1 text-success">
                      ✅ {productVoucherInfo.code}
                      <Button variant="link" size="sm" onClick={() => applyVoucher('remove_product')}>[Hủy]</Button>
                    </div>
                  )}
                </div>
              </Form.Group>

              <Form.Group className="mb-3">
                <Form.Label>Mã miễn phí vận chuyển</Form.Label>
                <Form.Select
                  value={shippingVoucherCode}
                  onChange={e => setShippingVoucherCode(e.target.value)}
                >
                  <option value="">-- Không áp dụng --</option>
                  {availableShippingVouchers.map(voucher => (
                    <option key={voucher.code} value={voucher.code}>
                      {voucher.code} - {voucher.type === 'percent'
                        ? `${voucher.value ?? 0}%`
                        : `${formatCurrency(voucher.value)} VNĐ`}
                    </option>
                  ))}
                </Form.Select>
                <div className="d-flex gap-2 mt-2">
                  <Button variant="info" size="sm" onClick={() => applyVoucher('shipping')} disabled={!shippingVoucherCode || loading}>
                    Áp dụng
                  </Button>
                  {shippingVoucherInfo && (
                    <div className="mt-1 text-info">
                      ✅ {shippingVoucherInfo.code}
                      <Button variant="link" size="sm" onClick={() => applyVoucher('remove_shipping')}>[Hủy]</Button>
                    </div>
                  )}
                </div>
              </Form.Group>

              <p>Tạm tính: {formatCurrency(totals.subtotal)} VNĐ</p>
              <p>Phí vận chuyển: {formatCurrency(totals.shipping)} VNĐ</p>
              <p>Thuế: {formatCurrency(totals.tax)} VNĐ</p>
              {totals.discount > 0 && (
                <p className="text-success">Giảm giá: -{formatCurrency(totals.discount)} VNĐ</p>
              )}
              <h5 className="fw-bold">Tổng cộng: {formatCurrency(totals.total)} VNĐ</h5>
            </>
          )}
        </Col>
      </Row>
    </Container>
  );
}
