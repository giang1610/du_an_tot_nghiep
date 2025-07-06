import { useEffect, useState, useCallback } from 'react';
import { useParams, Link } from 'react-router-dom';
import {
    Container, Table, Spinner, Alert, Button, Modal, Row, Col, Card, Form
} from 'react-bootstrap';
import axios from 'axios';

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
    const [showReviewModal, setShowReviewModal] = useState(false);
    const [reviewItem, setReviewItem] = useState(null);
    const [reviewContent, setReviewContent] = useState('');
    const [reviewRating, setReviewRating] = useState(5);
    const [reviewLoading, setReviewLoading] = useState(false);
    const [productReviews, setProductReviews] = useState({});

    const token = localStorage.getItem('token');

    // Lấy chi tiết đơn hàng
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

    // Gọi khi order thay đổi
    useEffect(() => {
        if (order && order.items) {
            fetchProductReviews(order.items, order.id);
        }
    }, [order, fetchProductReviews]);

    // Gọi khi vào trang
    useEffect(() => {
        fetchOrder();
    }, [fetchOrder]);

    // Hủy đơn hàng
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

    // Cập nhật địa chỉ
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

    // Yêu cầu hoàn đơn
    const handleRequestReturn = async () => {
        try {
            await axios.post(
                `${process.env.REACT_APP_API_URL}/orders/${order.id}/request-return`,
                {},
                { headers: { Authorization: `Bearer ${token}` } }
            );
            fetchOrder();
            alert('Yêu cầu hoàn đơn đã được gửi!');
        } catch (err) {
            alert('Yêu cầu hoàn đơn thất bại!');
            console.error(err);
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
            // Cập nhật lại đánh giá cho sản phẩm vừa đánh giá
            fetchProductReviews(order.items, order.id);
        } catch (err) {
            if (err.response && err.response.data && err.response.data.error) {
                alert(err.response.data.error);
            } else {
                alert('Gửi đánh giá thất bại!');
            }
            console.error(err);
        } finally {
            setReviewLoading(false);
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

                    {/* Hiển thị nút theo trạng thái */}
                    {order.status === 'shipped' && (
                        <Button
                            variant="success"
                            className="mb-2"
                            onClick={async () => {
                                try {
                                    await axios.post(
                                        `${process.env.REACT_APP_API_URL}/orders/${order.id}/confirm-received`,
                                        {},
                                        { headers: { Authorization: `Bearer ${token}` } }
                                    );
                                    fetchOrder();
                                } catch (err) {
                                    alert('Xác nhận nhận hàng thất bại!');
                                }
                            }}
                        >
                            Xác nhận đã nhận hàng
                        </Button>
                    )}

                    {order.status === 'delivered' && (
                        <>
                            <Button
                                variant="warning"
                                className="mb-2"
                                onClick={handleRequestReturn}
                            >
                                Hoàn đơn
                            </Button>
                            <div className="text-muted mb-2">
                                Đơn hàng sẽ tự động hoàn thành sau 3 ngày kể từ khi bạn xác nhận đã nhận hàng.<br />
                                Sau thời gian này, bạn sẽ không thể yêu cầu trả hàng nữa.
                            </div>
                        </>
                    )}

                    {order.status === 'completed' && (
                        <div className="text-success mb-2">
                            Đơn hàng đã hoàn thành.
                        </div>
                    )}

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
                                        {order.status === 'delivered' && <th>Đánh giá</th>}
                                    </tr>
                                </thead>
                                <tbody>
                                    {order.items.map(item => (
                                        <tr key={item.id}>
                                            <td>
                                                <img
                                                    src={
                                                        item.product_variant?.img ||
                                                        item.product_variant?.product?.img ||
                                                        'https://via.placeholder.com/50x50?text=No+Image'
                                                    }
                                                    alt={item.product_variant?.product?.name || 'Ảnh sản phẩm'}
                                                    style={{ width: 50, height: 50, objectFit: 'cover' }}
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
                                            {order.status === 'delivered' && (
                                                <td>
                                                    <Button
                                                        variant="outline-primary"
                                                        size="sm"
                                                        onClick={() => handleShowReviewModal(item)}
                                                    >
                                                        Đánh giá
                                                    </Button>
                                                    {/* Hiển thị đánh giá nếu có */}
                                                    {productReviews[item.product_variant_id] && productReviews[item.product_variant_id].length > 0 && (
                                                        <div className="mt-2 text-start">
                                                            {productReviews[item.product_variant_id].map((review, idx) => (
                                                                <div key={review.id || idx} style={{ borderTop: '1px solid #eee', paddingTop: 4 }}>
                                                                    <span className="text-warning">
                                                                        {'★'.repeat(review.rating)}
                                                                        {'☆'.repeat(5 - review.rating)}
                                                                    </span>
                                                                    <span className="ms-2">{review.content}</span>
                                                                    <div className="small text-muted">
                                                                        {review.user?.name} - {new Date(review.created_at).toLocaleDateString()}
                                                                    </div>
                                                                </div>
                                                            ))}
                                                        </div>
                                                    )}
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