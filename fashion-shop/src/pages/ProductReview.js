import React, { useEffect, useState, useCallback, useMemo } from 'react';
import axios from 'axios';
import { Button, Alert, Form, Spinner } from 'react-bootstrap';
import { useNavigate } from 'react-router-dom';
import { FaStar } from 'react-icons/fa';

const MAX_CONTENT_LENGTH = 500;
const STAR_COUNT = 5;

function StarRating({ rating, setRating, disabled }) {
  return (
    <div>
      {[...Array(STAR_COUNT)].map((_, i) => {
        const starValue = i + 1;
        return (
          <label key={starValue} style={{ cursor: disabled ? 'default' : 'pointer' }}>
            <input
              type="radio"
              name="rating"
              value={starValue}
              style={{ display: 'none' }}
              disabled={disabled}
              onChange={() => setRating(starValue)}
              checked={rating === starValue}
            />
            <FaStar
              color={starValue <= rating ? '#ffc107' : '#e4e5e9'}
              size={30}
            />
          </label>
        );
      })}
    </div>
  );
}

export default function ProductReview({ productId, selectedVariantId }) {
  const navigate = useNavigate();
  const [userOrders, setUserOrders] = useState([]);
  const [canReview, setCanReview] = useState(false);
  const [loading, setLoading] = useState(true);
  const [sending, setSending] = useState(false);
  const [message, setMessage] = useState(null);

  const [selectedOrderId, setSelectedOrderId] = useState('');
  const [rating, setRating] = useState(0);
  const [content, setContent] = useState('');
  const [debouncedContent, setDebouncedContent] = useState('');
  const [reviews, setReviews] = useState([]);

  // Debounce content input
  useEffect(() => {
    const handler = setTimeout(() => setDebouncedContent(content), 300);
    return () => clearTimeout(handler);
  }, [content]);

  // Fetch user orders and reviews once on mount or productId change
  useEffect(() => {
    setLoading(true);
    const token = localStorage.getItem('token');
    if (!token) {
      setCanReview(false);
      setLoading(false);
      return;
    }
    const ordersReq = axios.get(`${process.env.REACT_APP_API_URL}/orders/received-product`, {
      params: { product_id: productId },
      headers: { Authorization: `Bearer ${token}` },
    });
    const reviewsReq = axios.get(`${process.env.REACT_APP_API_URL}/reviews`, {
      params: { product_id: productId },
    });
    Promise.all([ordersReq, reviewsReq])
      .then(([ordersRes, reviewsRes]) => {
        const orders = ordersRes.data.data || [];
        setUserOrders(orders);
        setCanReview(orders.length > 0);
        setReviews(reviewsRes.data.data || []);
      })
      .catch(() => {
        setCanReview(false);
        setReviews([]);
      })
      .finally(() => setLoading(false));
  }, [productId]);

  // Validate form fields
  const validationErrors = useMemo(() => {
    const errors = {};
    if (!selectedOrderId) errors.selectedOrderId = 'Vui lòng chọn đơn hàng đã nhận.';
    if (rating < 1 || rating > 5) errors.rating = 'Vui lòng chọn đánh giá từ 1 đến 5 sao.';
    if (debouncedContent.length > MAX_CONTENT_LENGTH) errors.content = `Nội dung không vượt quá ${MAX_CONTENT_LENGTH} ký tự.`;
    return errors;
  }, [selectedOrderId, rating, debouncedContent]);

  const isValid = Object.keys(validationErrors).length === 0;

  const handleSubmit = useCallback(async () => {
    if (!isValid) {
      setMessage({ type: 'warning', text: 'Vui lòng sửa lỗi trước khi gửi đánh giá.' });
      return;
    }
    const token = localStorage.getItem('token');
    if (!token) {
      setMessage({ type: 'warning', text: 'Vui lòng đăng nhập để đánh giá.' });
      navigate('/login');
      return;
    }
    setSending(true);
    setMessage(null);
    try {
      const res = await axios.post(`${process.env.REACT_APP_API_URL}/reviews`, {
        order_id: selectedOrderId,
        product_variant_id: selectedVariantId,
        rating,
        content: debouncedContent,
      }, {
        headers: { Authorization: `Bearer ${token}` },
      });
      setMessage({ type: 'success', text: res.data.message || 'Đánh giá thành công!' });
      setRating(0);
      setContent('');
      setSelectedOrderId('');
      // Refresh reviews
      const refreshed = await axios.get(`${process.env.REACT_APP_API_URL}/reviews`, { params: { product_id: productId } });
      setReviews(refreshed.data.data || []);
    } catch (error) {
      setMessage({ type: 'danger', text: error.response?.data?.error || 'Gửi đánh giá thất bại.' });
    } finally {
      setSending(false);
    }
  }, [isValid, selectedOrderId, rating, debouncedContent, selectedVariantId, productId, navigate]);

  if (loading) return <p>Đang tải thông tin đánh giá...</p>;

  return (
    <div className="mt-4">
      <h5>Đánh giá sản phẩm</h5>
      {!canReview && <Alert variant="info">Bạn chỉ có thể đánh giá sản phẩm sau khi mua và nhận hàng.</Alert>}

      {canReview && (
        <>
          <Form.Group className="mb-3">
            <Form.Label>Chọn đơn hàng đã nhận:</Form.Label>
            <Form.Select
              value={selectedOrderId}
              onChange={e => setSelectedOrderId(e.target.value)}
              isInvalid={!!validationErrors.selectedOrderId}
              disabled={sending}
            >
              <option value="">-- Chọn đơn hàng --</option>
              {userOrders.map(o => (
                <option key={o.id} value={o.id}>
                  Đơn hàng #{o.id} - Ngày nhận: {new Date(o.delivered_at).toLocaleDateString('vi-VN')}
                </option>
              ))}
            </Form.Select>
            <Form.Control.Feedback type="invalid">{validationErrors.selectedOrderId}</Form.Control.Feedback>
          </Form.Group>

          <Form.Group className="mb-3">
            <Form.Label>Đánh giá:</Form.Label>
            <StarRating rating={rating} setRating={setRating} disabled={sending} />
            {!!validationErrors.rating && <div className="text-danger mt-1" style={{ fontSize: '0.875rem' }}>{validationErrors.rating}</div>}
          </Form.Group>

          <Form.Group className="mb-3">
            <Form.Label>Nội dung đánh giá (không bắt buộc):</Form.Label>
            <Form.Control
              as="textarea"
              rows={3}
              value={content}
              onChange={e => setContent(e.target.value)}
              maxLength={MAX_CONTENT_LENGTH}
              placeholder="Viết cảm nhận của bạn về sản phẩm..."
              disabled={sending}
              isInvalid={!!validationErrors.content}
            />
            <Form.Control.Feedback type="invalid">{validationErrors.content}</Form.Control.Feedback>
          </Form.Group>

          {message && (
            <Alert variant={message.type} onClose={() => setMessage(null)} dismissible>
              {message.text}
            </Alert>
          )}

          <Button
            onClick={handleSubmit}
            disabled={sending || !isValid}
          >
            {sending ? <><Spinner animation="border" size="sm" className="me-2" />Đang gửi...</> : 'Gửi đánh giá'}
          </Button>

          <hr className="my-4" />
          <h5>Đánh giá đã có ({reviews.length})</h5>
          {!reviews.length && <p>Chưa có đánh giá nào cho sản phẩm này.</p>}
          {reviews.map(r => (
            <div key={r.id} className="mb-3 border-bottom pb-2">
              <strong>{r.user.name}</strong> - <small>{new Date(r.created_at).toLocaleDateString('vi-VN')}</small>
              <div style={{ color: '#ffc107' }}>
                {[...Array(STAR_COUNT)].map((_, i) => (
                  <FaStar key={i} color={i < r.rating ? '#ffc107' : '#e4e5e9'} />
                ))}
              </div>
              <p>{r.comment || <i>(Không có nội dung)</i>}</p>
            </div>
          ))}
        </>
      )}
    </div>
  );
}
