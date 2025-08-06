import { Card, Button } from 'react-bootstrap';
import { Link } from 'react-router-dom';

export default function ProductCard({ product }) {
  const variant = product.variants[0];
  const price = Number(variant?.sale_price ?? variant?.price);
  const originalPrice = Number(variant?.price);
  const thumbnailUrl = product.thumbnail
    ? `http://localhost:8000/storage/${product.thumbnail}`
    : 'https://via.placeholder.com/300x300?text=No+Image';

  const discountPercent = variant?.sale_price
    ? Math.round(100 - (price / originalPrice) * 100)
    : 0;

  return (
    <Card className="h-100 border-0 shadow-sm rounded-4 position-relative hover-scale">
      {/* Badge giảm giá */}
      {discountPercent > 0 && (
        <span className="position-absolute top-0 start-0 badge bg-danger rounded-end px-2 py-1 z-1">
          -{discountPercent}%
        </span>
      )}

      {/* Ảnh sản phẩm */}
      <Card.Img
        variant="top"
        src={thumbnailUrl}
        alt={product.name}
        style={{
          height: 220,
          objectFit: 'contain',
          backgroundColor: '#fff',
        }}
        className="p-3 border-bottom"
      />

      {/* Thông tin */}
      <Card.Body className="text-center d-flex flex-column px-3">
        <Card.Title className="fs-6 fw-semibold text-truncate">
          {product.name}
        </Card.Title>

        <Card.Text className="text-primary fw-bold mb-1">
          {price.toLocaleString('vi-VN')}₫{' '}
          {variant?.sale_price && (
            <small className="text-muted text-decoration-line-through ms-1">
              {originalPrice.toLocaleString('vi-VN')}₫
            </small>
          )}
        </Card.Text>

        <Button
          as={Link}
          to={`/products/${product.slug}`}
          variant="primary"
          size="sm"
          className="mt-auto rounded-pill px-3"
        >
          Xem chi tiết
        </Button>
      </Card.Body>
    </Card>
  );
}
