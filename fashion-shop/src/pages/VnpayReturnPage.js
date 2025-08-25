import { useEffect, useState } from 'react';
import { useLocation, useNavigate } from 'react-router-dom';
import axios from 'axios';
import { ArrowLeft } from 'react-bootstrap-icons';
import { useCart } from '../context/CartContext';

export default function VnpayReturn() {
  const location = useLocation();
  const navigate = useNavigate();
  const token = localStorage.getItem('token');

  const [urlData, setUrlData] = useState({});
  const [orderDetail, setOrderDetail] = useState(null);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState('');
  const [fetched, setFetched] = useState(false);

  const { removeSelectedItems } = useCart();

  useEffect(() => {
    const query = new URLSearchParams(location.search);
    const data = {
      message: query.get('message'),
      order_id: query.get('orderId') || query.get('order_id'),
      order_number: query.get('order_number'),
      status: query.get('status'),
      payment_status: query.get('payment_status'),
      transaction_id: query.get('transaction_id'),
    };
    setUrlData(data);
  }, [location.search]);

  useEffect(() => {
    const fetchOrder = async () => {
      if (!token) return setError('Bạn chưa đăng nhập');
      if (!urlData.order_id || fetched) return;

      try {
        const res = await axios.get(
          `${process.env.REACT_APP_API_URL}/payment/vnpay/verify?orderId=${urlData.order_id}`,
          { headers: { Authorization: `Bearer ${token}` } }
        );
        setOrderDetail(res.data.data);
        if (res.data.success) {
          await removeSelectedItems();
          localStorage.removeItem('buy_now');
        }
      } catch {
        setError('Không thể tải chi tiết đơn hàng.');
      } finally {
        setFetched(true);
        setLoading(false);
      }
    };

    fetchOrder();
  }, [urlData.order_id, token, removeSelectedItems, fetched]);

  if (loading) {
    return (
      <div style={{ minHeight: '100vh', display: 'flex', justifyContent: 'center', alignItems: 'center', background: 'linear-gradient(to bottom right, #edf2f7, #ebf4ff)' }}>
        <div style={{ borderTop: '4px solid #3182ce', borderRight: '4px solid transparent', borderBottom: '4px solid transparent', borderLeft: '4px solid transparent', width: '64px', height: '64px', animation: 'spin 1s linear infinite' }} />
      </div>
    );
  }

  if (error) {
    return (
      <div style={{ minHeight: '100vh', display: 'flex', justifyContent: 'center', alignItems: 'center', background: 'linear-gradient(to bottom right, #edf2f7, #ebf4ff)', padding: '1rem' }}>
        <div style={{ maxWidth: '28rem', width: '100%', backgroundColor: 'white', padding: '1.5rem', borderRadius: '1rem', boxShadow: '0 4px 6px rgba(0, 0, 0, 0.1)' }}>
          <div style={{ textAlign: 'center' }}>
            <svg style={{ margin: '0 auto 1rem', color: '#e53e3e' }} width="40" height="40" fill="currentColor" viewBox="0 0 24 24">
              <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-2h2v2zm0-4h-2V7h2v6z"/>
            </svg>
            <h2 style={{ fontSize: '1.5rem', fontWeight: 'bold', color: '#2d3748', marginBottom: '0.5rem' }}>Đã xảy ra lỗi</h2>
            <p style={{ color: '#4a5568', marginBottom: '1.5rem' }}>{error}</p>
            <button
              onClick={() => navigate('../orders')}
              style={{ display: 'inline-flex', alignItems: 'center', padding: '0.75rem 1.5rem', backgroundColor: '#feb2b2', color: '#742a2a', borderRadius: '9999px', border: 'none', cursor: 'pointer', transition: 'background-color 0.3s' }}
              onMouseOver={e => e.target.style.backgroundColor = '#f56565'}
              onMouseOut={e => e.target.style.backgroundColor = '#feb2b2'}
            >
              <ArrowLeft style={{ marginRight: '0.5rem' }} />
              Quay lại trang đơn hàng
            </button>
          </div>
        </div>
      </div>
    );
  }

  const isPaid = orderDetail?.payment_status === 'paid';

  return (
    <div style={{ minHeight: '100vh', padding: '3rem 1rem' }}>
      <div style={{ maxWidth: '80rem', margin: '0 auto' }}>
        <div style={{ backgroundColor: 'white', borderRadius: '1.5rem', boxShadow: '0 10px 15px rgba(0, 0, 0, 0.1)', overflow: 'hidden' }}>
          <div
            style={{
              textAlign: 'center',
              padding: '1.5rem',
              background: isPaid ? 'linear-gradient(to right, #48bb78, #38a169)' : 'linear-gradient(to right, #f56565, #e53e3e)',
              color: 'white'
            }}
          >
            <h2 style={{ fontSize: '1.875rem', fontWeight: 'bold' }}>
              {isPaid ? "Thanh toán VnPay thành công" : "Thanh toán VnPay thất bại"}
            </h2>
          </div>
          <div style={{ padding: '2rem' }}>
            <div style={{ display: 'grid', gridTemplateColumns: '1fr', gap: '2rem', ...(window.innerWidth >= 768 && { gridTemplateColumns: '1fr 1fr' }) }}>
              {/* Order Information */}
              <div>
                <h3 style={{ fontSize: '1.5rem', fontWeight: '600', color: '#2d3748', marginBottom: '1rem' }}>Thông tin đơn hàng</h3>
                <div style={{ backgroundColor: '#f7fafc', padding: '1.25rem', borderRadius: '0.75rem', boxShadow: '0 2px 4px rgba(0, 0, 0, 0.05)' }}>
                  <ul style={{ listStyle: 'none', padding: 0, margin: 0, display: 'flex', flexDirection: 'column', gap: '0.75rem' }}>
                    <li style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center' }}>
                      <span style={{ fontWeight: '500', color: '#4a5568' }}>Mã đơn hàng:</span>
                      <span style={{ color: '#2d3748' }}>{orderDetail?.order_number}</span>
                    </li>
                    <li style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center' }}>
                      <span style={{ fontWeight: '500', color: '#4a5568' }}>Trạng thái đơn hàng:</span>
                      <span style={{ backgroundColor: '#ebf4ff', color: '#2b6cb0', padding: '0.25rem 0.75rem', borderRadius: '9999px', fontSize: '0.875rem', fontWeight: '500' }}>
                        {orderDetail?.status === 'processing' ? 'Đang xử lý' : 'Không xác định'}
                      </span>
                    </li>
                    <li style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center' }}>
                      <span style={{ fontWeight: '500', color: '#4a5568' }}>Thanh toán:</span>
                      <span style={{ backgroundColor: isPaid ? '#c6f6d5' : '#fefcbf', color: isPaid ? '#22543d' : '#744210', padding: '0.25rem 0.75rem', borderRadius: '9999px', fontSize: '0.875rem', fontWeight: '500' }}>
                        {isPaid ? 'Đã thanh toán' : 'Chưa thanh toán'}
                      </span>
                    </li>
                    <li style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center' }}>
                      <span style={{ fontWeight: '500', color: '#4a5568' }}>Người nhận:</span>
                      <span style={{ color: '#2d3748' }}>{orderDetail?.user?.name}</span>
                    </li>
                    <li style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center' }}>
                      <span style={{ fontWeight: '500', color: '#4a5568' }}>SĐT:</span>
                      <span style={{ color: '#2d3748' }}>{orderDetail?.user?.phone}</span>
                    </li>
                    <li style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center' }}>
                      <span style={{ fontWeight: '500', color: '#4a5568' }}>Địa chỉ:</span>
                      <span style={{ color: '#718096' }}>{orderDetail?.shipping_address}</span>
                    </li>
                    <li style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center' }}>
                      <span style={{ fontWeight: '500', color: '#4a5568' }}>Email:</span>
                      <span style={{ color: '#2d3748' }}>{orderDetail?.user?.email}</span>
                    </li>
                  </ul>
                </div>
              </div>

              {/* Product List */}
              <div>
                <h3 style={{ fontSize: '1.5rem', fontWeight: '600', color: '#2d3748', marginBottom: '1rem' }}>Sản phẩm</h3>
                <div style={{ display: 'flex', flexDirection: 'column', gap: '1rem' }}>
                  {orderDetail?.items?.map((item, idx) => {
                    const variant = item.product_variant || {};
                    const product = variant.product || {};
                    const color = variant.color || {};
                    const size = variant.size || {};

                    return (
                      <div key={idx} style={{ display: 'flex', backgroundColor: 'white', padding: '1rem', borderRadius: '0.75rem', boxShadow: '0 2px 4px rgba(0, 0, 0, 0.05)', transition: 'box-shadow 0.3s' }} onMouseOver={e => e.target.style.boxShadow = '0 4px 6px rgba(0, 0, 0, 0.1)'} onMouseOut={e => e.target.style.boxShadow = '0 2px 4px rgba(0, 0, 0, 0.05)'}>
                        <img
                          src={variant.thumbnail}
                          alt={product.name}
                          style={{ width: '8rem', height: '8rem', objectFit: 'cover', borderRadius: '0.5rem', marginRight: '1rem' }}
                        />
                        <div style={{ flex: 1 }}>
                          <h4 style={{ fontWeight: '600', color: '#2d3748' }}>{product.name}</h4>
                          <p style={{ fontSize: '0.875rem', color: '#718096' }}>Màu: {color.name}</p>
                          <p style={{ fontSize: '0.875rem', color: '#718096' }}>Size: {size.name}</p>
                          <p style={{ fontSize: '0.875rem', color: '#718096' }}>Số lượng: {item.quantity}</p>
                          <p style={{ fontWeight: '600', color: '#2d3748', marginTop: '0.25rem' }}>Giá: {Number(item.price).toLocaleString()} ₫</p>
                          <p style={{ color: '#718096', marginTop: '0.25rem' }}>Thuế (10%): {Number(orderDetail?.tax).toLocaleString()} ₫</p>
                          <p style={{ color: '#718096', marginTop: '0.25rem' }}>Phí ship: {Number(orderDetail?.shipping).toLocaleString()} ₫</p>
                          <p style={{ color: '#718096', marginTop: '0.25rem' }}>Áp dụng Voucher: {Number(orderDetail?.discount_amount).toLocaleString()} ₫</p>
                          <p style={{ color: '#48bb78', fontWeight: '500', marginTop: '0.25rem' }}>
                            Tổng: {Number(orderDetail?.total).toLocaleString()} ₫
                          </p>
                        </div>
                      </div>
                    );
                  })}
                </div>
              </div>
            </div>
          </div>
        </div>

        <div style={{ textAlign: 'center', marginTop: '2rem' }}>
          <button
            onClick={() => navigate('../orders')}
            style={{ display: 'inline-flex', alignItems: 'center', padding: '0.75rem 1.5rem', backgroundColor: '#3182ce', color: 'white', borderRadius: '9999px', border: 'none', cursor: 'pointer', transition: 'background-color 0.3s' }}
            onMouseOver={e => e.target.style.backgroundColor = '#2b6cb0'}
            onMouseOut={e => e.target.style.backgroundColor = '#3182ce'}
          >
            <ArrowLeft style={{ marginRight: '0.5rem' }} />
            Quay về đơn hàng của tôi
          </button>
        </div>
      </div>
    </div>
  );
}
