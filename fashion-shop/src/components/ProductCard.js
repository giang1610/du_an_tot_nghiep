import { Card, Button, Badge } from 'react-bootstrap';
import { Link } from 'react-router-dom';

export default function ProductCard({ product }) {
  const thumbnailUrl = product.thumbnail
    ? `http://localhost:8000/storage/${product.thumbnail}`
    : 'https://via.placeholder.com/300x300?text=No+Image';
  return (
    <Card className="h-100 border-0 shadow-sm position-relative hover-scale">
      <Card.Img
        variant="top"
        src={thumbnailUrl}
        alt={product.name}
        style={{ height: 200, objectFit: 'contain', backgroundColor: '#fff' }}
      />
      {/* <Badge bg="danger" className="position-absolute top-0 start-0 m-2">-30%</Badge> */}
      <Card.Body className="text-center">
        <Card.Title>{product.name}</Card.Title>
        <Card.Text className="text-danger fw-bold">
          {(() => {
            const variant = product.variants[0];
            const price = variant?.sale_price ?? variant?.price;
            return Number(price).toLocaleString() + '₫';
          })()}
          {product.variants[0]?.sale_price && (
            <small className="text-muted ms-2 text-decoration-line-through">
              {Number(product.variants[0].price).toLocaleString()}₫
            </small>
          )}
        </Card.Text>

        <Button as={Link} to={`/products/${product.slug}`} variant="outline-dark" size="sm">
          Xem chi tiết
        </Button>


      </Card.Body>
    </Card>
  );
}
