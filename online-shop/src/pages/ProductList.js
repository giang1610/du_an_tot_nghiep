import React, { useEffect, useState } from 'react';
import { Container, Row, Col, Card, Button, Spinner } from 'react-bootstrap';
import axios from 'axios';
import { Link } from 'react-router-dom';

const ProductList = () => {
  const [products, setProducts] = useState([]);
  const [loading, setLoading] = useState(true);

  // Gọi API khi component được mount
  useEffect(() => {
    axios.get('http://localhost:8000/api/products')
      .then(response => {
        console.log('DATA:', response.data.data);
        setProducts(response.data.data); // lấy danh sách sản phẩm
        setLoading(false);
        
      })
      .catch(error => {
        console.error('Lỗi khi tải sản phẩm:', error);
        setLoading(false);
      });
      
  }, []);

  return (
    <Container className="my-5">
      {/* <h2 className="mb-4 text-center">Tất cả sản phẩm</h2> */}

      {loading ? (
        <div className="text-center">
          <Spinner animation="border" />
        </div>
      ) : (
        <Row>
          {products.map(product => (
            <Col key={product.id} sm={6} md={3} className="mb-4">
              <Card className="h-100">
                {/* Ảnh sản phẩm click được */}
                <Link to={`/products/${product.slug}`}>
                  <Card.Img
                    variant="top"
                    src={product.img} // hoặc product.thumbnail nếu dùng slug ảnh
                    style={{  height: '250px', objectFit: 'contain', backgroundColor: '#f8f9fa'  }}
                    alt={product.name}
                  />
                </Link>

                <Card.Body className="text-center">
                  {/* Tên sản phẩm */}
                  <Card.Title>
                    <Link to={`/products/${product.slug}`} style={{ textDecoration: 'none', color: 'black' }}>
                      {product.name}
                    </Link>
                  </Card.Title>

                  {/* Giá sản phẩm */}
                  <Card.Text className="text-danger fw-bold">
                    {product.price.toLocaleString()} đ
                  </Card.Text>

                  {/* Nút mua */}
                  <Button variant="primary" size="sm" className="w-100">
                    Mua Ngay
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
