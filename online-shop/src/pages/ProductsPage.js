import React, { useEffect, useState } from 'react';
import { Container, Row, Col, Card, Button, Form, Spinner } from 'react-bootstrap';
import axios from 'axios';
import { useSearchParams, Link } from 'react-router-dom';
import Header from '../components/Header';
import Banner from '../components/Banner';

const ProductsPage = () => {
  const [products, setProducts] = useState([]);
  const [categories, setCategories] = useState([]);
  const [sizes, setSizes] = useState([]);
  const [loading, setLoading] = useState(false);
  const [searchParams, setSearchParams] = useSearchParams();

  const searchQuery = searchParams.get('search') || '';
  const categoryFilter = searchParams.get('category') || '';
  const sizeFilter = searchParams.get('size') || '';
  const priceFilter = searchParams.get('price') || '';

  // Lấy dữ liệu filter
  useEffect(() => {
    const fetchFilters = async () => {
      try {
        const [catRes, sizeRes] = await Promise.all([
          axios.get(`${process.env.REACT_APP_API_URI}/categories`),
          axios.get(`${process.env.REACT_APP_API_URI}/sizes`)
        ]);
        setCategories(catRes.data.data || []);
        setSizes(sizeRes.data.data || []);
      } catch (err) {
        console.error('Lỗi khi load filters:', err);
      }
    };
    fetchFilters();
  }, []);

  // Lấy sản phẩm theo filter
  useEffect(() => {
    const fetchProducts = async () => {
      setLoading(true);
      try {
        const res = await axios.get(`${process.env.REACT_APP_API_URI}/products`, {
          params: { search: searchQuery, category: categoryFilter, price: priceFilter, size: sizeFilter }
        });
        setProducts(res.data.data || []);
      } catch (err) {
        console.error('Lỗi khi load sản phẩm:', err);
      } finally {
        setLoading(false);
      }
    };
    fetchProducts();
  }, [searchQuery, categoryFilter, priceFilter, sizeFilter]);

  const handleFilterChange = (key, value) => {
    setSearchParams(prev => {
      if (value) prev.set(key, value);
      else prev.delete(key);
      return prev;
    });
  };

  const renderSelect = (label, key, value, options) => (
    <>
      <h5>{label}</h5>
      <Form.Select
        className="mb-3"
        value={value}
        onChange={(e) => handleFilterChange(key, e.target.value)}
      >
        <option value="">-- Tất cả {label.toLowerCase()} --</option>
        {options.map(opt => (
          <option key={opt.id} value={opt.id}>{opt.name}</option>
        ))}
      </Form.Select>
    </>
  );

  return (
    <>
      <Header />
      <Banner />
      <Container className="my-5">
        <h2 className="mb-4 text-center">🛍️ Tất cả sản phẩm</h2>
        <Row>
          {/* Bộ lọc */}
          <Col md={3}>
            {renderSelect('Danh mục', 'category', categoryFilter, categories)}
            {renderSelect('Kích thước', 'size', sizeFilter, sizes)}

            <h5>Khoảng giá</h5>
            <Form.Select
              className="mb-3"
              value={priceFilter}
              onChange={(e) => handleFilterChange('price', e.target.value)}
            >
              <option value="">-- Tất cả giá --</option>
              <option value="0-1000000">Dưới 1,000,000 đ</option>
              <option value="1000000-3000000">1,000,000 đ - 3,000,000 đ</option>
              <option value="3000000-1000000000">Trên 3,000,000 đ</option>
            </Form.Select>
          </Col>

          {/* Danh sách sản phẩm */}
          <Col md={9}>
            {loading ? (
              <div className="text-center py-5">
                <Spinner animation="border" />
                <div className="mt-2">Đang tải sản phẩm...</div>
              </div>
            ) : (
              <Row>
                {products.length > 0 ? (
                  products.map(product => (
                    <Col key={product.id} sm={6} md={4} lg={3} className="mb-4">
                      <Card className="h-100 shadow-sm border-0">
                        <Link to={`/products/${product.slug}`}>
                          <Card.Img
                            variant="top"
                            src={product.img}
                            style={{ height: '250px', objectFit: 'contain' }}
                            alt={product.name}
                          />
                        </Link>
                        <Card.Body className="text-center">
                          <Card.Title className="text-truncate">
                            <Link to={`/products/${product.slug}`} className="text-decoration-none text-dark">
                              {product.name}
                            </Link>
                          </Card.Title>
                          <Card.Text className="text-danger fw-bold">
                            {product.price_products?.toLocaleString()} đ
                          </Card.Text>
                          <Button
                            as={Link}
                            to={`/products/${product.slug}`}
                            variant="primary"
                            size="sm"
                            className="w-100"
                          >
                            Xem chi tiết
                          </Button>
                        </Card.Body>
                      </Card>
                    </Col>
                  ))
                ) : (
                  <p className="text-center mt-5">Không tìm thấy sản phẩm nào phù hợp.</p>
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
