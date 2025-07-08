import { useEffect, useState } from 'react';
import {
  Container, Card, Row, Col, Button, Badge, Spinner, Alert, Image
} from 'react-bootstrap';
import { Link, useNavigate } from 'react-router-dom';
import axios from 'axios';

const formatDate = (iso) => {
  const d = new Date(iso);
  return `${d.getDate().toString().padStart(2, '0')}/${(d.getMonth() + 1).toString().padStart(2, '0')}/${d.getFullYear()}`;
};

const formatCurrency = (amount) => Number(amount).toLocaleString('vi-VN') + '₫';

const STATUS_LABELS = {
  pending: 'Chờ xác nhận',
  processing: 'Đang xử lý',
  picking: 'Đang lấy hàng',
  shipping: 'Đang giao hàng',
  shipped: 'Đã giao hàng',
  delivered: 'Đã nhận hàng',
  completed: 'Hoàn thành',
  cancelled: 'Đã hủy',
  failed: 'Thất bại',
};

const STATUS_VARIANTS = {
  pending: 'warning',
  processing: 'info',
  picking: 'primary',
  shipping: 'primary',
  shipped: 'info',
  delivered: 'success',
  completed: 'success',
  cancelled: 'secondary',
  failed: 'danger',
};

const PAYMENT_STATUS_LABELS = {
  paid: 'Đã thanh toán',
  unpaid: 'Chưa thanh toán',
  pending: 'Đang xử lý',
  failed: 'Thất bại',
};

const PAYMENT_STATUS_VARIANTS = {
  paid: 'success',
  unpaid: 'danger',
  pending: 'warning',
  failed: 'danger',
};
const PAYMENT_METHOD_LABELS = {
  cod: 'Thanh toán khi nhận hàng',
  momo: 'Ví Momo',
  // vnpay: 'VNPay',
  // zalopay: 'ZaloPay',
  // bank: 'Chuyển khoản ngân hàng',
  // other: 'Khác'
};


export default function MyOrdersPage() {
  const [orders, setOrders] = useState([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState('');
  const navigate = useNavigate();

  useEffect(() => {
    const fetchOrders = async () => {
      const token = localStorage.getItem('token');
      if (!token) return navigate('/login');

      try {
        setLoading(true);
        const res = await axios.get(`${process.env.REACT_APP_API_URL}/orders`, {
          headers: { Authorization: `Bearer ${token}` }
        });
        setOrders(res.data.data?.data || []);
      } catch (err) {
        setError('Không thể tải đơn hàng. Vui lòng thử lại.');
        if (err.response?.status === 401) {
          localStorage.removeItem('token');
          navigate('/login');
        }
      } finally {
        setLoading(false);
      }
    };

    fetchOrders();
  }, [navigate]);

  const handleConfirmReceived = async (orderId) => {
    const token = localStorage.getItem('token');
    if (!window.confirm('Bạn xác nhận đã nhận hàng?')) return;

    try {
      await axios.post(`${process.env.REACT_APP_API_URL}/orders/${orderId}/confirm-received`, {}, {
        headers: { Authorization: `Bearer ${token}` }
      });
      setOrders(prev =>
        prev.map(order =>
          order.id === orderId ? { ...order, status: 'delivered' } : order
        )
      );
      alert('Xác nhận thành công!');
    } catch {
      alert('Thất bại. Vui lòng thử lại.');
    }
  };

  return (
    <Container className="py-4">
      <h3 className="mb-4">Đơn hàng của tôi</h3>

      {loading ? (
        <div className="text-center"><Spinner animation="border" /></div>
      ) : error ? (
        <Alert variant="danger">{error}</Alert>
      ) : orders.length === 0 ? (
        <Alert variant="info">Bạn chưa có đơn hàng nào.</Alert>
      ) : (
        orders.map(order => (
          <Card className="mb-4 shadow-sm" key={order.id}>
            <Card.Header className="d-flex justify-content-between align-items-center">
              <div>
                <strong>Mã đơn:</strong> #{order.order_number || order.id} &nbsp;|&nbsp;
                <strong>Ngày đặt:</strong> {formatDate(order.created_at)} <br />
                <strong>Khách hàng:</strong> {order.user?.name || 'Không rõ'}
              </div>
              <Badge bg={STATUS_VARIANTS[order.status] || 'secondary'}>
                {STATUS_LABELS[order.status] || 'Không rõ'}
              </Badge>
            </Card.Header>


            <Card.Body>
              {order.items.map(item => (
                <Row key={item.id} className="align-items-center mb-3">
                  <Col xs={2}>
                    <Image
                      src={
                        item.product_variant?.img ||
                        item.product_variant?.product?.img ||
                        'https://via.placeholder.com/60x60?text=No+Image'
                      }
                      rounded
                      style={{ width: 60, height: 60, objectFit: 'cover' }}
                    />
                  </Col>
                  <Col xs={7}>
                    <div>{item.product_variant?.product?.name}</div>
                    <small className="text-muted">
                      Phân loại: {item.product_variant?.color?.name || '—'} / {item.product_variant?.size?.name || '—'}
                    </small>
                  </Col>
                  <Col xs={3} className="text-end">
                    <div>{formatCurrency(item.sale_price || item.price)}</div>
                    <small>Số lượng: {item.quantity}</small>
                  </Col>
                </Row>
              ))}
            </Card.Body>

            <Card.Footer className="d-flex justify-content-between align-items-center">
              <div>
                {order.status !== 'cancelled' && (
                  <>
                    <strong>Thanh toán:</strong>{' '}
                    <Badge bg={PAYMENT_STATUS_VARIANTS[order.payment_status] || 'secondary'}>
                      {PAYMENT_STATUS_LABELS[order.payment_status] || 'Không rõ'}
                    </Badge>{' '}
                    {order.payment_method && (
                      <span className="ms-2">
                        ({PAYMENT_METHOD_LABELS[order.payment_method] || order.payment_method})
                      </span>
                    )}
                    <span className="ms-2 text-danger fw-bold">
                      {formatCurrency(order.total ?? order.total_amount ?? 0)}
                    </span>

                  </>
                )}
              </div>
              <div>
                <Link to={`/orders/${order.id}`}>
                  <Button variant="outline-primary" size="sm" className="me-2">
                    Chi tiết
                  </Button>
                </Link>

                {order.status === 'shipped' && (
                  <Button variant="success" size="sm" onClick={() => handleConfirmReceived(order.id)}>
                    Xác nhận nhận hàng
                  </Button>
                )}

                {order.status === 'delivered' && (
                  <Link to={`/review/${order.id}`}>
                    <Button variant="warning" size="sm">Đánh giá</Button>
                  </Link>
                )}
              </div>
            </Card.Footer>

          </Card>
        ))
      )}
    </Container>
  );
}
