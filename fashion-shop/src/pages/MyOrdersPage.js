import { useEffect, useState } from 'react';
import { Container, Table, Spinner, Alert, Button, Badge } from 'react-bootstrap';
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
              <th>Mã đơn</th>
              <th>Ngày đặt</th>
              <th>Phương thức</th>
              <th>Trạng thái</th>
              <th>Tổng tiền</th>
              <th>Hành động</th>
            </tr>
          </thead>
          <tbody>
            {orders.map(order => (
              <tr key={order.id}>
                <td>{order.order_number || order.id}</td>
                <td>{formatDate(order.created_at)}</td>
                <td>{getPaymentMethodLabel(order.payment_method)}</td>
                <td>
                  <Badge bg={STATUS_VARIANTS[order.status] || 'secondary'}>
                    {STATUS_LABELS[order.status] || 'Không rõ'}
                  </Badge>
                </td>
                <td>{formatCurrency(order.total)}</td>
                <td>
                  <Link to={`/orders/${order.id}`}>
                    <Button variant="primary" size="sm">Xem chi tiết</Button>
                  </Link>
                </td>
              </tr>
            ))}
          </tbody>
        </Table>
      )}
    </Container>
  );
}
