import { useState } from 'react';
import { Container, Row, Col, Form, Button } from 'react-bootstrap';
import ProductCard from './ProductCard';
import productsData from '../data/products';

export default function ProductList() {
  const [keyword, setKeyword] = useState('');
  const [priceFilter, setPriceFilter] = useState('');

  const handleSearch = () => {
    let filtered = [...productsData];
    if (keyword) {
      filtered = filtered.filter(p =>
        p.name.toLowerCase().includes(keyword.toLowerCase())
      );
    }
    if (priceFilter) {
      if (priceFilter === 'low') {
        filtered = filtered.filter(p => p.variants[0]?.sale_price ?? p.variants[0]?.price < 300000);
      } else if (priceFilter === 'mid') {
        filtered = filtered.filter(p => {
          const price = p.variants[0]?.sale_price ?? p.variants[0]?.price;
          return price >= 300000 && price <= 500000;
        });
      } else if (priceFilter === 'high') {
        filtered = filtered.filter(p => (p.variants[0]?.sale_price ?? p.variants[0]?.price) > 500000);
      }
    }
    return filtered;
  };

  const filteredProducts = handleSearch();

  return (
    <Container className="py-5">
      <h2 className="text-center mb-4 fw-bold fs-3 text-uppercase border-bottom pb-3">
        Sản phẩm nổi bật
      </h2>

      <Row className="mb-4 justify-content-center g-2">
        <Col xs={12} md={5}>
          <Form.Control
            type="text"
            placeholder="🔍 Tìm kiếm theo tên sản phẩm..."
            value={keyword}
            onChange={(e) => setKeyword(e.target.value)}
          />
        </Col>
        <Col xs={12} md={4}>
          <Form.Select
            value={priceFilter}
            onChange={(e) => setPriceFilter(e.target.value)}
          >
            <option value="">🪙 Lọc theo giá</option>
            <option value="low">⬇️ Dưới 300.000₫</option>
            <option value="mid">💰 300.000₫ - 500.000₫</option>
            <option value="high">⬆️ Trên 500.000₫</option>
          </Form.Select>
        </Col>
        <Col xs={12} md={2}>
          <Button
            variant="outline-secondary"
            className="w-100"
            onClick={() => {
              setKeyword('');
              setPriceFilter('');
            }}
          >
            🧹 Xóa lọc
          </Button>
        </Col>
      </Row>

      <Row className="g-4">
        {filteredProducts.length > 0 ? (
          filteredProducts.map(product => (
            <Col key={product.id} xs={12} sm={6} md={4} lg={3}>
              <ProductCard product={product} />
            </Col>
          ))
        ) : (
          <Col>
            <p className="text-center text-muted">😢 Không tìm thấy sản phẩm phù hợp.</p>
          </Col>
        )}
      </Row>
    </Container>
  );
}
