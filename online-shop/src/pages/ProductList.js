import React, { useEffect, useState } from 'react';
import { Container, Row, Col, Card, Button, Spinner } from 'react-bootstrap';
import axios from 'axios';
import { Link } from 'react-router-dom';

const ProductList = () => {
  const [products, setProducts] = useState([]);
  const [loading, setLoading] = useState(true);

  const fetchProducts = async () => {
    try {
      const res = await axios.get(`${process.env.REACT_APP_API_URI}/products`);
      setProducts(res.data.data || []);
    } catch (err) {
      console.error('Lỗi khi tải sản phẩm:', err);
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => {
    fetchProducts();
  }, []);

  const formatPrice = (price) =>
    typeof price === 'number' ? price.toLocaleString() + ' đ' : 'Liên hệ';

  return (
    <Container className="my-5">
      <h2 className="mb-4 text-center fw-bold">🛍️ Tất cả sản phẩm</h2>

      {loading ? (
        <div className="text-center py-5">
          <Spinner animation="border" />
          <div className="mt-2">Đang tải sản phẩm...</div>
        </div>
      ) : (
        <Row>
          {products.map((product) => (
            <Col key={product.id} xs={12} sm={6} md={4} lg={3} className="mb-4">
              <Card className="h-100 shadow-sm border-0">
                <Link to={`/products/${product.slug}`}>
                  <Card.Img
                    variant="top"
                    src={product.img}
                    alt={product.name}
                    style={{
                      height: '250px',
                      objectFit: 'contain',
                      backgroundColor: '#f8f9fa',
                      borderTopLeftRadius: '0.5rem',
                      borderTopRightRadius: '0.5rem',
                    }}
                  />
                </Link>
                <Card.Body className="d-flex flex-column text-center">
                  <Card.Title className="text-truncate">
                    <Link
                      to={`/products/${product.slug}`}
                      className="text-decoration-none text-dark fw-semibold"
                    >
                      {product.name}
                    </Link>
                  </Card.Title>
                  <Card.Text className="text-danger fw-bold">
                    {formatPrice(product.price_products)}
                  </Card.Text>
                  <Button
                    as={Link}
                    to={`/products/${product.slug}`}
                    variant="primary"
                    size="sm"
                    className="mt-auto"
                  >
                    Xem chi tiết
                  </Button>
                </Card.Body>
              </Card>
            </Col>
          ))}
        </Row>
      )}
    </Container>
  );
};

export default ProductList;
