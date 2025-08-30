import React from 'react';
import { Card } from 'react-bootstrap';

const ReviewCard = ({ review, baseUrl }) => {
    let mediaList = [];
    try {
        mediaList = Array.isArray(review.media)
            ? review.media
            : JSON.parse(review.media || '[]');
    } catch {
        mediaList = [];
    }

    const formatDate = (iso) => {
        if (!iso) return '';
        const d = new Date(iso);
        return d.toLocaleDateString('vi-VN');
    };

    return (
        <Card className="mb-4 border-0 shadow-sm">
            <Card.Body>
                <div className="d-flex justify-content-between align-items-center mb-2">
                    <div>
                        <strong className="fs-5">{review.user?.name || 'Khách hàng'}</strong>
                        <div>
                            {[...Array(5)].map((_, i) => (
                                <span
                                    key={i}
                                    style={{
                                        color: i < review.rating ? '#f1c40f' : '#dcdcdc',
                                        fontSize: '1.2rem',
                                    }}
                                >
                                    ★
                                </span>
                            ))}
                        </div>
                    </div>
                    <small className="text-muted">{formatDate(review.created_at)}</small>
                </div>

                <Card.Text className="fst-italic">{review.content}</Card.Text>

                {mediaList.length > 0 && (
                    <div className="mt-3 d-flex flex-wrap gap-3">
                        {mediaList.map((path, idx) =>
                            /\.(jpg|jpeg|png)$/i.test(path)
                                ? (
                                    <img
                                        key={idx}
                                        src={`${baseUrl}/storage/${path}`}
                                        alt="Ảnh đánh giá"
                                        className="rounded shadow-sm"
                                        style={{ width: 100, height: 100, objectFit: 'cover' }}
                                    />
                                )
                                : (
                                    <video
                                        key={idx}
                                        src={`${baseUrl}/storage/${path}`}
                                        controls
                                        className="rounded shadow-sm"
                                        style={{ width: 220, height: 160, objectFit: 'cover' }}
                                    />
                                )
                        )}
                    </div>
                )}
            </Card.Body>
        </Card>
    );
};

export default ReviewCard;
