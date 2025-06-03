import React, { useEffect, useState } from 'react';
import { Container, Row, Col, Card, Button, Form, Spinner } from 'react-bootstrap';
import axios from 'axios';
import { useSearchParams } from 'react-router-dom';
import Header from '../components/Header';
import Banner from '../components/Banner';
import { Link } from 'react-router-dom';

const ProductsPage = () => {
  const [products, setProducts] = useState([]);
  const [loading, setLoading] = useState(false);
  const [categories, setCategories] = useState([]);
  const [searchParams, setSearchParams] = useSearchParams();

  // Lấy tham số từ URL
  const searchQuery = searchParams.get('search') || '';
  const categoryFilter = searchParams.get('category') || '';
  const priceFilter = searchParams.get('price') || '';

  // Lấy danh mục từ API
  useEffect(() => {
    axios.get('http://localhost:8000/api/categories')
      .then(res => setCategories(res.data.data))
      .catch(err => console.error(err));
  }, []);

  // Lấy sản phẩm với params lọc
  useEffect(() => {
    setLoading(true);
    axios.get('http://localhost:8000/api/products', {
      params: {
        search: searchQuery,
        category: categoryFilter,
        price: priceFilter
      }
    })
      .then(res => {
        setProducts(res.data.data);
        setLoading(false);
      })
      .catch(err => {
        console.error(err);
        setLoading(false);
      });
  }, [searchQuery, categoryFilter, priceFilter]);

  // Xử lý lọc danh mục
  const handleCategoryChange = (e) => {
    const value = e.target.value;
    setSearchParams(prev => {
      if (value) {
        prev.set('category', value);
      } else {
        prev.delete('category');
      }
      return prev;
    });
  };

  // Xử lý lọc giá
  const handlePriceChange = (e) => {
    const value = e.target.value;
    setSearchParams(prev => {
      if (value) {
        prev.set('price', value);
      } else {
        prev.delete('price');
      }
      return prev;
    });
  };

  return (
    <>
      <Header />
      <Banner />
      <Container className="my-5">
        <h2 className="mb-4 text-center">Tất cả sản phẩm</h2>
        <Row>
          {/* Sidebar lọc */}
          <Col md={3}>
            <h5>Danh mục sản phẩm</h5>
            <Form className="mb-3">
              <Form.Select value={categoryFilter} onChange={handleCategoryChange}>
                <option value="">-- Tất cả danh mục --</option>
                {categories.map(cat => (
                  <option key={cat.id} value={cat.id}>{cat.name}</option>
                ))}
              </Form.Select>
            </Form>

            <h5>Khoảng giá</h5>
            <Form>
              <Form.Select value={priceFilter} onChange={handlePriceChange}>
                <option value="">-- Tất cả giá --</option>
                <option value="0-1000000">Dưới 1,000,000 đ</option>
                <option value="1000000-3000000">1,000,000 đ - 3,000,000 đ</option>
                <option value="3000000-1000000000">Trên 3,000,000 đ</option>
              </Form.Select>
            </Form>
          </Col>

          {/* Danh sách sản phẩm */}
          <Col md={9}>
            {loading ? (
              <div className="text-center"><Spinner animation="border" /></div>
            ) : (
              <Row>
                {products.length > 0 ? (
                  products.map(product => (
                    <Col key={product.id} sm={6} md={4} lg={3} className="mb-4">
                      <Card className="h-100 shadow-sm">
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
                          <Card.Text className="text-danger fw-bold">{product.price.toLocaleString()} đ</Card.Text>
                          <Button variant="primary" size="sm" className="w-100">Mua Ngay</Button>
                        </Card.Body>

                      </Card>
                    </Col>
                  ))
                ) : (
                  <p className="text-center">Không có sản phẩm phù hợp.</p>
                )}
              </Row>
            )}
          </Col>
        </Row>
      </Container>
    </>
  );
};

export default ProductsPage;
