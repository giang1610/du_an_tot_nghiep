// src/components/ProductReview.jsx
import React, { useEffect, useState } from 'react';
import axios from 'axios';
import { Alert, Form, Button } from 'react-bootstrap';

export default function ProductReview({ productId, selectedVariantId }) {
  const [reviews, setReviews] = useState([]);
  const [canReview, setCanReview] = useState(false);
  const [rating, setRating] = useState(5);
  const [content, setContent] = useState('');
  const [message, setMessage] = useState('');
  const [orderId, setOrderId] = useState(null);

  const token = localStorage.getItem('token');
  const headers = token ? { Authorization: `Bearer ${token}` } : {};

  useEffect(() => {
    if (!productId) return;
    axios
      .get(`${process.env.REACT_APP_API_URL}/products/${productId}/reviews`)
      .then((res) => {
        setReviews(res.data.data || []);
      })
      .catch((err) => {
        console.warn('Lỗi khi tải đánh giá:', err);
      });
  }, [productId]);

  useEffect(() => {
    if (!selectedVariantId || !token) return;

    axios
      .get(`${process.env.REACT_APP_API_URL}/orders/received-product?product_variant_id=${selectedVariantId}`, {
        headers,
      })
      .then((res) => {
        if (res.data.received && res.data.order_id) {
          setCanReview(true);
          setOrderId(res.data.order_id);
        } else {
          setCanReview(false);
        }
      })
      .catch((err) => {
        console.warn('Lỗi kiểm tra quyền đánh giá:', err);
      });
  }, [selectedVariantId, token]);

  const handleSubmit = async (e) => {
    e.preventDefault();
    if (!orderId || !selectedVariantId) return;

    try {
      const res = await axios.post(
        `${process.env.REACT_APP_API_URL}/reviews`,
        {
          order_id: orderId,
          product_variant_id: selectedVariantId,
          rating,
          content,
        },
        { headers }
      );

      setMessage('🎉 Gửi đánh giá thành công!');
      setRating(5);
      setContent('');

      // Cập nhật lại danh sách đánh giá
      const reviewRes = await axios.get(`${process.env.REACT_APP_API_URL}/products/${productId}/reviews`);
      setReviews(reviewRes.data.data || []);
    } catch (err) {
      const msg = err?.response?.data?.error || 'Lỗi khi gửi đánh giá.';
      setMessage(msg);
    }
  };

  return (
    <div className="mt-4">
      <h5>Đánh giá sản phẩm</h5>
      {reviews.length === 0 ? (
        <p>Chưa có đánh giá nào.</p>
      ) : (
        <ul className="list-unstyled">
          {reviews.map((r) => (
            <li key={r.id} className="mb-3 border-bottom pb-2">
              <strong>{r.user?.name || 'Khách hàng'}</strong>
              <div>⭐ {r.rating}</div>
              <p>{r.content}</p>
            </li>
          ))}
        </ul>
      )}

      {canReview && (
        <Form onSubmit={handleSubmit} className="mt-4 border p-3 rounded">
          <h6>Gửi đánh giá của bạn:</h6>
          {message && <Alert variant="info">{message}</Alert>}

          <Form.Group className="mb-2">
            <Form.Label>Đánh giá sao (1–5):</Form.Label>
            <Form.Control
              type="number"
              min={1}
              max={5}
              value={rating}
              onChange={(e) => setRating(Number(e.target.value))}
              required
            />
          </Form.Group>

          <Form.Group className="mb-2">
            <Form.Label>Nội dung:</Form.Label>
            <Form.Control
              as="textarea"
              rows={3}
              value={content}
              onChange={(e) => setContent(e.target.value)}
            />
          </Form.Group>

          <Button type="submit" variant="primary">
            Gửi đánh giá
          </Button>
        </Form>
      )}
    </div>
  );
}
