import { useEffect, useState } from "react";
import { useSearchParams, useNavigate } from "react-router-dom";
import axios from "axios";
import { ArrowLeft, CheckCircleFill, XCircleFill } from "react-bootstrap-icons";
import { useCart } from "../context/CartContext";

const STATUS_LABELS = {
  pending: 'Chờ xử lý',
  processing: 'Đang xử lý',
  picking: 'Đang lấy hàng',
  shipper_arrived: 'Shipper đến lấy hàng',
  in_warehouse: 'Hàng về kho',
  shipping: 'Đang giao hàng',
  shipped: 'Đã giao hàng',
  completed: 'Hoàn thành',
  return_requested: 'Đã yêu cầu hoàn hàng',
  returned: 'Hoàn hàng',
  cancelled: 'Đã hủy',
  failed: 'Giao hàng thất bại',
  failed_1: 'Giao hàng thất bại lần 1',
  failed_2: 'Giao hàng thất bại lần 2',
  restocked: 'Hàng đã trả kho',
};

export default function MomoReturn() {
  const [searchParams] = useSearchParams();
  const navigate = useNavigate();
  const { removeSelectedItems } = useCart();
  const [loading, setLoading] = useState(true);
  const [orderDetail, setOrderDetail] = useState(null);
  const [error, setError] = useState("");

  useEffect(() => {
    const fetchOrder = async () => {
      const orderId = searchParams.get("orderId");
      const resultCode = searchParams.get("resultCode");

      if (!orderId || !resultCode) {
        setError("URL không hợp lệ");
        setLoading(false);
        return;
      }

      try {
        const res = await axios.get(
          `${process.env.REACT_APP_API_URL}/payment/momo/return?orderId=${orderId}&resultCode=${resultCode}`
        );
        setOrderDetail(res.data.data);

        if (res.data?.data?.payment_status === "paid") {
          await removeSelectedItems();
          localStorage.removeItem("buy_now");
        }
      } catch (err) {
        setError(err.response?.data?.message || "Lỗi xác minh thanh toán");
      } finally {
        setLoading(false);
      }
    };

    fetchOrder();
  }, []);

  if (loading) {
    return (
      <div style={{ minHeight: '100vh', display: 'flex', justifyContent: 'center', alignItems: 'center', backgroundColor: '#f7fafc' }}>
        <div style={{ borderTop: '4px solid #3182ce', borderRight: '4px solid transparent', borderBottom: '4px solid transparent', borderLeft: '4px solid transparent', width: '64px', height: '64px', animation: 'spin 1s linear infinite' }} />
      </div>
    );
  }

  if (error) {
    return (
      <div style={{ minHeight: '100vh', display: 'flex', justifyContent: 'center', alignItems: 'center', backgroundColor: '#f7fafc', padding: '1rem' }}>
        <div style={{ maxWidth: '28rem', width: '100%', backgroundColor: 'white', padding: '1.5rem', borderRadius: '1rem', boxShadow: '0 4px 6px rgba(0, 0, 0, 0.1)' }}>
          <div style={{ textAlign: 'center' }}>
            <XCircleFill style={{ color: '#e53e3e', marginBottom: '1rem' }} size={40} />
            <h2 style={{ fontSize: '1.5rem', fontWeight: 'bold', color: '#2d3748', marginBottom: '0.5rem' }}>Đã xảy ra lỗi</h2>
            <p style={{ color: '#4a5568', marginBottom: '1.5rem' }}>{error}</p>
            <button
              onClick={() => navigate("../orders")}
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

  const isPaid = orderDetail?.payment_status === "paid";

  return (
    <div style={{ minHeight: '100vh',  padding: '3rem 1rem' }}>
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
            <div style={{ display: 'flex', justifyContent: 'center', alignItems: 'center', gap: '0.75rem' }}>
              {isPaid ? <CheckCircleFill size={36} /> : <XCircleFill size={36} />}
              <h2 style={{ fontSize: '1.875rem', fontWeight: 'bold' }}>
                {isPaid ? "Thanh toán MoMo thành công" : "Thanh toán MoMo thất bại"}
              </h2>
            </div>
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
                      <span style={{ fontWeight: '500', color: '#4a5568' }}>Trạng thái:</span>
                      <span style={{ backgroundColor: '#ebf4ff', color: '#2b6cb0', padding: '0.25rem 0.75rem', borderRadius: '9999px', fontSize: '0.875rem', fontWeight: '500' }}>
                        {STATUS_LABELS[orderDetail?.status] || orderDetail?.status || "Không xác định"}
                      </span>
                    </li>
                    <li style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center' }}>
                      <span style={{ fontWeight: '500', color: '#4a5568' }}>Thanh toán:</span>
                      <span style={{ backgroundColor: isPaid ? '#c6f6d5' : '#fefcbf', color: isPaid ? '#22543d' : '#744210', padding: '0.25rem 0.75rem', borderRadius: '9999px', fontSize: '0.875rem', fontWeight: '500' }}>
                        {isPaid ? "Đã thanh toán" : "Chưa thanh toán"}
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
                      <span style={{ fontWeight: '500', color: '#4a5568' }}>Email:</span>
                      <span style={{ color: '#2d3748' }}>{orderDetail?.user?.email}</span>
                    </li>
                    <li style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center' }}>
                      <span style={{ fontWeight: '500', color: '#4a5568' }}>Địa chỉ:</span>
                      <span style={{ color: '#718096' }}>{orderDetail?.shipping_address}</span>
                    </li>
                  </ul>
                </div>
              </div>

              {/* Product List and Payment Summary */}
              <div>
                <h3 style={{ fontSize: '1.5rem', fontWeight: '600', color: '#2d3748', marginBottom: '1rem' }}>Danh sách sản phẩm</h3>
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
                          style={{ width: '6rem', height: '6rem', objectFit: 'cover', borderRadius: '0.5rem', marginRight: '1rem' }}
                        />
                        <div style={{ flex: 1 }}>
                          <h4 style={{ fontWeight: '600', color: '#2d3748' }}>{product.name}</h4>
                          <p style={{ fontSize: '0.875rem', color: '#718096' }}>Màu: {color.name} | Size: {size.name}</p>
                          <p style={{ fontSize: '0.875rem', color: '#718096' }}>SL: {item.quantity}</p>
                          <p style={{ fontWeight: '600', color: '#2d3748', marginTop: '0.25rem' }}>
                            {Number(item.price).toLocaleString()} ₫
                          </p>
                          <p style={{ color: '#48bb78', fontWeight: '500' }}>
                            Tổng: {Number(orderDetail?.subtotal).toLocaleString()} ₫
                          </p>
                        </div>
                      </div>
                    );
                  })}
                </div>

                <div style={{ marginTop: '1.5rem', backgroundColor: '#f7fafc', padding: '1.25rem', borderRadius: '0.75rem', boxShadow: '0 2px 4px rgba(0, 0, 0, 0.05)' }}>
                  <h3 style={{ fontSize: '1.5rem', fontWeight: '600', color: '#2d3748', marginBottom: '1rem' }}>Tóm tắt thanh toán</h3>
                  <ul style={{ listStyle: 'none', padding: 0, margin: 0, display: 'flex', flexDirection: 'column', gap: '0.75rem' }}>
                    <li style={{ display: 'flex', justifyContent: 'space-between' }}>
                      <span style={{ color: '#4a5568' }}>Tạm tính:</span>
                      <span style={{ fontWeight: '600' }}>{Number(orderDetail?.subtotal || 0).toLocaleString()} ₫</span>
                    </li>
                    <li style={{ display: 'flex', justifyContent: 'space-between' }}>
                      <span style={{ color: '#4a5568' }}>Thuế (10%):</span>
                      <span style={{ fontWeight: '600' }}>{Number(orderDetail?.tax || 0).toLocaleString()} ₫</span>
                    </li>
                    <li style={{ display: 'flex', justifyContent: 'space-between' }}>
                      <span style={{ color: '#4a5568' }}>Phí vận chuyển:</span>
                      <span style={{ fontWeight: '600' }}>{Number(orderDetail?.shipping || 0).toLocaleString()} ₫</span>
                    </li>
                    {orderDetail?.discount_amount > 0 && (
                      <li style={{ display: 'flex', justifyContent: 'space-between', color: '#48bb78' }}>
                        <span>Giảm giá voucher:</span>
                        <span style={{ fontWeight: '600' }}>
                          -{Number(orderDetail?.discount_amount).toLocaleString()} ₫
                        </span>
                      </li>
                    )}
                    <li style={{ display: 'flex', justifyContent: 'space-between', fontSize: '1.25rem', fontWeight: 'bold', color: '#e53e3e', paddingTop: '0.5rem', borderTop: '1px solid #e2e8f0' }}>
                      <span>Thành tiền:</span>
                      <span>{Number(orderDetail?.total || 0).toLocaleString()} ₫</span>
                    </li>
                  </ul>
                </div>
              </div>
            </div>
          </div>
        </div>

        <div style={{ textAlign: 'center', marginTop: '2rem' }}>
          <button
            onClick={() => navigate("../orders")}
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