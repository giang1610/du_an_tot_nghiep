import { useEffect, useState } from 'react';
import { Container, Table, Spinner, Alert, Button, Badge, Image } from 'react-bootstrap';
import { Link, useNavigate } from 'react-router-dom';
import axios from 'axios';

const formatDate = (isoDate) => {
  const date = new Date(isoDate);
  return `${date.getDate().toString().padStart(2, '0')}/${(date.getMonth() + 1)
    .toString().padStart(2, '0')}/${date.getFullYear()}`;
};

const formatCurrency = (amount) =>
  Number(amount).toLocaleString('vi-VN') + '₫';

const getPaymentMethodLabel = (method) =>
  method === 'cod' ? 'Thanh toán khi nhận hàng' : 'Chuyển khoản';

const getPaymentStatusLabel = (status) =>
  status === 'paid' ? 'Đã thanh toán' : 'Chưa thanh toán';

const getPaymentStatusVariant = (status) =>
  status === 'paid' ? 'success' : 'warning';

const STATUS_LABELS = {
  pending: 'Chờ xử lý',
  processing: 'Đang xử lý',
  completed: 'Hoàn thành',
  cancelled: 'Đã hủy',
  failed: 'Thất bại',
};

const STATUS_VARIANTS = {
  pending: 'warning',
  processing: 'info',
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
              <th>Người đặt</th>
              <th>Địa chỉ</th>
              <th>Phương thức</th>
              <th>Thanh toán</th>
              <th>Trạng thái</th>
              <th>Tổng tiền</th>
              <th>Hành động</th>
            </tr>
          </thead>
          <tbody>
            {orders.map(order => {
              const firstItem = order.order_items?.[0];
              const imageUrl = firstItem?.product?.images?.[0]?.url || '/default.jpg';

              return (
                <tr key={order.id}>
                  <td>
                    <Image
                      src={`${process.env.REACT_APP_IMAGE_BASE_URL}${imageUrl}`}
                      width={60}
                      height={60}
                      rounded
                    />
                  </td>
                  <td>{formatDate(order.created_at)}</td>
                  <td>{order.customer_name}</td>
                  <td>{order.address}</td>
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
                  <td>{formatCurrency(order.total)}</td>
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
