import { useEffect, useState, useCallback } from 'react';
import { useParams, Link } from 'react-router-dom';
import {
  Container, Table, Spinner, Alert, Button, Modal, Row, Col, Card, Form
} from 'react-bootstrap';
import axios from 'axios';

const STATUS_LABELS = {
  pending: 'Chờ xử lý',
  processing: 'Đang xử lý',
  completed: 'Hoàn thành',
  cancelled: 'Đã hủy',
  failed: 'Thất bại',
};

const PAYMENT_STATUS_LABELS = {
  paid: 'Đã thanh toán',
  unpaid: 'Chưa thanh toán',
  pending: 'Đang xử lý',
  failed: 'Thất bại',
};

const statusBadgeVariant = {
  completed: 'success',
  pending: 'warning',
  cancelled: 'secondary',
  failed: 'danger',
  processing: 'info',
};

const paymentStatusBadgeVariant = {
  paid: 'success',
  pending: 'warning',
  unpaid: 'danger',
  failed: 'danger',
};

export default function OrderDetailPage() {
  const { id } = useParams();
  const [order, setOrder] = useState(null);
  const [loading, setLoading] = useState(true);
  const [showCancelConfirm, setShowCancelConfirm] = useState(false);
  const [editingAddress, setEditingAddress] = useState(false);
  const [newAddress, setNewAddress] = useState('');
  const [updatingAddress, setUpdatingAddress] = useState(false);
  const [error, setError] = useState('');

  const token = localStorage.getItem('token');

  const fetchOrder = useCallback(async () => {
    if (!token) {
      setError('Bạn chưa đăng nhập');
      setLoading(false);
      return;
    }
    setLoading(true);
    setError('');
    try {
      const res = await axios.get(`${process.env.REACT_APP_API_URL}/orders/${id}`, {
        headers: { Authorization: `Bearer ${token}` }
      });
      setOrder(res.data.data);
      setNewAddress(res.data.data.shipping_address);
    } catch (err) {
      setError('Lỗi khi tải đơn hàng.');
      console.error(err);
    } finally {
      setLoading(false);
    }
  }, [id, token]);

  useEffect(() => {
    fetchOrder();
  }, [fetchOrder]);

  const handleCancelOrder = async () => {
    try {
      await axios.put(`${process.env.REACT_APP_API_URL}/orders/${id}/cancel`, {}, {
        headers: { Authorization: `Bearer ${token}` }
      });
      setShowCancelConfirm(false);
      fetchOrder();
    } catch (err) {
      alert('Hủy đơn hàng thất bại. Vui lòng thử lại.');
      console.error(err);
    }
  };

  const handleUpdateAddress = async () => {
    if (!newAddress.trim()) return;
    setUpdatingAddress(true);
    try {
      await axios.put(`${process.env.REACT_APP_API_URL}/orders/${id}/update-address`, {
        shipping_address: newAddress
      }, {
        headers: { Authorization: `Bearer ${token}` }
      });
      setEditingAddress(false);
      fetchOrder();
    } catch (err) {
      alert('Cập nhật địa chỉ thất bại. Vui lòng thử lại.');
      console.error(err);
    } finally {
      setUpdatingAddress(false);
    }
  };

  if (loading) return <div className="text-center py-5"><Spinner animation="border" /></div>;
  if (error) return <Alert variant="danger" className="py-5 text-center">{error}</Alert>;
  if (!order) return <Alert variant="danger" className="py-5 text-center">Không tìm thấy đơn hàng.</Alert>;

  return (
    <Container className="py-4">
      <Row>
        <Col md={6}>
          <h3>Chi tiết đơn hàng #{order.order_number || order.id}</h3>
          <p><strong>Email:</strong> {order.customer_email}</p>
          <p><strong>SĐT:</strong> {order.customer_phone}</p>

          <div>
            <strong>Địa chỉ:</strong>{' '}
            {editingAddress ? (
              <>
                <Form.Control
                  value={newAddress}
                  onChange={e => setNewAddress(e.target.value)}
                  size="sm"
                  disabled={updatingAddress}
                />
                <div className="mt-2">
                  <Button
                    size="sm"
                    variant="success"
                    disabled={updatingAddress || !newAddress.trim()}
                    onClick={handleUpdateAddress}
                  >
                    {updatingAddress ? 'Đang lưu...' : 'Lưu'}
                  </Button>{' '}
                  <Button
                    size="sm"
                    variant="secondary"
                    onClick={() => {
                      setNewAddress(order.shipping_address);
                      setEditingAddress(false);
                    }}
                    disabled={updatingAddress}
                  >
                    Hủy
                  </Button>
                </div>
              </>
            ) : (
              <>
                {order.shipping_address}{' '}
                {order.status === 'pending' && (
                  <Button
                    size="sm"
                    variant="link"
                    onClick={() => setEditingAddress(true)}
                  >
                    [Sửa]
                  </Button>
                )}
              </>
            )}
          </div>

          <p><strong>Ngày đặt:</strong> {new Date(order.created_at).toLocaleDateString()}</p>

          <p>
            <strong>Trạng thái đơn hàng:</strong>{' '}
            <span className={`badge bg-${statusBadgeVariant[order.status] || 'secondary'}`}>
              {STATUS_LABELS[order.status] || 'Không rõ'}
            </span>
          </p>

          {order.status !== 'cancelled' && (
            <p>
              <strong>Thanh toán:</strong>{' '}
              <span className={`badge bg-${paymentStatusBadgeVariant[order.payment_status] || 'secondary'}`}>
                {PAYMENT_STATUS_LABELS[order.payment_status] || 'Không rõ'}
              </span>
            </p>
          )}

          <Link to="/orders">
            <Button variant="secondary">&larr; Quay lại danh sách đơn</Button>
          </Link>
        </Col>

        <Col md={6}>
          <Card>
            <Card.Header className="bg-primary text-white">Sản phẩm trong đơn</Card.Header>
            <Card.Body className="p-0">
              <Table bordered responsive className="mb-0 text-center align-middle">
                <thead className="table-light">
                  <tr>
                    <th>Ảnh</th>
                    <th>Sản phẩm</th>
                    <th>Phân loại</th>
                    <th>SL</th>
                    <th>Tạm tính</th>
                    {order.status === 'pending' && <th>Hành động</th>}
                  </tr>
                </thead>
                <tbody>
                  {order.items.map(item => (
                    <tr key={item.id}>
                      <td>
                        <img
                          src={item.product_variant.thumbnail}
                          alt={item.product_variant.product.name}
                          width={80}
                        />
                      </td>
                      <td>{item.product_variant?.product?.name}</td>
                      <td>{item.product_variant?.color?.name || '—'} / {item.product_variant?.size?.name || '—'}</td>
                      <td>{item.quantity}</td>
                      <td>{((item.sale_price || item.price) * item.quantity).toLocaleString()}₫</td>
                      {order.status === 'pending' && (
                        <td>
                          <Button
                            variant="danger"
                            size="sm"
                            onClick={() => setShowCancelConfirm(true)}
                          >
                            Hủy đơn
                          </Button>
                        </td>
                      )}
                    </tr>
                  ))}
                </tbody>
              </Table>
            </Card.Body>
            <Card.Footer className="text-end fw-bold">
              Tổng cộng: {Number(order.total).toLocaleString()}₫
            </Card.Footer>
          </Card>
        </Col>
      </Row>

      <Modal show={showCancelConfirm} onHide={() => setShowCancelConfirm(false)} centered>
        <Modal.Header closeButton>
          <Modal.Title>Xác nhận hủy đơn hàng</Modal.Title>
        </Modal.Header>
        <Modal.Body>
          Bạn có chắc chắn muốn hủy đơn hàng #{order.order_number || order.id} không?
        </Modal.Body>
        <Modal.Footer>
          <Button variant="secondary" onClick={() => setShowCancelConfirm(false)}>Đóng</Button>
          <Button variant="danger" onClick={handleCancelOrder}>Hủy đơn</Button>
        </Modal.Footer>
      </Modal>
    </Container>
  );
}
