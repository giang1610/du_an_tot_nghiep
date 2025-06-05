  import React, { useEffect, useState } from 'react';
  import { Container, Row, Col, Card, Button, Spinner } from 'react-bootstrap';
  import axios from 'axios';
  import { Link } from 'react-router-dom';

  const ProductList = () => {
    const [products, setProducts] = useState([]);
    const [loading, setLoading] = useState(true);

    useEffect(() => {
      axios.get('http://localhost:8000/api/products')
        .then(response => {
          setProducts(response.data.data); // danh sách sản phẩm từ Laravel
          setLoading(false);
        })
        .catch(error => {
          console.error('Lỗi khi tải sản phẩm:', error);
          setLoading(false);
        });
    }, []);

    return (
      <Container className="my-5">

        {loading ? (
          <div className="text-center"><Spinner animation="border" /></div>
        ) : (
          <Row>
            {products.map(product => (
              <Col key={product.id} sm={6} md={3} className="mb-4">
                <Card className="h-100">
                  <Card.Img
                    variant="top"
                    src={product.img}
                    style={{ height: '200px', objectFit: 'cover' }}
                    alt={product.name}
                  />
                  <Card.Body>
                    <Card.Title>
                      <Link to={`/products/${product.id}`} style={{ textDecoration: 'none' }}>
                        {product.name}
                      </Link>
                    </Card.Title>
                    <Link to={`/products/${product.id}`}>
                      <Card.Img
                        variant="top"
                        src={product.img}
                        style={{ height: '200px', objectFit: 'cover', cursor: 'pointer' }}
                        alt={product.name}
                      />
                    </Link>
                    {/* <Card.Text className="text-danger fw-bold">{product.price.toLocaleString()} đ</Card.Text> */}
                    <Button variant="primary" size="sm" className="w-100">Mua Ngay</Button>
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
