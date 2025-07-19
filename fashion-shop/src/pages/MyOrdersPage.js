import { useEffect, useState } from 'react';
import {
    Container, Card, Row, Col, Button, Badge, Spinner, Alert, Image, Modal, Form
} from 'react-bootstrap';
import { Link, useNavigate } from 'react-router-dom';
import { listenToOrderStatusRealtime } from '../realtime/orderStatusRealtime';
import axios from 'axios';

const formatDate = (iso) => {
    const d = new Date(iso);
    return `${d.getDate().toString().padStart(2, '0')}/${(d.getMonth() + 1).toString().padStart(2, '0')}/${d.getFullYear()}`;
};

const formatCurrency = (amount) => Number(amount).toLocaleString('vi-VN') + '₫';

const STATUS_LABELS = {
    pending: 'Chờ xử lý',
    processing: 'Đang xử lý',
    picking: 'Đang lấy hàng',
    shipping: 'Đang giao hàng',
    shipped: 'Đã giao hàng',
    delivered: 'Hoàn thành', // coi là hoàn thành
    return_requested: 'Đã yêu cầu hoàn hàng',
    returned: 'Hoàn hàng',
    cancelled: 'Đã hủy',
    failed: 'Giao hàng thất bại',
    failed_1: 'Giao hàng thất bại lần 1',
    failed_2: 'Giao hàng thất bại lần 2',
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
    returned: 'success',
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
};

export default function MyOrdersPage() {
    const [orders, setOrders] = useState([]);
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState('');
    const navigate = useNavigate();

    const [showReturnModal, setShowReturnModal] = useState(false);
    const [returnReason, setReturnReason] = useState('');
    const [returnMedia, setReturnMedia] = useState(null);
    const [returnOrderId, setReturnOrderId] = useState(null);
    const [returnLoading, setReturnLoading] = useState(false);


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

    // ✅ Tự động xác nhận đơn hàng sau 3 ngày trạng thái là "shipped"
    useEffect(() => {
        const token = localStorage.getItem('token');
        if (!token || orders.length === 0) return;

        const autoConfirmReceived = async () => {
            const now = new Date();

            for (const order of orders) {
                if (order.status === 'shipped') {
                    const shippedAt = new Date(order.shipped_at || order.updated_at || order.created_at);
                    const diffDays = Math.floor((now - shippedAt) / (1000 * 60 * 60 * 24));

                    if (diffDays >= 3) {
                        try {
                            await axios.post(
                                `${process.env.REACT_APP_API_URL}/orders/${order.id}/confirm-received`,
                                {},
                                {
                                    headers: { Authorization: `Bearer ${token}` }
                                }
                            );
                            setOrders(prev =>
                                prev.map(o =>
                                    o.id === order.id ? { ...o, status: 'delivered' } : o
                                )
                            );
                            console.log(`✅ Đã tự động xác nhận đơn hàng #${order.id} sau ${diffDays} ngày.`);
                        } catch (err) {
                            console.warn(`❌ Không thể xác nhận đơn hàng #${order.id}`, err);
                        }
                    }
                }
            }
        };

        autoConfirmReceived();
    }, [orders]);

    useEffect(() => {
        const channel = listenToOrderStatusRealtime((orderId, newStatus, paymentStatus) => {
            setOrders(prev =>
                prev.map(order =>
                    order.id === Number(orderId)
                        ? {
                            ...order,
                            status: newStatus ?? order.status,
                            payment_status: paymentStatus ?? order.payment_status,
                        }
                        : order
                )
            );
        });

        return () => {
            channel.stopListening('.order.updated');
        };
    }, []);

    const handleShowReturnModal = (orderId) => {
        setReturnOrderId(orderId);
        setReturnReason('');
        setReturnMedia(null);
        setShowReturnModal(true);
    };

    const handleRequestReturn = async () => {
        if (!returnReason.trim()) {
            alert('Vui lòng nhập lý do hoàn đơn!');
            return;
        }
        const token = localStorage.getItem('token');
        const formData = new FormData();
        formData.append('reason', returnReason);
        if (returnMedia) formData.append('media', returnMedia);

        setReturnLoading(true);
        try {
            await axios.post(`${process.env.REACT_APP_API_URL}/orders/${returnOrderId}/request-return`, formData, {
                headers: {
                    Authorization: `Bearer ${token}`,
                    'Content-Type': 'multipart/form-data'
                }
            });
            alert('Đã gửi yêu cầu hoàn đơn!');
            setShowReturnModal(false);
            setReturnReason('');
            setReturnMedia(null);
            setReturnOrderId(null);
            // Reload orders
            setLoading(true);
            const res = await axios.get(`${process.env.REACT_APP_API_URL}/orders`, {
                headers: { Authorization: `Bearer ${token}` }
            });
            setOrders(res.data.data?.data || []);
        } catch {
            alert('Yêu cầu hoàn đơn thất bại!');
        } finally {
            setReturnLoading(false);
        }
    };

    const handleConfirmReceived = async (orderId) => {
        if (!window.confirm('Bạn xác nhận đã nhận hàng?')) return;
        const token = localStorage.getItem('token');

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

    const handleCancelOrder = async (orderId) => {
        if (!window.confirm('Bạn có chắc chắn muốn hủy đơn hàng này?')) return;
        const token = localStorage.getItem('token');

        try {
            await axios.post(`${process.env.REACT_APP_API_URL}/orders/${orderId}/cancel`, {}, {
                headers: { Authorization: `Bearer ${token}` }
            });
            setOrders(prev =>
                prev.map(order =>
                    order.id === orderId ? { ...order, status: 'cancelled' } : order
                )
            );
            alert('Hủy đơn hàng thành công!');
        } catch {
            alert('Không thể hủy đơn hàng. Vui lòng thử lại.');
        }
    };

    const handleReturnOrder = async (orderId) => {
        if (!window.confirm('Bạn xác nhận muốn hoàn hàng đơn này?')) return;
        const token = localStorage.getItem('token');

        try {
            await axios.post(`${process.env.REACT_APP_API_URL}/orders/${orderId}/request-return`, {}, {
                headers: { Authorization: `Bearer ${token}` }
            });
            setOrders(prev =>
                prev.map(order =>
                    order.id === orderId ? { ...order, status: 'return_requested' } : order
                )
            );
            alert('Yêu cầu hoàn hàng đã được gửi!');
        } catch {
            alert('Không thể yêu cầu hoàn hàng. Vui lòng thử lại.');
        }
    };

    return (
        <>
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
                                <strong>Mã đơn:</strong> #{order.order_number || order.id}
                                <div><strong>Ngày đặt:</strong> {formatDate(order.created_at)}</div>
                            </div>
                            <Badge bg={STATUS_VARIANTS[order.status] || 'secondary'}>
                                {STATUS_LABELS[order.status] || 'Không rõ'}
                            </Badge>
                        </Card.Header>

                        <Card.Body>
                            <Row>
                                <Col md={8}>
                                    {order.items.map(item => {
                                        const reviews = item.reviews || [];
                                        const count = reviews.length;
                                        const deliveredAt = new Date(order.delivered_at);
                                        const now = new Date();
                                        const diffDays = Math.floor((now - deliveredAt) / (1000 * 60 * 60 * 24));
                                        let canReview = false;
                                        if (count === 0) canReview = true;
                                        else if (count === 1 && diffDays >= 7) canReview = true;

                                        return (
                                            <Row key={item.id} className="align-items-center mb-3">
                                                <Col xs={3}>
                                                    <Image
                                                        src={item.product_variant?.img || item.product_variant?.product?.img || 'https://via.placeholder.com/60'}
                                                        rounded
                                                        style={{ width: '100%', height: 80, objectFit: 'contain', backgroundColor: '#f8f9fa' }}
                                                    />
                                                </Col>
                                                <Col xs={9}>
                                                    <div className="fw-semibold">{item.product_variant?.product?.name}</div>
                                                    <small className="text-muted">
                                                        Phân loại: {item.product_variant?.color?.name || '—'} / {item.product_variant?.size?.name || '—'}
                                                    </small>
                                                    <div>
                                                        {reviews.map(r => (
                                                            <div key={r.id} className="border p-1 my-1 rounded">
                                                                {'★'.repeat(r.rating)} - {r.content}
                                                                {r.media && (
                                                                <div className="mt-2">
                                                                    {/\.(jpg|jpeg|png)$/i.test(r.media)
                                                                        ? <img src={`${process.env.REACT_APP_API_URL}/storage/${r.media}`} alt="Ảnh đánh giá" width={120} />
                                                                        : <video src={`${process.env.REACT_APP_API_URL}/storage/${r.media}`} controls width={180}></video>
                                                                    }
                                                                </div>
                                                            )}
                                                            </div>
                                                        ))}
                                                        {count >= 2 && <span className="text-muted">Đã đánh giá đủ</span>}
                                                        {!canReview && count === 1 && (
                                                            <span className="text-muted">Chờ đủ 7 ngày để đánh giá tiếp</span>
                                                        )}
                                                    </div>
                                                    <div>{formatCurrency(item.sale_price || item.price)} | SL: {item.quantity}</div>
                                                </Col>
                                            </Row>
                                        );
                                    })}
                                </Col>

                                <Col md={4}>
                                    <div><strong>Khách hàng:</strong> {order.user?.name}</div>
                                    <div><strong>Địa chỉ:</strong> {order.address || order.shipping_address || order.user?.address || 'Chưa có'}</div>
                                    <div><strong>Trạng thái:</strong>{' '}
                                        <Badge bg={STATUS_VARIANTS[order.status] || 'secondary'}>
                                            {STATUS_LABELS[order.status] || 'Không rõ'}
                                        </Badge>
                                    </div>
                                    <div className="mt-2"><strong>Thanh toán:</strong>{' '}
                                        <Badge bg={PAYMENT_STATUS_VARIANTS[order.payment_status] || 'secondary'}>
                                            {PAYMENT_STATUS_LABELS[order.payment_status] || 'Không rõ'}
                                        </Badge>
                                    </div>
                                    {order.payment_method && (
                                        <div>Hình thức: {PAYMENT_METHOD_LABELS[order.payment_method]}</div>
                                    )}
                                    <div className="fw-bold mt-2 text-danger">
                                        Tổng: {formatCurrency(order.total ?? order.total_amount ?? 0)}
                                    </div>
                                </Col>
                            </Row>
                        </Card.Body>

                        <Card.Footer className="d-flex justify-content-between align-items-center">
                            <Link to={`/orders/${order.id}`}>
                                <Button variant="outline-primary" size="sm">Chi tiết</Button>
                            </Link>

                            <div className="d-flex flex-wrap gap-2">
                                {order.items.some(item => {
                                    const reviews = item.reviews || [];
                                    const count = reviews.length;
                                    const deliveredAt = new Date(order.delivered_at);
                                    const now = new Date();
                                    const diffDays = Math.floor((now - deliveredAt) / (1000 * 60 * 60 * 24));
                                    return (
                                        (count === 0 || (count === 1 && diffDays >= 7)) &&
                                        order.status === 'delivered'
                                    );
                                }) && (
                                        <Link to={`/orders/${order.id}`}>
                                            <Button variant="primary" size="sm">Đánh giá</Button>
                                        </Link>
                                    )}

                                {(order.status === 'delivered' || order.status === 'shipped') && (() => {
                                    const deliveredAt = new Date(order.delivered_at || order.updated_at);
                                    const now = new Date();
                                    const diffDays = Math.floor((now - deliveredAt) / (1000 * 60 * 60 * 24));
                                    if (diffDays <= 7) {
                                        return (
                                            <Button variant="warning" size="sm" onClick={() => handleReturnOrder(order.id)}>
                                                Hoàn hàng
                                            </Button>
                                        );
                                    }
                                    return null;
                                })()}

                                {order.status === 'shipped' && (
                                    <Button variant="success" size="sm" onClick={() => handleConfirmReceived(order.id)}>
                                        Đã nhận hàng
                                    </Button>
                                )}
                                {(order.status === 'pending' || order.status === 'processing') && (
                                    <Button variant="danger" size="sm" onClick={() => handleCancelOrder(order.id)}>
                                        Hủy đơn
                                    </Button>

                                )}

                                {/* Nút hoàn đơn */}
                                {order.status === 'shipped' && (
                                    <Button variant="warning" size="sm" className="ms-2" onClick={() => handleShowReturnModal(order.id)}>
                                        Yêu cầu hoàn đơn
                                    </Button>
                                )}
                            </div>
                        </Card.Footer>
                    </Card>
                ))
            )}
        </Container>
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
                <Form.Group className="mt-2">
                    <Form.Label>Ảnh/Video sản phẩm lỗi</Form.Label>
                    <Form.Control
                        type="file"
                        accept="image/*,video/*"
                        onChange={e => setReturnMedia(e.target.files[0])}
                    />
                </Form.Group>
            </Modal.Body>
            <Modal.Footer>
                <Button variant="secondary" onClick={() => setShowReturnModal(false)}>Hủy</Button>
                <Button variant="primary" onClick={handleRequestReturn} disabled={returnLoading}>
                    {returnLoading ? 'Đang gửi...' : 'Gửi yêu cầu'}
                </Button>
            </Modal.Footer>
        </Modal>
        </>
    );
}
