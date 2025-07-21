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
  const [userOrder, setUserOrder] = useState(null);
  const [canReview, setCanReview] = useState(false);
  const [loading, setLoading] = useState(true);
  const [sending, setSending] = useState(false);
  const [message, setMessage] = useState(null);

  const [rating, setRating] = useState(0);
  const [content, setContent] = useState('');
  const [debouncedContent, setDebouncedContent] = useState('');
  const [reviews, setReviews] = useState([]);
  const [reviewMedia, setReviewMedia] = useState(null);


  useEffect(() => {
    const handler = setTimeout(() => setDebouncedContent(content), 300);
    return () => clearTimeout(handler);
  }, [content]);

  useEffect(() => {
    setLoading(true);
    const token = localStorage.getItem('token');

    if (!token || !selectedVariantId) {
      setCanReview(false);
      setLoading(false);
      return;
    }

    const authHeader = {
      headers: { Authorization: `Bearer ${token}` },
    };

    const ordersReq = axios.get(`${process.env.REACT_APP_API_URL}/orders/received-product`, {
      params: { product_variant_id: selectedVariantId },
      ...authHeader,
    });

    const reviewsReq = axios.get(`${process.env.REACT_APP_API_URL}/reviews`, {
      params: { product_id: productId },
      ...authHeader,
    });

    Promise.all([ordersReq, reviewsReq])
      .then(([orderRes, reviewsRes]) => {
        const { received, order_id } = orderRes.data;
        setCanReview(received);
        if (received) setUserOrder({ id: order_id });
        setReviews(reviewsRes.data.data || []);
      })
      .catch((err) => {
        console.error(err);
        setCanReview(false);
        setReviews([]);

      })
      .finally(() => setLoading(false));
  }, [productId, selectedVariantId]);

  const validationErrors = useMemo(() => {
    const errors = {};
    if (!userOrder?.id) errors.order = 'Không tìm thấy đơn hàng hợp lệ.';
    if (rating < 1 || rating > 5) errors.rating = 'Vui lòng chọn đánh giá từ 1 đến 5 sao.';
    if (debouncedContent.length > MAX_CONTENT_LENGTH)
      errors.content = `Nội dung không vượt quá ${MAX_CONTENT_LENGTH} ký tự.`;
    return errors;
  }, [userOrder, rating, debouncedContent]);

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
      const res = await axios.post(
        `${process.env.REACT_APP_API_URL}/reviews`,
        {
          order_id: userOrder.id,
          product_variant_id: selectedVariantId,
          rating,
          content: debouncedContent,
          media: reviewMedia ? reviewMedia.name : null,
        },
        {
          headers: { Authorization: `Bearer ${token}` },
        }
      );

      setMessage({ type: 'success', text: res.data.message || 'Đánh giá thành công!' });
      setRating(0);
      setContent('');
      setReviewMedia(null);

      const refreshed = await axios.get(`${process.env.REACT_APP_API_URL}/reviews`, {
        params: { product_id: productId },
        headers: { Authorization: `Bearer ${token}` },
      });
      setReviews(refreshed.data.data || []);
    } catch (error) {
      setMessage({
        type: 'danger',
        text: error.response?.data?.error || 'Gửi đánh giá thất bại.',
      });
    } finally {
      setSending(false);
    }
  }, [isValid, userOrder, rating, debouncedContent, selectedVariantId, productId, navigate]);

  if (loading) return <p>Đang tải đánh giá...</p>;

  return (
    <div className="mt-4">

    </div>
  );
}
