import { useEffect, useState } from 'react';
import { Container, Table, Spinner, Alert, Button, Badge, Image } from 'react-bootstrap';
import { Link, useNavigate } from 'react-router-dom';
import axios from 'axios';

const formatDate = (isoDate) => {
  const date = new Date(isoDate);
  return `${date.getDate().toString().padStart(2, '0')}/${(date.getMonth() + 1).toString().padStart(2, '0')}/${date.getFullYear()}`;
};

const formatCurrency = (amount) => Number(amount).toLocaleString('vi-VN') + '₫';

const getPaymentMethodLabel = (method) => {
  switch (method) {
    case 'cod': return 'Thanh toán khi nhận hàng';
    case 'momo': return 'Momo';
    case 'banking': return 'Chuyển khoản';
    default: return 'Không rõ';
  }
};

const getPaymentStatusLabel = (status) => {
  switch (status) {
    case 'paid': return 'Đã thanh toán';
    case 'unpaid': return 'Chưa thanh toán';
    case 'pending': return 'Đang xử lý';
    case 'failed': return 'Thất bại';
    default: return 'Không rõ';
  }
};

const getPaymentStatusVariant = (status) => {
  switch (status) {
    case 'paid': return 'success';
    case 'unpaid': return 'danger';
    case 'pending': return 'warning';
    case 'failed': return 'danger';
    default: return 'secondary';
  }
};

const STATUS_LABELS = {
  pending: 'Chờ xác nhận',
  processing: 'Đang xử lý',
  picking: 'Đang lấy hàng',
  shipping: 'Đang giao hàng',
  shipped: 'Đã giao',
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
  completed: 'success',
  cancelled: 'secondary',
  failed: 'danger',
};

export default function MyOrdersPage() {
  const [orders, setOrders] = useState([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState('');
  const navigate = useNavigate();

  useEffect(() => {
    const fetchOrders = async () => {
      const token = localStorage.getItem('token');
      if (!token) {
        navigate('/login');
        return;
      }

      try {
        setLoading(true);
        setError('');
        const res = await axios.get(`${process.env.REACT_APP_API_URL}/orders`, {
          headers: { Authorization: `Bearer ${token}` }
        });
        setOrders(res.data.data?.data || []);
      } catch (err) {
        if (err.response?.status === 401) {
          localStorage.removeItem('token');
          navigate('/login');
        } else {
          setError('Lỗi khi tải đơn hàng. Vui lòng thử lại sau.');
        }
      } finally {
        setLoading(false);
      }
    };

    fetchOrders();
  }, [navigate]);

  const handleConfirmReceipt = async (orderId) => {
    const token = localStorage.getItem('token');
    if (!window.confirm('Bạn xác nhận đã nhận được hàng?')) return;

    try {
      await axios.post(
        `${process.env.REACT_APP_API_URL}/orders/${orderId}/confirm-receipt`,
        {},
        { headers: { Authorization: `Bearer ${token}` } }
      );

      setOrders(prev =>
        prev.map(order =>
          order.id === orderId ? { ...order, is_received: true } : order
        )
      );

      alert('Xác nhận đã nhận hàng thành công!');
    } catch (error) {
      alert('Xác nhận thất bại. Vui lòng thử lại.');
    }
  };

  return (
    <Container className="py-4">
      <h3 className="mb-4">Đơn hàng của tôi</h3>

      {loading ? (
        <div className="text-center py-4">
          <Spinner animation="border" />
        </div>
      ) : error ? (
        <Alert variant="danger">{error}</Alert>
      ) : orders.length === 0 ? (
        <Alert variant="info">Bạn chưa có đơn hàng nào.</Alert>
      ) : (
        <Table striped bordered hover responsive>
          <thead>
            <tr>
              <th>Ảnh</th>
              <th>Ngày đặt</th>
              <th>Địa chỉ</th>
              <th>Phương thức</th>
              <th>Thanh toán</th>
              <th>Trạng thái</th>
              <th>Tổng tiền</th>
              <th>Hành động</th>
            </tr>
          </thead>
          <tbody>
            {orders.map((order) => {
              const firstItem = order.items?.[0];

              return (
                <tr key={order.id}>
                  <td>
                    <Image
                      src={
                        firstItem?.product_variant?.img ||
                        firstItem?.product_variant?.product?.img ||
                        'https://via.placeholder.com/50x50?text=No+Image'
                      }
                      alt={firstItem?.product_variant?.product?.name || 'Ảnh sản phẩm'}
                      rounded
                      style={{ width: 50, height: 50, objectFit: 'cover' }}
                    />
                  </td>
                  <td>{formatDate(order.created_at)}</td>
                  <td>{order.shipping_address}</td>
                  <td>{getPaymentMethodLabel(order.payment_method)}</td>
                  <td>
                    <Badge bg={getPaymentStatusVariant(order.payment_status)}>
                      {getPaymentStatusLabel(order.payment_status)}
                    </Badge>
                  </td>
                  <td>
                    <Badge bg={STATUS_VARIANTS[order.status] || 'secondary'}>
                      {STATUS_LABELS[order.status] || 'Không rõ'}
                    </Badge>
                  </td>
                  <td>{formatCurrency(order.total ?? order.total_amount ?? 0)}</td>
                  <td>
                    <Link to={`/orders/${order.id}`}>
                      <Button variant="primary" size="sm" className="me-2">
                        Xem chi tiết
                      </Button>
                    </Link>
                    {order.status === 'completed' && !order.is_received && (
                      <Button
                        variant="success"
                        size="sm"
                        onClick={() => handleConfirmReceipt(order.id)}
                      >
                        Đã nhận hàng
                      </Button>
                    )}
                    {order.is_received && (
                      <Badge bg="success">Đã nhận hàng</Badge>
                    )}
                  </td>
                </tr>
              );
            })}
          </tbody>
        </Table>
      )}
    </Container>
  );
}
