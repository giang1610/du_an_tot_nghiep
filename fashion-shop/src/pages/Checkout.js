import { useState, useMemo, useEffect } from 'react';
import { useNavigate, useLocation } from 'react-router-dom';
import axios from 'axios';
import { toast } from "react-toastify";
import { useAuth } from '../context/AuthContext';
import { useCart } from '../context/CartContext';

// Helper
const formatCurrency = (num) => (num ?? 0).toLocaleString();

const ProductSummary = ({ items }) => {
  if (!items.length) return <p style={{ color: '#4a5568' }}>Bạn chưa chọn sản phẩm nào để đặt hàng.</p>;
  return (
    <>
      {items.map(item => (
        <div key={item.id || item.product_variant_id || item.variant_id} style={{ marginBottom: '1rem', backgroundColor: 'white', padding: '1rem', borderRadius: '0.75rem', boxShadow: '0 2px 4px rgba(0, 0, 0, 0.05)', transition: 'box-shadow 0.3s' }} onMouseOver={e => e.target.style.boxShadow = '0 4px 6px rgba(0, 0, 0, 0.1)'} onMouseOut={e => e.target.style.boxShadow = '0 2px 4px rgba(0, 0, 0, 0.05)'}>
          <div style={{ display: 'flex', alignItems: 'center' }}>
            <img
              src={item.image}
              alt={item.product_name || item.name}
              width={80}
              height={80}
              style={{ borderRadius: '0.5rem', objectFit: 'cover', marginRight: '1rem' }}
            />
            <div>
              <h4 style={{ fontWeight: '600', color: '#2d3748' }}>{item.product_name || item.name}</h4>
              <p style={{ fontSize: '0.875rem', color: '#718096' }}>
                Số lượng: {item.quantity} <br />
                Giá: {formatCurrency(item.price)} VNĐ <br />
                {item.color && <>Màu: {item.color}<br /></>}
                {item.size && <>Size: {item.size}<br /></>}
                {item.stock === 0 && (
                  <span style={{ color: '#e53e3e', fontWeight: 'bold' }}>Sản phẩm đã hết hàng</span>
                )}
                {item.quantity > item.stock && item.stock > 0 && (
                  <span style={{ color: '#d69e2e', fontWeight: 'bold' }}>
                    Chỉ còn {item.stock} sản phẩm trong kho
                  </span>
                )}
              </p>
            </div>
          </div>
        </div>
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
      console.log('📦 Gửi dữ liệu đặt hàng:', payload);
      
      setLoading(true);

      if (form.payment_method === 'momo') {
        const { data } = await axios.post(
          `${process.env.REACT_APP_API_URL}/payment/momo`,
          payload,
          { headers: { Authorization: `Bearer ${token}` } }
        );
        if (data?.data?.payment_url) {
          localStorage.removeItem('buy_now');
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
    <div style={{ minHeight: '100vh', background: 'linear-gradient(to bottom right, #edf2f7, #ebf4ff)', padding: '3rem 1rem' }}>
      <div style={{ maxWidth: '80rem', margin: '0 auto' }}>
        <h2 style={{ fontSize: '1.875rem', fontWeight: 'bold', color: '#2d3748', marginBottom: '1.5rem', textAlign: 'center' }}>Thanh toán</h2>
        <div style={{ display: 'grid', gridTemplateColumns: '1fr', gap: '2rem', ...(window.innerWidth >= 768 && { gridTemplateColumns: '1fr 1fr' }) }}>
          <div style={{ backgroundColor: 'white', borderRadius: '0.75rem', boxShadow: '0 10px 15px rgba(0, 0, 0, 0.1)', padding: '1.5rem' }}>
            {error && <div style={{ backgroundColor: '#fee2e2', color: '#991b1b', padding: '0.75rem', borderRadius: '0.5rem', marginBottom: '1rem' }}>{error}</div>}
            {success && <div style={{ backgroundColor: '#d1fae5', color: '#065f46', padding: '0.75rem', borderRadius: '0.5rem', marginBottom: '1rem' }}>{success}</div>}

            <form onSubmit={handleSubmit} noValidate>
              {['name', 'phone', 'address'].map(field => (
                <div style={{ marginBottom: '1rem' }} key={field}>
                  <label style={{ display: 'block', fontSize: '0.875rem', fontWeight: '500', color: '#4a5568', marginBottom: '0.25rem' }}>
                    {field === 'name' && 'Họ tên'}
                    {field === 'phone' && 'Số điện thoại'}
                    {field === 'address' && 'Địa chỉ'}
                  </label>
                  <input
                    type="text"
                    name={field}
                    value={form[field]}
                    onChange={e => setField(field, e.target.value)}
                    style={{ width: '100%', padding: '0.5rem', borderRadius: '0.375rem', border: formErrors[field] ? '1px solid #ef4444' : '1px solid #e2e8f0', outline: 'none', transition: 'border-color 0.3s' }}
                    required
                  />
                  {formErrors[field] && <p style={{ color: '#ef4444', fontSize: '0.75rem', marginTop: '0.25rem' }}>{formErrors[field]}</p>}
                </div>
              ))}

              <div style={{ marginBottom: '1rem' }}>
                <label style={{ display: 'block', fontSize: '0.875rem', fontWeight: '500', color: '#4a5568', marginBottom: '0.25rem' }}>Email</label>
                <input
                  type="email"
                  name="email"
                  value={form.email}
                  onChange={e => setField('email', e.target.value)}
                  style={{ width: '100%', padding: '0.5rem', borderRadius: '0.375rem', border: '1px solid #e2e8f0', outline: 'none', backgroundColor: '#f7fafc', cursor: 'not-allowed' }}
                  disabled
                />
              </div>

              <div style={{ marginBottom: '1rem' }}>
                <label style={{ display: 'block', fontSize: '0.875rem', fontWeight: '500', color: '#4a5568', marginBottom: '0.25rem' }}>Ghi chú</label>
                <textarea
                  rows={3}
                  name="notes"
                  value={form.notes}
                  onChange={e => setField('notes', e.target.value)}
                  style={{ width: '100%', padding: '0.5rem', borderRadius: '0.375rem', border: '1px solid #e2e8f0', outline: 'none', transition: 'border-color 0.3s' }}
                />
              </div>

              <div style={{ marginBottom: '1rem' }}>
                <label style={{ display: 'block', fontSize: '0.875rem', fontWeight: '500', color: '#4a5568', marginBottom: '0.25rem' }}>Phương thức thanh toán</label>
                <select
                  value={form.payment_method}
                  onChange={e => setField('payment_method', e.target.value)}
                  style={{ width: '100%', padding: '0.5rem', borderRadius: '0.375rem', border: '1px solid #e2e8f0', outline: 'none', transition: 'border-color 0.3s' }}
                >
                  <option value="cod">Thanh toán khi nhận hàng (COD)</option>
                  <option value="momo">Thanh toán MoMo</option>
                  <option value="vnpay">Thanh toán VNPay</option>
                </select>
              </div>

              <button
                type="submit"
                disabled={loading}
                style={{ width: '100%', padding: '0.75rem', backgroundColor: '#3182ce', color: 'white', borderRadius: '0.5rem', border: 'none', cursor: loading ? 'not-allowed' : 'pointer', transition: 'background-color 0.3s' }}
                onMouseOver={e => !loading && (e.target.style.backgroundColor = '#2b6cb0')}
                onMouseOut={e => !loading && (e.target.style.backgroundColor = '#3182ce')}
              >
                {loading ? (
                  <>
                    <div style={{ borderTop: '2px solid white', borderRight: '2px solid transparent', borderBottom: '2px solid transparent', borderLeft: '2px solid transparent', width: '1.25rem', height: '1.25rem', animation: 'spin 1s linear infinite', marginRight: '0.5rem' }} />
                    Đang xử lý...
                  </>
                ) : form.payment_method === 'momo' ? (
                  'Thanh toán qua MoMo'
                ) : form.payment_method === 'vnpay' ? (
                  'Thanh toán qua VNPay'
                ) : (
                  'Xác nhận đặt hàng'
                )}
              </button>
            </form>
          </div>

          <div style={{ backgroundColor: 'white', borderRadius: '0.75rem', boxShadow: '0 10px 15px rgba(0, 0, 0, 0.1)', padding: '1.5rem' }}>
            <h3 style={{ fontSize: '1.25rem', fontWeight: '600', color: '#2d3748', marginBottom: '1rem' }}>Sản phẩm trong giỏ</h3>
            <ProductSummary items={selectedItems} />

            {selectedItems.length > 0 && (
              <>
                <hr style={{ margin: '1rem 0', borderColor: '#e2e8f0' }} />

                <div style={{ marginBottom: '1rem' }}>
                  <label style={{ display: 'block', fontSize: '0.875rem', fontWeight: '500', color: '#4a5568', marginBottom: '0.25rem' }}>Mã giảm giá sản phẩm</label>
                  <select
                    value={productVoucherCode}
                    onChange={e => setProductVoucherCode(e.target.value)}
                    style={{ width: '100%', padding: '0.5rem', borderRadius: '0.375rem', border: '1px solid #e2e8f0', outline: 'none', transition: 'border-color 0.3s' }}
                  >
                    <option value="">-- Không áp dụng --</option>
                    {availableProductVouchers.map(voucher => (
                      <option key={voucher.code} value={voucher.code}>
                        {voucher.code} - {voucher.type === 'percent'
                          ? `${voucher.value ?? 0}%`
                          : `${formatCurrency(voucher.value)} VNĐ`}
                      </option>
                    ))}
                  </select>
                  <div style={{ display: 'flex', gap: '0.5rem', marginTop: '0.5rem' }}>
                    <button
                      onClick={() => applyVoucher('product')}
                      disabled={!productVoucherCode || loading}
                      style={{ padding: '0.375rem 0.75rem', backgroundColor: '#34d399', color: 'white', borderRadius: '0.375rem', border: 'none', cursor: loading || !productVoucherCode ? 'not-allowed' : 'pointer', transition: 'background-color 0.3s' }}
                      onMouseOver={e => !loading && !productVoucherCode && (e.target.style.backgroundColor = '#10b981')}
                      onMouseOut={e => !loading && !productVoucherCode && (e.target.style.backgroundColor = '#34d399')}
                    >
                      Áp dụng
                    </button>
                    {productVoucherInfo && (
                      <div style={{ marginTop: '0.25rem', color: '#10b981', display: 'flex', alignItems: 'center' }}>
                        ✅ {productVoucherInfo.code}
                        <button
                          onClick={() => applyVoucher('remove_product')}
                          style={{ marginLeft: '0.5rem', fontSize: '0.75rem', color: '#065f46', textDecoration: 'underline', cursor: 'pointer' }}
                        >
                          [Hủy]
                        </button>
                      </div>
                    )}
                  </div>
                </div>

                <div style={{ marginBottom: '1rem' }}>
                  <label style={{ display: 'block', fontSize: '0.875rem', fontWeight: '500', color: '#4a5568', marginBottom: '0.25rem' }}>Mã miễn phí vận chuyển</label>
                  <select
                    value={shippingVoucherCode}
                    onChange={e => setShippingVoucherCode(e.target.value)}
                    style={{ width: '100%', padding: '0.5rem', borderRadius: '0.375rem', border: '1px solid #e2e8f0', outline: 'none', transition: 'border-color 0.3s' }}
                  >
                    <option value="">-- Không áp dụng --</option>
                    {availableShippingVouchers.map(voucher => (
                      <option key={voucher.code} value={voucher.code}>
                        {voucher.code} - {voucher.type === 'percent'
                          ? `${voucher.value ?? 0}%`
                          : `${formatCurrency(voucher.value)} VNĐ`}
                      </option>
                    ))}
                  </select>
                  <div style={{ display: 'flex', gap: '0.5rem', marginTop: '0.5rem' }}>
                    <button
                      onClick={() => applyVoucher('shipping')}
                      disabled={!shippingVoucherCode || loading}
                      style={{ padding: '0.375rem 0.75rem', backgroundColor: '#60a5fa', color: 'white', borderRadius: '0.375rem', border: 'none', cursor: loading || !shippingVoucherCode ? 'not-allowed' : 'pointer', transition: 'background-color 0.3s' }}
                      onMouseOver={e => !loading && !shippingVoucherCode && (e.target.style.backgroundColor = '#3b82f6')}
                      onMouseOut={e => !loading && !shippingVoucherCode && (e.target.style.backgroundColor = '#60a5fa')}
                    >
                      Áp dụng
                    </button>
                    {shippingVoucherInfo && (
                      <div style={{ marginTop: '0.25rem', color: '#3b82f6', display: 'flex', alignItems: 'center' }}>
                        ✅ {shippingVoucherInfo.code}
                        <button
                          onClick={() => applyVoucher('remove_shipping')}
                          style={{ marginLeft: '0.5rem', fontSize: '0.75rem', color: '#1e40af', textDecoration: 'underline', cursor: 'pointer' }}
                        >
                          [Hủy]
                        </button>
                      </div>
                    )}
                  </div>
                </div>

                <p style={{ color: '#4a5568' }}>Tạm tính: {formatCurrency(totals.subtotal)} VNĐ</p>
                <p style={{ color: '#4a5568' }}>Phí vận chuyển: {formatCurrency(totals.shipping)} VNĐ</p>
                <p style={{ color: '#4a5568' }}>Thuế: {formatCurrency(totals.tax)} VNĐ</p>
                {totals.discount > 0 && (
                  <p style={{ color: '#10b981' }}>Giảm giá: -{formatCurrency(totals.discount).replace(/\.00$/, '')} VNĐ</p>
                )}
                <h4 style={{ fontSize: '1.25rem', fontWeight: 'bold', color: '#e53e3e', marginTop: '0.5rem' }}>Tổng cộng: {formatCurrency(totals.total)} VNĐ</h4>
              </>
            )}
          </div>
        </div>
      </div>
    </div>
  );
}