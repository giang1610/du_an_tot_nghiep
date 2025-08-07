import { useEffect, useState, useCallback } from 'react';
import { useParams, Link } from 'react-router-dom';
import {
    Container, Card, Table, Spinner, Alert,
    Button, Form, Badge, Modal, Row, Col
} from 'react-bootstrap';
import axios from 'axios';
import '../css/OrderDetail.css';
import { listenToOrderStatusRealtime } from '../realtime/orderStatusRealtime';
import InteractiveStarRating from '../components/InteractiveStarRating';

const STATUS_LABELS = {
    pending: 'Chờ xử lý',
    processing: 'Đang xử lý',
    picking: 'Đang lấy hàng',
    shipping: 'Đang giao hàng',
    shipped: 'Đã giao hàng',
    completed: 'Hoàn thành',
    cancelled: 'Đã hủy',
    failed: 'Giao hàng thất bại',
    return_requested: 'Đã yêu cầu hoàn hàng',
    returned: 'Hoàn hàng',
    failed_1: 'Giao hàng thất bại lần 1',
    failed_2: 'Giao hàng thất bại lần 2',
    restocked: 'Hàng đã trả kho',
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
    shipping: 'info',
    shipped: 'success',
    return_requested: 'warning',
    returned: 'secondary',
};

const PAYMENT_METHOD_LABELS = {
    cod: 'Thanh toán khi nhận hàng',
    momo: 'Ví Momo',
    vnpay: 'VNPay',
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

    const [showReturnModal, setShowReturnModal] = useState(false);
    const [returnReason, setReturnReason] = useState('');
    const [returnMedia, setReturnMedia] = useState([]);
    const [returnLoading, setReturnLoading] = useState(false);
    const [showCancelConfirm, setShowCancelConfirm] = useState(false);
    const [showReviewModal, setShowReviewModal] = useState(false);
    const [reviewItem, setReviewItem] = useState(null);
    const [reviewContent, setReviewContent] = useState('');
    const [reviewRating, setReviewRating] = useState(5);
    const [reviewLoading, setReviewLoading] = useState(false);
    const [reviewMedia, setReviewMedia] = useState([]);
    const [reviewMediaPreviews, setReviewMediaPreviews] = useState([]);
    const [showConfirmReceived, setShowConfirmReceived] = useState(false);
    const [confirmReceivedLoading, setConfirmReceivedLoading] = useState(false);
    const [returnMediaPreviews, setReturnMediaPreviews] = useState([]);
    const [currentUserId, setCurrentUserId] = useState(null);

    useEffect(() => {
    const token = localStorage.getItem('token');
    if (token) {
        try {
        const payload = JSON.parse(atob(token.split('.')[1]));
        setCurrentUserId(payload.sub || payload.id); // Tuỳ JWT bạn
        } catch (err) {
        console.error('Decode JWT thất bại:', err);
        }
    }
    }, []);

    useEffect(() => {
        const channel = listenToOrderStatusRealtime((orderIdFromSocket, newStatus) => {
            if (Number(orderIdFromSocket) === Number(id)) {
                console.log('[Realtime] Cập nhật trạng thái mới:', newStatus);
                setOrder(prev => {
                    if (!prev) return prev;
                    return { ...prev, status: newStatus };
                });
            }
        });
        return () => channel.stopListening('.order.updated');
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
        const formData = new FormData();
        formData.append('reason', returnReason);
        returnMedia.forEach(file => formData.append('media[]', file));

        setReturnLoading(true);
        try {
            await axios.post(`${process.env.REACT_APP_API_URL}/orders/${id}/request-return`, formData, {
                headers: {
                    Authorization: `Bearer ${token}`,
                    'Content-Type': 'multipart/form-data'
                }
            });
            alert('Đã gửi yêu cầu hoàn đơn!');
            setShowReturnModal(false);
            setReturnReason('');
            setReturnMedia([]);
            fetchOrder(); // reload lại đơn hàng
        } catch {
            alert('Yêu cầu hoàn đơn thất bại!');
        } finally {
            setReturnLoading(false);
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
        // Lấy danh sách review của sản phẩm này
        const reviews = reviewItem.reviews || [];
        const count = reviews.length;
        const completedAt = new Date(order.completed_at);
        const now = new Date();
        const diffDays = Math.floor((now - completedAt) / (1000 * 60 * 60 * 24));

        // Nếu đã có 1 đánh giá và chưa đủ 7 ngày thì không cho đánh giá lần 2
        if (count === 1 && diffDays < 7) {
            alert('Bạn chỉ có thể đánh giá lần 2 sau khi đủ 7 ngày kể từ lần đánh giá đầu tiên.');
            return;
        }
        if (reviewMedia.length > 5) {
            alert("Chỉ được chọn tối đa 5 file ảnh/video!");
            return;
        }
        setReviewLoading(true);
        try {
            const formData = new FormData();
            formData.append('order_id', order.id);
            formData.append('product_id', reviewItem.product_variant?.product_id || reviewItem.product_variant?.product?.id);
            formData.append('product_variant_id', reviewItem.product_variant?.id || reviewItem.product_variant_id);
            formData.append('rating', reviewRating);
            formData.append('content', reviewContent);
            reviewMedia.forEach(file => formData.append('media[]', file));

            await axios.post(`${process.env.REACT_APP_API_URL}/reviews`, formData, {
                headers: {
                    Authorization: `Bearer ${token}`,
                    'Content-Type': 'multipart/form-data'
                }
            });
            alert('Đánh giá thành công!');
            setShowReviewModal(false);
            setReviewMedia([]);
            fetchOrder();
        } catch (err) {
             if (err.response?.data?.message) {
                alert(err.response.data.message);
            } else {
                alert('Gửi đánh giá thất bại.');
            }
            console.error(err);
        } finally {
            setReviewLoading(false);
        }
    };

    const handleReviewMediaChange = (e) => {
        const files = Array.from(e.target.files);
        setReviewMedia(files);
        setReviewMediaPreviews(files.map(file => URL.createObjectURL(file)));
    };

        const handleReturnMediaChange = (e) => {
        const files = Array.from(e.target.files);
        setReturnMedia(files);
        setReturnMediaPreviews(files.map(file => URL.createObjectURL(file)));
    };

    if (loading) return <Spinner />;
    if (error) return <Alert variant="danger">{error}</Alert>;
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

            <Row className="mb-3">
                <Col md={5}>
                    <Card>
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

                            <p className="mb-0">
                                <strong>Phương thức thanh toán:</strong>{' '}
                                {PAYMENT_METHOD_LABELS[order.payment_method] || 'Không rõ'}
                            </p>
                            {order.return_reason && (
                                <p className="mt-3">
                                    <strong>Lý do hoàn đơn:</strong><br />
                                    <span className="border rounded d-block p-2 bg-light">{order.return_reason}</span>
                                    {/* Hiển thị ảnh/video minh chứng nếu có */}
                                    {order.return_media && (() => {
                                        let mediaList = [];
                                        try {
                                            // Nếu backend trả về JSON string, parse ra mảng
                                            mediaList = Array.isArray(order.return_media)
                                                ? order.return_media
                                                : JSON.parse(order.return_media);
                                        } catch {
                                            // Nếu lỗi parse, fallback về mảng rỗng
                                            mediaList = [];
                                        }
                                        return (
                                            <div className="mt-2 d-flex flex-wrap gap-2">
                                                {mediaList.map((path, idx) =>
                                                    /\.(jpg|jpeg|png)$/i.test(path)
                                                        ? (
                                                            <img
                                                                key={idx}
                                                                src={`${process.env.REACT_APP_API_URL.replace('/api', '')}/storage/${path}`}
                                                                alt="Ảnh minh chứng hoàn đơn"
                                                                style={{ maxWidth: 150, borderRadius: 8 }}
                                                            />
                                                        )
                                                        : (
                                                            <video
                                                                key={idx}
                                                                src={`${process.env.REACT_APP_API_URL.replace('/api', '')}/storage/${path}`}
                                                                controls
                                                                style={{ maxWidth: 150, borderRadius: 8 }}
                                                            />
                                                        )
                                                )}
                                            </div>
                                        );
                                    })()}
                                </p>
                            )}
                        </Card.Body>
                    </Card>
                </Col>

                <Col md={7}>
                    <Card>
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
                                        const completedAt = new Date(order.completed_at);
                                        const now = new Date();
                                        const diffDays = Math.floor((now - completedAt) / (1000 * 60 * 60 * 24));
                                        let canReview = count === 0 || (count === 1 && diffDays >= 7);

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
                                                        {reviews.filter(r => r.status || r.user_id === currentUserId).map(r => (
                                                            <div key={r.id} className="border rounded mb-1 p-1">
                                                                {'★'.repeat(r.rating)} - {r.content}
                                                                {r.media && (() => {
                                                                    let mediaList = [];
                                                                    try {
                                                                        mediaList = Array.isArray(r.media) ? r.media : JSON.parse(r.media);
                                                                    } catch {
                                                                        mediaList = [];
                                                                    }
                                                                    return (
                                                                        <div className="mt-2 d-flex flex-wrap gap-2">
                                                                            {mediaList.map((path, idx) =>
                                                                                /\.(jpg|jpeg|png)$/i.test(path)
                                                                                    ? (
                                                                                        <img
                                                                                            key={`review-media-${r.id}-${idx}`}
                                                                                            src={`${process.env.REACT_APP_API_URL.replace('/api', '')}/storage/${path}`}
                                                                                            alt="Ảnh đánh giá"
                                                                                            width={120}
                                                                                            style={{ borderRadius: 8 }}
                                                                                        />
                                                                                    )
                                                                                    : (
                                                                                        <video
                                                                                            key={`review-media-${r.id}-${idx}`}
                                                                                            src={`${process.env.REACT_APP_API_URL.replace('/api', '')}/storage/${path}`}
                                                                                            controls
                                                                                            width={180}
                                                                                            style={{ borderRadius: 8 }}
                                                                                        />
                                                                                    )
                                                                            )}
                                                                        </div>
                                                                    );
                                                                })()}
                                                            </div>
                                                        ))}
                                                        {count >= 2 && <span className="text-muted">Đã đánh giá đủ</span>}
                                                        {canReview && count < 2 && order.status === 'completed' && (
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
                </Col>
            </Row>

            <Card>
                <Card.Body className="d-flex justify-content-between align-items-center flex-wrap">
                    <div>
                        <div className="d-flex flex-column gap-2">
                            {order.status === 'shipped' && (
                                <Button variant="success" size="sm" onClick={() => setShowConfirmReceived(true)}>
                                    Đã nhận hàng
                                </Button>
                            )}
                            {order.status === 'shipped' && (() => {
                                const shippedAt = new Date(order.shipped_at || order.updated_at || order.created_at);
                                const now = new Date();
                                const diffDays = Math.floor((now - shippedAt) / (1000 * 60 * 60 * 24));
                                return diffDays <= 7 ? (
                                    <Button disabled={returnLoading} onClick={() => setShowReturnModal(true)}>
                                        {returnLoading ? 'Đang gửi yêu cầu...' : 'Yêu cầu hoàn đơn'}
                                    </Button>
                                ) : null;

                            })()}

                        </div>

                        <p className="mt-3 mb-0">
                            <strong>Thanh toán:</strong>{' '}
                            <Badge bg={paymentStatusBadgeVariant[order.status === 'cancelled' ? 'failed' : order.payment_status] || 'secondary'}>
                                {PAYMENT_STATUS_LABELS[order.status === 'cancelled' ? 'failed' : order.payment_status] || 'Không rõ'}
                            </Badge>
                            {order.payment_status !== 'paid' && order.payment_method === 'vnpay' && (
                                <div className="mt-2">
                                    <Link
                                        to={`/vnpay-continue/${order.id}`}
                                        className="btn btn-outline-warning btn-sm"
                                    >
                                        Tiếp tục thanh toán
                                    </Link>
                                </div>
                            )}
                        </p>
                        {order.status === 'pending' && (
                            <Button variant="danger" size="sm" onClick={() => setShowCancelConfirm(true)} className='mt-2'>
                                Hủy đơn
                            </Button>
                        )}


                    </div>
                    <Link to="/orders">
                        <Button variant="secondary" size="sm" className="mt-3 mt-md-0">← Trở lại</Button>
                    </Link>
                </Card.Body>
            </Card>

            {/* Modal HOÀN ĐƠN */}
            <Modal show={showReturnModal} onHide={() => setShowReturnModal(false)} centered>
                <Modal.Header closeButton><Modal.Title>Yêu cầu hoàn đơn</Modal.Title></Modal.Header>
                <Modal.Body>
                    <Form.Group className="mt-2">
                        <Form.Label>Ảnh/Video sản phẩm lỗi</Form.Label>
                        <Form.Control
                            type="file"
                            accept="image/*,video/*"
                            multiple
                            onChange={handleReturnMediaChange}
                        />
                        <div className="d-flex flex-wrap gap-2 mt-2">
                            {returnMediaPreviews.map((url, idx) => {
                                const file = returnMedia[idx];
                                if (!file) return null; // Fix lỗi undefined
                                return file.type && file.type.startsWith('image/')
                                    ? (
                                        <div key={idx} style={{ position: 'relative' }}>
                                            <img
                                                src={url}
                                                alt="preview"
                                                style={{ width: 120, height: 120, objectFit: 'cover', borderRadius: 8, border: '1px solid #ddd' }}
                                            />
                                            <Button
                                                size="sm"
                                                variant="outline-danger"
                                                style={{ position: 'absolute', top: 2, right: 2, padding: '2px 6px' }}
                                                onClick={() => {
                                                    setReturnMedia(prev => prev.filter((_, i) => i !== idx));
                                                    setReturnMediaPreviews(prev => prev.filter((_, i) => i !== idx));
                                                }}
                                            >X</Button>
                                        </div>
                                    )
                                    : (
                                        <div key={idx} style={{ position: 'relative' }}>
                                            <video
                                                src={url}
                                                controls
                                                style={{ width: 120, height: 120, borderRadius: 8, border: '1px solid #ddd' }}
                                            />
                                            <Button
                                                size="sm"
                                                variant="outline-danger"
                                                style={{ position: 'absolute', top: 2, right: 2, padding: '2px 6px' }}
                                                onClick={() => {
                                                    setReturnMedia(prev => prev.filter((_, i) => i !== idx));
                                                    setReturnMediaPreviews(prev => prev.filter((_, i) => i !== idx));
                                                }}
                                            >X</Button>
                                        </div>
                                    );
                            })}
                        </div>
                    </Form.Group>
                    <Form.Group>
                        <Form.Label>Lý do hoàn đơn</Form.Label>
                        <Form.Control as="textarea" rows={4} value={returnReason} onChange={(e) => setReturnReason(e.target.value)} placeholder="Nhập lý do chi tiết..." />
                    </Form.Group>
                </Modal.Body>
                <Modal.Footer>
                    <Button variant="secondary" onClick={() => setShowReturnModal(false)}>Hủy</Button>
                    <Button variant="primary" onClick={handleRequestReturn}>Gửi yêu cầu</Button>
                </Modal.Footer>
            </Modal>

            {/* Modal HỦY */}
            <Modal show={showCancelConfirm} onHide={() => setShowCancelConfirm(false)} centered>
                <Modal.Header closeButton><Modal.Title>Xác nhận hủy đơn</Modal.Title></Modal.Header>
                <Modal.Body>Bạn có chắc chắn muốn hủy đơn này?</Modal.Body>
                <Modal.Footer>
                    <Button variant="secondary" onClick={() => setShowCancelConfirm(false)}>Đóng</Button>
                    <Button variant="danger" onClick={handleCancelOrder}>Xác nhận hủy</Button>
                </Modal.Footer>
            </Modal>

            {/* Modal REVIEW */}
            <Modal show={showReviewModal} onHide={() => setShowReviewModal(false)} centered>
                <Modal.Header closeButton><Modal.Title>Đánh giá sản phẩm</Modal.Title></Modal.Header>
                <Modal.Body>
                    <Form.Group className="mb-3">
                        <Form.Label>Đánh giá sao</Form.Label>
                        <InteractiveStarRating rating={reviewRating} onChange={setReviewRating} />
                    </Form.Group>
                    <Form.Group className="mt-2">
                        <Form.Label>Ảnh/Video sản phẩm</Form.Label>
                        <Form.Control
                            type="file"
                            accept="image/*,video/*"
                            multiple
                            onChange={handleReviewMediaChange}
                        />
                        <div className="d-flex flex-wrap gap-2 mt-2">
                            {reviewMediaPreviews.map((url, idx) => {
                                const file = reviewMedia[idx];
                                if (!file) return null; // Fix lỗi undefined
                                return file.type && file.type.startsWith('image/')
                                    ? (
                                        <div key={idx} style={{ position: 'relative' }}>
                                            <img
                                                src={url}
                                                alt="preview"
                                                style={{ width: 120, height: 120, objectFit: 'cover', borderRadius: 8, border: '1px solid #ddd' }}
                                            />
                                            <Button
                                                size="sm"
                                                variant="outline-danger"
                                                style={{ position: 'absolute', top: 2, right: 2, padding: '2px 6px' }}
                                                onClick={() => {
                                                    setReviewMedia(prev => prev.filter((_, i) => i !== idx));
                                                    setReviewMediaPreviews(prev => prev.filter((_, i) => i !== idx));
                                                }}
                                            >X</Button>
                                        </div>
                                    )
                                    : (
                                        <div key={idx} style={{ position: 'relative' }}>
                                            <video
                                                src={url}
                                                controls
                                                style={{ width: 120, height: 120, borderRadius: 8, border: '1px solid #ddd' }}
                                            />
                                            <Button
                                                size="sm"
                                                variant="outline-danger"
                                                style={{ position: 'absolute', top: 2, right: 2, padding: '2px 6px' }}
                                                onClick={() => {
                                                    setReviewMedia(prev => prev.filter((_, i) => i !== idx));
                                                    setReviewMediaPreviews(prev => prev.filter((_, i) => i !== idx));
                                                }}
                                            >X</Button>
                                        </div>
                                    );
                            })}
                        </div>
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

            {/* Modal XÁC NHẬN NHẬN HÀNG */}
            <Modal show={showConfirmReceived} onHide={() => setShowConfirmReceived(false)} centered>
                <Modal.Header closeButton><Modal.Title>Đã nhận hàng</Modal.Title></Modal.Header>
                <Modal.Body>Bạn có chắc chắn muốn xác nhận đã nhận được hàng?</Modal.Body>
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
