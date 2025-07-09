import { useEffect, useState, useCallback } from 'react';
import { useParams, Link } from 'react-router-dom';
import {
    Container, Card, Table, Spinner, Alert,
    Button, Form, Badge, Modal
} from 'react-bootstrap';
import axios from 'axios';
import '../css/OrderDetail.css';
import { listenToOrderStatusRealtime } from '../realtime/orderStatusRealtime';


const STATUS_LABELS = {
  pending: 'Chờ xử lý',
  processing: 'Đang xử lý',
  picking: 'Đang lấy hàng',
  shipping: 'Đang giao hàng',
  shipped: 'Đã giao hàng',
  delivered: 'Đã nhận hàng',
  completed: 'Hoàn thành',
  cancelled: 'Đã hủy',
  failed: 'Thất bại',
  return_requested: 'Đã yêu cầu hoàn đơn',
  returning: 'Đang hoàn đơn',
  returned: 'Đã hoàn đơn',
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
    delivered: 'primary',
    shipping: 'info',
    shipped: 'success',
    returning: 'warning',
};
const PAYMENT_METHOD_LABELS = {
    cod: 'Thanh toán khi nhận hàng',
    momo: 'Ví Momo',
};
const PAYMENT_METHOD_LABELS = {
  cod: 'Thanh toán khi nhận hàng',
  momo: 'Ví Momo',
  // vnpay: 'VNPay',
  // zalopay: 'ZaloPay',
  // bank: 'Chuyển khoản ngân hàng',
  // other: 'Khác'
};

const paymentStatusBadgeVariant = {
    paid: 'success',
    pending: 'warning',
    unpaid: 'danger',
    failed: 'danger',
};

export default function OrderDetailPage() {
    const { id } = useParams();
    const token = localStorage.getItem('token');
    const [order, setOrder] = useState(null);
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState('');
    const [newAddress, setNewAddress] = useState('');
    const [editingAddress, setEditingAddress] = useState(false);
    const [updatingAddress, setUpdatingAddress] = useState(false);
    const [showCancelConfirm, setShowCancelConfirm] = useState(false);
    const [showReviewModal, setShowReviewModal] = useState(false);
    const [reviewItem, setReviewItem] = useState(null);
    const [reviewContent, setReviewContent] = useState('');
    const [reviewRating, setReviewRating] = useState(5);
    const [reviewLoading, setReviewLoading] = useState(false);
    const [productReviews, setProductReviews] = useState({});
    const [showReturnModal, setShowReturnModal] = useState(false);
    const [returnReason, setReturnReason] = useState('');
    const [requestingReturn, setRequestingReturn] = useState(false);

    const fetchOrder = useCallback(async () => {
        if (!token) return setError('Bạn chưa đăng nhập');
        setLoading(true);
        try {
            const res = await axios.get(`${process.env.REACT_APP_API_URL}/orders/${id}`, {
                headers: { Authorization: `Bearer ${token}` }
            });
            setOrder(res.data.data); // Đảm bảo dữ liệu chứa status
        } catch (err) {
            setError('Không thể tải đơn hàng.');
        } finally {
            setLoading(false);
        }
    }, [id, token]);

    useEffect(() => {
        fetchOrder();
    }, [fetchOrder]);

  //realTime Status
  useEffect(() => {
    const channel = listenToOrderStatusRealtime((orderIdFromSocket, newStatus) => {
      if (Number(orderIdFromSocket) === Number(id)) {
        console.log('[Realtime] Cập nhật trạng thái mới:', newStatus);
        setOrder(prev => {
          if (!prev) return prev;
          return {
            ...prev,
            status: newStatus
          };
        });
      }
    });

    return () => {
      console.log('[Realtime] Hủy lắng nghe kênh order-status');
      channel.stopListening('.order.updated');
    };
  }, [id]);




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
    } catch {
      alert('Cập nhật địa chỉ thất bại!');
    } finally {
      setUpdatingAddress(false);
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
        } catch {
            alert('Cập nhật địa chỉ thất bại!');
        } finally {
            setUpdatingAddress(false);
        }
    };

    const handleCancelOrder = async () => {
        try {
            await axios.put(`${process.env.REACT_APP_API_URL}/orders/${id}/cancel`, {}, {
                headers: { Authorization: `Bearer ${token}` }
            });
            setShowCancelConfirm(false);
            fetchOrder();
        } catch {
            alert('Hủy đơn thất bại.');
        }
    };

    // Hiện modal đánh giá
    const handleShowReviewModal = (item) => {
        setReviewItem(item);
        setReviewContent('');
        setReviewRating(5);
        setShowReviewModal(true);
    };

    // Gửi đánh giá
    const handleSubmitReview = async () => {
        if (!reviewItem) return;
        setReviewLoading(true);
        try {
            await axios.post(
                `${process.env.REACT_APP_API_URL}/reviews`,
                {
                    order_id: order.id,
                    product_variant_id: reviewItem.product_variant_id,
                    rating: reviewRating,
                    content: reviewContent
                },
                { headers: { Authorization: `Bearer ${token}` } }
            );
            setShowReviewModal(false);
            alert('Đánh giá thành công!');
            fetchProductReviews(order.items, order.id);
        } catch (err) {
            if (err.response && err.response.data && err.response.data.error) {
                alert(err.response.data.error);
            } else {
                alert('Gửi đánh giá thất bại!');
            }
        } finally {
            setReviewLoading(false);
        }
    };

    // Lấy đánh giá cho từng sản phẩm trong đơn hàng
    const fetchProductReviews = useCallback(async (items, orderId) => {
        if (!items || items.length === 0) return;
        const reviewsObj = {};
        await Promise.all(items.map(async (item) => {
            try {
                const res = await axios.get(
                    `${process.env.REACT_APP_API_URL}/products/${item.product_variant.product_id}/reviews`
                );
                reviewsObj[item.product_variant_id] = res.data.data.filter(
                    r => r.product_variant_id === item.product_variant_id && r.order_id === orderId
                );
            } catch (err) {
                reviewsObj[item.product_variant_id] = [];
            }
        }));
        setProductReviews(reviewsObj);
    }, []);

      <Card className="mb-3">
        <Card.Header className="fw-bold">Thông tin giao hàng</Card.Header>
        <Card.Body>
          <p><strong>Name:</strong> {order.user?.name || 'Không rõ'}</p>
          <p><strong>Email:</strong> {order.customer_email}</p>
          <p><strong>SĐT:</strong> {order.customer_phone}</p>
          <div>
            <strong>Địa chỉ:</strong>{' '}
            {editingAddress ? (
              <>
                <Form.Control
                  size="sm"
                  value={newAddress}
                  onChange={(e) => setNewAddress(e.target.value)}
                  disabled={updatingAddress}
                />
                <div className="mt-2">
                  <Button size="sm" variant="success" onClick={handleUpdateAddress} disabled={updatingAddress}>Lưu</Button>{' '}
                  <Button size="sm" variant="secondary" onClick={() => {
                    setNewAddress(order.shipping_address);
                    setEditingAddress(false);
                  }}>Hủy</Button>
                </div>
              </>
            ) : (
              <>
                {order.shipping_address}{' '}
                {order.status === 'pending' && (
                  <Button size="sm" variant="link" onClick={() => setEditingAddress(true)}>[Sửa]</Button>
                )}
              </>
            )}
          </div>
        </Card.Body>
      </Card>

    const handleRequestReturn = async () => {
        setRequestingReturn(true);
        try {
            await axios.post(`${process.env.REACT_APP_API_URL}/orders/${id}/request-return`, {
                reason: returnReason
            }, {
                headers: { Authorization: `Bearer ${token}` }
            });
            setShowReturnModal(false);
            setReturnReason('');
            alert('Đã gửi yêu cầu hoàn hàng!');
            fetchOrder();
        } catch {
            alert('Gửi yêu cầu thất bại!');
        } finally {
            setRequestingReturn(false);
        }
    };

      <Card>
        <Card.Body className="d-flex justify-content-between align-items-center">
          <div>
            {order.status !== 'cancelled' && (
              <p>
                <strong>Thanh toán:</strong>{' '}
                <Badge bg={paymentStatusBadgeVariant[order.payment_status] || 'secondary'}>
                  {PAYMENT_STATUS_LABELS[order.payment_status] || 'Không rõ'}
                </Badge>{' '}
                {order.payment_method && (
                  <span className="ms-2">
                    ({PAYMENT_METHOD_LABELS[order.payment_method] || order.payment_method})
                  </span>
                )}
              </p>
            )}



    if (loading) return <div className="text-center py-5"><Spinner animation="border" /></div>;
    if (error) return <Alert variant="danger" className="py-5 text-center">{error}</Alert>;
    if (!order) return <Alert variant="danger">Không tìm thấy đơn hàng.</Alert>;

    return (
        <Container className="py-4">
            <Card className="mb-3">
                <Card.Header className="bg-white d-flex justify-content-between align-items-center">
                    <div>
                        <strong>Đơn hàng:</strong> #{order.order_number || order.id} <br />
                        <small className="text-muted">Ngày đặt: {new Date(order.created_at).toLocaleDateString()}</small>
                    </div>
                    <Badge bg={statusBadgeVariant[order.status] || 'secondary'}>
                        {STATUS_LABELS[order.status] || 'Không rõ'}
                    </Badge>
                </Card.Header>
            </Card>

            <Card className="mb-3">
                <Card.Header className="fw-bold">Thông tin giao hàng</Card.Header>
                <Card.Body>
                    <p><strong>Email:</strong> {order.customer_email}</p>
                    <p><strong>SĐT:</strong> {order.customer_phone}</p>
                    <div>
                        <strong>Địa chỉ:</strong>{' '}
                        {editingAddress ? (
                            <>
                                <Form.Control
                                    size="sm"
                                    value={newAddress}
                                    onChange={(e) => setNewAddress(e.target.value)}
                                    disabled={updatingAddress}
                                />
                                <div className="mt-2">
                                    <Button size="sm" variant="success" onClick={handleUpdateAddress} disabled={updatingAddress}>Lưu</Button>{' '}
                                    <Button size="sm" variant="secondary" onClick={() => {
                                        setNewAddress(order.shipping_address);
                                        setEditingAddress(false);
                                    }}>Hủy</Button>
                                </div>
                            </>
                        ) : (
                            <>
                                {order.shipping_address}{' '}
                                {order.status === 'pending' && (
                                    <Button size="sm" variant="link" onClick={() => setEditingAddress(true)}>[Sửa]</Button>
                                )}
                            </>
                        )}
                    </div>
                </Card.Body>
            </Card>

            <Card className="mb-3">
                <Card.Header className="fw-bold">Sản phẩm</Card.Header>
                <Card.Body className="p-0">
                    <Table responsive borderless hover className="mb-0 text-center align-middle">
                        <thead className="table-light">
                            <tr>
                                <th>Ảnh</th>
                                <th>Sản phẩm</th>
                                <th>Phân loại</th>
                                <th>SL</th>
                                <th>Giá</th>
                                <th>Tạm tính</th>
                            </tr>
                        </thead>
                        <tbody>
                            {order.items.map((item) => (
                                <tr key={item.id}>
                                    <td>
                                        <img
                                            src={item.product_variant?.img || item.product_variant?.product?.img || 'https://via.placeholder.com/60'}
                                            alt="Ảnh"
                                            style={{ width: 60, height: 60, objectFit: 'cover' }}
                                            className="rounded"
                                        />
                                    </td>
                                    <td>{item.product_variant?.product?.name}</td>
                                    <td>{item.product_variant?.color?.name || '—'} / {item.product_variant?.size?.name || '—'}</td>
                                    <td>{item.quantity}</td>
                                    <td>{Number(item.sale_price || item.price).toLocaleString()}₫</td>
                                    <td>{(item.quantity * (item.sale_price || item.price)).toLocaleString()}₫</td>
                                    {order.status === 'delivered' && (
                                        <td>
                                            {productReviews[item.product_variant_id]?.length > 0 ? (
                                                <div className="review-box p-2 rounded bg-light border mb-2">
                                                    {productReviews[item.product_variant_id].map((review, idx) => (
                                                        <div key={review.id || idx}>
                                                            <span className="text-warning fw-bold">
                                                                {'★'.repeat(review.rating)}
                                                                {'☆'.repeat(5 - review.rating)}
                                                            </span>
                                                            <span className="ms-2">{review.content}</span>
                                                            <div className="small text-muted">
                                                                {review.user?.name || 'Khách'} - {new Date(review.created_at).toLocaleDateString()}
                                                            </div>
                                                        </div>
                                                    ))}
                                                </div>
                                            ) : (
                                                <Button
                                                    variant="outline-primary"
                                                    size="sm"
                                                    className="rounded-pill px-3"
                                                    onClick={() => handleShowReviewModal(item)}
                                                >
                                                    Đánh giá
                                                </Button>
                                            )}
                                        </td>
                                    )}
                                </tr>
                            ))}
                        </tbody>
                    </Table>
                </Card.Body>
                <Card.Footer className="text-end fw-bold">
                    Tổng cộng: <span className="text-danger">{Number(order.total).toLocaleString()}₫</span>
                </Card.Footer>
            </Card>

            <Card>
                <Card.Body className="d-flex justify-content-between align-items-center">
                    <div>
                        {order.status !== 'cancelled' && (
                            <p>
                                <strong>Thanh toán:</strong>{' '}
                                <Badge bg={paymentStatusBadgeVariant[order.payment_status] || 'secondary'}>
                                    {PAYMENT_STATUS_LABELS[order.payment_status] || 'Không rõ'}
                                </Badge>{' '}
                                {order.payment_method && (
                                    <span className="ms-2">
                                        ({PAYMENT_METHOD_LABELS[order.payment_method] || order.payment_method})
                                    </span>
                                )}
                            </p>
                        )}

                        {order.status === 'shipped' && (
                            <Button variant="success" size="sm" onClick={handleConfirmReceived}>Xác nhận đã nhận hàng</Button>
                        )}

                        {order.status === 'delivered' && (
                            <>
                                <Button
                                    variant="warning"
                                    size="sm"
                                    className="ms-2"
                                    onClick={() => setShowReturnModal(true)}
                                >
                                    Hoàn hàng
                                </Button>
                                <Modal show={showReturnModal} onHide={() => setShowReturnModal(false)} centered>
                                    <Modal.Header closeButton>
                                        <Modal.Title>Yêu cầu hoàn hàng</Modal.Title>
                                    </Modal.Header>
                                    <Modal.Body>
                                        <Form.Group>
                                            <Form.Label>Lý do hoàn hàng</Form.Label>
                                            <Form.Control
                                                as="textarea"
                                                rows={3}
                                                value={returnReason}
                                                onChange={e => setReturnReason(e.target.value)}
                                                placeholder="Nhập lý do hoàn hàng"
                                            />
                                        </Form.Group>
                                    </Modal.Body>
                                    <Modal.Footer>
                                        <Button variant="secondary" onClick={() => setShowReturnModal(false)}>Đóng</Button>
                                        <Button
                                            variant="warning"
                                            onClick={handleRequestReturn}
                                            disabled={!returnReason.trim() || requestingReturn}
                                        >
                                            {requestingReturn ? 'Đang gửi...' : 'Gửi yêu cầu'}
                                        </Button>
                                    </Modal.Footer>
                                </Modal>
                            </>
                        )}

                        {order.status === 'pending' && (
                            <Button variant="danger" size="sm" onClick={() => setShowCancelConfirm(true)}>
                                Hủy đơn
                            </Button>
                        )}
                    </div>
                    <Link to="/orders">
                        <Button variant="secondary" size="sm">← Trở lại</Button>
                    </Link>
                </Card.Body>
            </Card>

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

            {/* Modal đánh giá */}
            <Modal show={showReviewModal} onHide={() => setShowReviewModal(false)} centered>
                <Modal.Header closeButton>
                    <Modal.Title>Đánh giá sản phẩm</Modal.Title>
                </Modal.Header>
                <Modal.Body>
                    <div>
                        <strong>{reviewItem?.product_variant?.product?.name}</strong>
                    </div>
                    <Form.Group className="mt-3">
                        <Form.Label>Số sao</Form.Label>
                        <Form.Select
                            value={reviewRating}
                            onChange={e => setReviewRating(Number(e.target.value))}
                        >
                            {[5, 4, 3, 2, 1].map(star => (
                                <option key={star} value={star}>{star} sao</option>
                            ))}
                        </Form.Select>
                    </Form.Group>
                    <Form.Group className="mt-3">
                        <Form.Label>Nội dung đánh giá</Form.Label>
                        <Form.Control
                            as="textarea"
                            rows={3}
                            value={reviewContent}
                            onChange={e => setReviewContent(e.target.value)}
                        />
                    </Form.Group>
                </Modal.Body>
                <Modal.Footer>
                    <Button variant="secondary" onClick={() => setShowReviewModal(false)}>
                        Đóng
                    </Button>
                    <Button
                        variant="primary"
                        onClick={handleSubmitReview}
                        disabled={reviewLoading}
                    >
                        {reviewLoading ? 'Đang gửi...' : 'Gửi đánh giá'}
                    </Button>
                </Modal.Footer>
            </Modal>
        </Container>
    );
}
