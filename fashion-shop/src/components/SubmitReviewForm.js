// components/SubmitReviewForm.jsx
import { useState } from 'react';
import axios from 'axios';

function SubmitReviewForm({ variantId, orderId }) {
  const [rating, setRating] = useState(5);
  const [content, setContent] = useState('');
  const [message, setMessage] = useState(null);

  const token = localStorage.getItem('token');

  const handleSubmit = async (e) => {
    e.preventDefault();
    try {
      const res = await axios.post(
        'http://127.0.0.1:8000/api/reviews',
        {
          order_id: orderId,
          product_variant_id: variantId,
          rating,
          content,
        },
        {
          headers: {
            Authorization: `Bearer ${token}`,
          },
        }
      );
      setMessage('🎉 Gửi đánh giá thành công!');
      setRating(5);
      setContent('');
    } catch (err) {
      console.error(err);
      setMessage('❌ Lỗi khi gửi đánh giá');
    }
  };

  return (
    <div className="card card-body border border-primary mb-3">
      <h5>Viết đánh giá</h5>
      {message && <div className="alert alert-info">{message}</div>}
      <form onSubmit={handleSubmit}>
        <div className="mb-2">
          <label>Đánh giá sao (1–5):</label>
          <input
            type="number"
            className="form-control"
            value={rating}
            min="1"
            max="5"
            onChange={(e) => setRating(Number(e.target.value))}
            required
          />
        </div>
        <div className="mb-2">
          <label>Nội dung:</label>
          <textarea
            className="form-control"
            value={content}
            onChange={(e) => setContent(e.target.value)}
            rows={3}
          />
        </div>
        <button type="submit" className="btn btn-primary">
          Gửi đánh giá
        </button>
      </form>
    </div>
  );
}

export default SubmitReviewForm;
