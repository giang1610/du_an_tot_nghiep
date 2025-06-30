import { useState } from 'react';
import { Form, Button } from 'react-bootstrap';
import { FaStar } from 'react-icons/fa';
import { useAuth } from '../context/AuthContext';

export default function ProductReview() {
    const [rating, setRating] = useState(0);
    const [comment, setComment] = useState('');
    const [submitted, setSubmitted] = useState(false);
    const { user } = useAuth();
    if (!user) return <div className="alert alert-warning">Bạn cần đăng nhập để đánh giá sản phẩm.</div>;
    return (
        <div className="border rounded p-3">
            <h6 className="fw-bold mb-3">Đánh giá sản phẩm</h6>
            {!submitted ? (
                <Form onSubmit={(e) => { e.preventDefault(); setSubmitted(true); }}>
                    <div className="mb-2">
                        {[1, 2, 3, 4, 5].map((star) => (
                            <FaStar
                                key={star}
                                size={24}
                                onClick={() => setRating(star)}
                                className="me-1"
                                color={star <= rating ? '#ffc107' : '#ccc'}
                                style={{ cursor: 'pointer' }}
                            />
                        ))}
                    </div>
                    <Form.Group className="mb-3">
                        <Form.Control
                            as="textarea"
                            rows={3}
                            placeholder="Viết nhận xét..."
                            value={comment}
                            onChange={(e) => setComment(e.target.value)}
                            required
                        />
                    </Form.Group>
                    <Button type="submit">Gửi đánh giá</Button>
                </Form>
            ) : (
                <div className="alert alert-success">Cảm ơn bạn đã đánh giá sản phẩm!</div>
            )}
        </div>
    );
}
