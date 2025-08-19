import React, { useState } from 'react';
import { Card, Button } from 'react-bootstrap';
import { Link } from 'react-router-dom';

export default function ProductCard({ product }) {
  const [isHovered, setIsHovered] = useState(false);

  if (!product) {
    return (
      <Card className="h-100 border-0 shadow-sm rounded-4">
        <Card.Body className="text-center text-muted">
          Không có sản phẩm
        </Card.Body>
      </Card>
    );
  }

  const variant = product.variants?.[0] || {};
  const price = Number(product?.price_products ?? 0);
//   const price = Number(variant?.sale_price ?? variant?.price ?? 0);
  const originalPrice = Number(variant?.price ?? 0);

  const discountPercent =
    variant?.sale_price && originalPrice > 0
      ? Math.round(100 - (price / originalPrice) * 100)
      : 0;

  const mainImage = product.thumbnail
    ? `http://localhost:8000/storage/${product.thumbnail}`
    : 'https://via.placeholder.com/300x300?text=No+Image';

  const hoverImage =
    product.images?.[0] &&
    `http://localhost:8000/storage/${product.images[0]}`;

  return (
    <Card
      className="h-100 border-0 shadow-sm rounded-4 position-relative overflow-hidden product-card"
      onMouseEnter={() => setIsHovered(true)}
      onMouseLeave={() => setIsHovered(false)}
    >
      {discountPercent > 0 && (
        <span className="position-absolute top-0 start-0 badge bg-danger rounded-end px-2 py-1 z-1">
          -{discountPercent}%
        </span>
      )}

      <div className="position-relative">
        <Card.Img
          variant="top"
          src={isHovered && hoverImage ? hoverImage : mainImage}
          alt={product?.name || 'Sản phẩm'}
          style={{
            height: 240,
            objectFit: 'contain',
            backgroundColor: '#fff',
            transition: 'transform 0.3s ease',
            transform: isHovered ? 'scale(1.05)' : 'scale(1)',
          }}
          className="p-3"
        />

        <div
          className="position-absolute top-50 start-50 translate-middle"
          style={{
            opacity: isHovered ? 1 : 0,
            transition: 'opacity 0.3s ease',
          }}
        >
          {/* <Button
            variant="primary"
            size="sm"
            className="rounded-pill px-3"
            onClick={() => alert('Đã thêm vào giỏ')}
          >
            Thêm vào giỏ
          </Button> */}
        </div>
      </div>

      <Card.Body className="text-center d-flex flex-column px-3">
        <Card.Title className="fs-6 fw-semibold text-truncate">
          {product?.name || 'Tên sản phẩm'}
        </Card.Title>

        <Card.Text className="text-primary fw-bold mb-1">
          {price.toLocaleString('vi-VN')}₫{' '}
          {variant?.sale_price && originalPrice > 0 && (
            <small className="text-muted text-decoration-line-through ms-1">
              {originalPrice.toLocaleString('vi-VN')}₫
            </small>
          )}
        </Card.Text>

        <Button
          as={Link}
          to={`/products/${product?.slug || ''}`}
          variant="outline-secondary"
          size="sm"
          className="mt-auto rounded-pill px-3"
        >
          Xem chi tiết
        </Button>
      </Card.Body>
    </Card>
  );
}
