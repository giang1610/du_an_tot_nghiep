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
    failed: 'Giao hàng thất bại',
    return_requested: 'Đã yêu cầu hoàn hàng',
    returned: 'Hoàn hàng',
    failed_1: 'Giao hàng thất bại lần 1',
    failed_2: 'Giao hàng thất bại lần 2',
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
    return_requested: 'warning',
    returned: 'secondary',
};

const PAYMENT_METHOD_LABELS = {
    cod: 'Thanh toán khi nhận hàng',
    momo: 'Ví Momo',
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

    // Modal HOÀN ĐƠN
    const [showReturnModal, setShowReturnModal] = useState(false);
    const [returnReason, setReturnReason] = useState('');

    // Modal XÁC NHẬN HỦY
    const [showCancelConfirm, setShowCancelConfirm] = useState(false);

    // Modal REVIEW
    const [showReviewModal, setShowReviewModal] = useState(false);
    const [reviewItem, setReviewItem] = useState(null);
    const [reviewContent, setReviewContent] = useState('');
    const [reviewRating, setReviewRating] = useState(5);
    const [reviewLoading, setReviewLoading] = useState(false);

    // Modal XÁC NHẬN ĐÃ NHẬN HÀNG
    const [showConfirmReceived, setShowConfirmReceived] = useState(false);
    const [confirmReceivedLoading, setConfirmReceivedLoading] = useState(false);

    // Realtime status
    useEffect(() => {
        const channel = listenToOrderStatusRealtime((orderIdFromSocket, newStatus) => {
            if (Number(orderIdFromSocket) === Number(id)) {
                setOrder(prev => {
                    if (!prev) return prev;
                    return { ...prev, status: newStatus };
                });
            }
        });

        return () => {
            channel.stopListening('.order.updated');
        };
    }, [id]);

    const fetchOrder = useCallback(async () => {
        if (!token) return setError('Bạn chưa đăng nhập');
        setLoading(true);
        try {
            const res = await axios.get(`${process.env.REACT_APP_API_URL}/orders/${id}`, {
                headers: { Authorization: `Bearer ${token}` }
            });
            setOrder(res.data.data);
            setNewAddress(res.data.data.shipping_address);
        } catch {
            setError('Không thể tải đơn hàng.');
        } finally {
            setLoading(false);
        }
    }, [id, token]);

    useEffect(() => {
        fetchOrder();
    }, [fetchOrder]);

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

    const handleConfirmReceived = async () => {
        setConfirmReceivedLoading(true);
        try {
            await axios.post(`${process.env.REACT_APP_API_URL}/orders/${id}/confirm-received`, {}, {
                headers: { Authorization: `Bearer ${token}` }
            });
            setShowConfirmReceived(false);
            fetchOrder();
            alert('Đã xác nhận đã nhận hàng thành công!');
        } catch {
            alert('Xác nhận nhận hàng thất bại!');
        } finally {
            setConfirmReceivedLoading(false);
        }
    };

    const handleRequestReturn = async () => {
        if (!returnReason.trim()) {
            alert('Vui lòng nhập lý do hoàn đơn!');
            return;
        }
        try {
            await axios.post(`${process.env.REACT_APP_API_URL}/orders/${id}/request-return`, {
                reason: returnReason
            }, {
                headers: { Authorization: `Bearer ${token}` }
            });
            alert('Đã gửi yêu cầu hoàn đơn!');
            setShowReturnModal(false);
            setReturnReason('');
            fetchOrder();
        } catch {
            alert('Yêu cầu hoàn đơn thất bại!');
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

    const handleShowReviewModal = (item) => {
        setReviewItem(item);
        setReviewContent('');
        setReviewRating(5);
        setShowReviewModal(true);
    };

    const handleSubmitReview = async () => {
        if (!reviewItem) return;
        setReviewLoading(true);
        try {
            await axios.post(`${process.env.REACT_APP_API_URL}/reviews`, {
                order_id: order.id,
                product_id: reviewItem.product_variant.product_id,
                product_variant_id: reviewItem.product_variant_id,
                rating: reviewRating,
                content: reviewContent
            }, {
                headers: { Authorization: `Bearer ${token}` }
            });
            alert('Đánh giá thành công!');
            setShowReviewModal(false);
            fetchOrder();
        } catch {
            alert('Gửi đánh giá thất bại.');
        } finally {
            setReviewLoading(false);
        }
    };

    if (loading) return <Spinner />;
    if (error) return <Alert variant="danger">{error}</Alert>;
    if (!order) return <Alert variant="danger">Không tìm thấy đơn hàng.</Alert>;

    // Các trạng thái chỉ cho xem, không cho thao tác gì nữa
    const isReadonlyStatus = ['shipped', 'delivered', 'return_requested', 'returned', 'completed', 'cancelled', 'failed'].includes(order.status);

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
                    <p><strong>Name:</strong> {order.user?.name || 'Không rõ'}</p>
                    <p><strong>Email:</strong> {order.customer_email}</p>
                    <p><strong>SĐT:</strong> {order.customer_phone}</p>
                    <div>
                        <strong>Địa chỉ:</strong>{' '}
                        {editingAddress ? (
                            <>
                                <Form.Control size="sm" value={newAddress} onChange={(e) => setNewAddress(e.target.value)} disabled={updatingAddress} />
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

                    {order.return_reason && (
                        <p className="mt-3">
                            <strong>Lý do hoàn đơn:</strong><br />
                            <span className="border rounded d-block p-2 bg-light">{order.return_reason}</span>
                        </p>
                    )}
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
                                <th>Đánh giá</th>
                            </tr>
                        </thead>
                        <tbody>
                            {order.items.map((item) => {
                                const reviews = item.reviews || [];
                                const count = reviews.length;
                                const deliveredAt = new Date(order.delivered_at);
                                const now = new Date();
                                const diffDays = Math.floor((now - deliveredAt) / (1000 * 60 * 60 * 24));
                                let canReview = false;
                                if (count === 0) canReview = true;
                                else if (count === 1 && diffDays >= 7) canReview = true;

                                return (
                                    <tr key={item.id}>
                                        <td><img src={item.product_variant?.img} alt="" width={60} /></td>
                                        <td>{item.product_variant?.product?.name}</td>
                                        <td>{item.product_variant?.color?.name} / {item.product_variant?.size?.name}</td>
                                        <td>{item.quantity}</td>
                                        <td>{item.price}₫</td>
                                        <td>{(item.price * item.quantity).toLocaleString()}₫</td>
                                        <td>
                                            <div>
                                                {reviews.map(r => (
                                                    <div key={r.id} className="border rounded mb-1 p-1">
                                                        {'★'.repeat(r.rating)} - {r.content}
                                                    </div>
                                                ))}
                                                {count >= 2 && <span className="text-muted">Đã đánh giá đủ</span>}
                                                {canReview && count < 2 && order.status === 'delivered' && (
                                                    <Button size="sm" variant="outline-primary" onClick={() => handleShowReviewModal(item)}>
                                                        Đánh giá
                                                    </Button>
                                                )}
                                                {!canReview && count < 2 && (
                                                    <span className="text-muted">Chờ đủ 7 ngày</span>
                                                )}
                                            </div>
                                        </td>
                                    </tr>
                                );
                            })}
                        </tbody>
                    </Table>
                </Card.Body>
            </Card>

            <Card>
                <Card.Body className="d-flex justify-content-between align-items-center">
                    <div>
                        {/* Chỉ hiển thị các nút thao tác khi trạng thái cho phép */}
                        {order.status === 'shipped' && (
                            <Button variant="success" size="sm" onClick={() => setShowConfirmReceived(true)}>
                                Xác nhận đã nhận hàng
                            </Button>
                        )}

                        {order.status === 'delivered' && (
                            <Button variant="warning" size="sm" onClick={() => setShowReturnModal(true)} className="me-2">
                                Yêu cầu hoàn đơn
                            </Button>
                        )}

                        {order.status === 'pending' && (
                            <Button variant="danger" size="sm" onClick={() => setShowCancelConfirm(true)}>
                                Hủy đơn
                            </Button>
                        )}
                        <p className="mt-3 mb-0">
                            <strong>Thanh toán:</strong>{' '}
                            <Badge bg={paymentStatusBadgeVariant[order.payment_status] || 'secondary'}>
                                {PAYMENT_STATUS_LABELS[order.payment_status] || 'Không rõ'}
                            </Badge>
                        </p>
                    </div>
                    <Link to="/orders">
                        <Button variant="secondary" size="sm">← Trở lại</Button>
                    </Link>
                </Card.Body>
            </Card>

            {/* Modal HOÀN ĐƠN */}
            <Modal show={showReturnModal} onHide={() => setShowReturnModal(false)} centered>
                <Modal.Header closeButton>
                    <Modal.Title>Yêu cầu hoàn đơn</Modal.Title>
                </Modal.Header>
                <Modal.Body>
                    <Form.Group>
                        <Form.Label>Lý do hoàn đơn</Form.Label>
                        <Form.Control
                            as="textarea"
                            rows={4}
                            value={returnReason}
                            onChange={(e) => setReturnReason(e.target.value)}
                            placeholder="Nhập lý do chi tiết..."
                        />
                    </Form.Group>
                </Modal.Body>
                <Modal.Footer>
                    <Button variant="secondary" onClick={() => setShowReturnModal(false)}>Hủy</Button>
                    <Button variant="primary" onClick={handleRequestReturn}>Gửi yêu cầu</Button>
                </Modal.Footer>
            </Modal>

            {/* Modal XÁC NHẬN HỦY */}
            <Modal show={showCancelConfirm} onHide={() => setShowCancelConfirm(false)} centered>
                <Modal.Header closeButton>
                    <Modal.Title>Xác nhận hủy đơn</Modal.Title>
                </Modal.Header>
                <Modal.Body>Bạn có chắc chắn muốn hủy đơn này?</Modal.Body>
                <Modal.Footer>
                    <Button variant="secondary" onClick={() => setShowCancelConfirm(false)}>Đóng</Button>
                    <Button variant="danger" onClick={handleCancelOrder}>Xác nhận hủy</Button>
                </Modal.Footer>
            </Modal>

            {/* Modal REVIEW */}
            <Modal show={showReviewModal} onHide={() => setShowReviewModal(false)} centered>
                <Modal.Header closeButton>
                    <Modal.Title>Đánh giá sản phẩm</Modal.Title>
                </Modal.Header>
                <Modal.Body>
                    <Form.Group>
                        <Form.Label>Đánh giá sao</Form.Label>
                        <Form.Control type="number" min={1} max={5} value={reviewRating} onChange={e => setReviewRating(Number(e.target.value))} />
                    </Form.Group>
                    <Form.Group className="mt-2">
                        <Form.Label>Nội dung</Form.Label>
                        <Form.Control as="textarea" rows={3} value={reviewContent} onChange={e => setReviewContent(e.target.value)} />
                    </Form.Group>
                </Modal.Body>
                <Modal.Footer>
                    <Button variant="secondary" onClick={() => setShowReviewModal(false)}>Đóng</Button>
<Button variant="primary" onClick={handleSubmitReview} disabled={reviewLoading}>
                        {reviewLoading ? 'Đang gửi...' : 'Gửi đánh giá'}
                    </Button>
                </Modal.Footer>
            </Modal>

            {/* Modal xác nhận nhận hàng */}
            <Modal show={showConfirmReceived} onHide={() => setShowConfirmReceived(false)} centered>
                <Modal.Header closeButton>
                    <Modal.Title>Xác nhận đã nhận hàng</Modal.Title>
                </Modal.Header>
                <Modal.Body>
                    Bạn có chắc chắn muốn xác nhận đã nhận được hàng?
                </Modal.Body>
                <Modal.Footer>
                    <Button variant="secondary" onClick={() => setShowConfirmReceived(false)}>Hủy</Button>
                    <Button variant="success" onClick={handleConfirmReceived} disabled={confirmReceivedLoading}>
                        {confirmReceivedLoading ? 'Đang xác nhận...' : 'Xác nhận'}
                    </Button>
                </Modal.Footer>
            </Modal>
        </Container>
    );
}
