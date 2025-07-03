import { useCallback, useEffect, useState } from 'react';
import { useLocation } from 'react-router-dom';
import axios from 'axios';
import {
  Container, Row, Col, Spinner, Alert, Form, Button
} from 'react-bootstrap';
import ProductCard from '../components/ProductCard';

const LIMIT = 9;

export default function AllProductsPage() {
  const [products, setProducts] = useState([]);
  const [total, setTotal] = useState(0);
  const [loading, setLoading] = useState(false);
  const [hasMore, setHasMore] = useState(true);
  const [page, setPage] = useState(1);

  const [categories, setCategories] = useState([]);
  const [sizes, setSizes] = useState([]);

  const [filters, setFilters] = useState({
    category: '',
    price: '',
    size: '',
    sort: '',
    search: ''
  });

  const location = useLocation();

  useEffect(() => {
    const params = new URLSearchParams(location.search);
    const search = params.get('search') || '';
    setFilters(prev => ({ ...prev, search }));
    setPage(1);
  }, [location.search]);

  useEffect(() => {
    axios.get(`${process.env.REACT_APP_API_URL}/categories`)
      .then(res => setCategories(res.data.data))
      .catch(err => console.error("Lỗi tải danh mục:", err));

    axios.get(`${process.env.REACT_APP_API_URL}/sizes`)
      .then(res => setSizes(res.data.data))
      .catch(err => console.error("Lỗi tải kích cỡ:", err));
  }, []);

  const fetchProducts = useCallback(async () => {
    setLoading(true);
    try {
      const res = await axios.get(`${process.env.REACT_APP_API_URL}/products`, {
        params: { ...filters, page, limit: LIMIT }
      });

      let result = res.data.data;

   
      switch (filters.sort) {
        case 'latest':
          result.sort((a, b) => new Date(b.created_at) - new Date(a.created_at));
          break;
        case 'price_asc':
          result.sort((a, b) => a.price_original - b.price_original);
          break;
        case 'price_desc':
          result.sort((a, b) => b.price_original - a.price_original);
          break;
        default:
          break;
      }

      setProducts(prev =>
        page === 1 ? result : [...prev, ...result]
      );
      setTotal(res.data.total || 0);
      setHasMore(result.length === LIMIT);
    } catch (err) {
      console.error("Lỗi khi tải sản phẩm:", err);
    } finally {
      setLoading(false);
    }
  }, [filters, page]);

  useEffect(() => {
    fetchProducts();
  }, [fetchProducts]);

  const handleFilterChange = (field, value) => {
    setFilters(prev => ({ ...prev, [field]: value }));
    setPage(1);
  };

  const handleFilterSubmit = (e) => {
    e.preventDefault();
    setPage(1);
    fetchProducts();
  };

  const handleResetFilters = () => {
    setFilters({
      category: '',
      price: '',
      size: '',
      sort: '',
      search: filters.search // giữ nguyên search từ URL
    });
    setPage(1);
  };

  const handleLoadMore = () => {
    setPage(prev => prev + 1);
  };

  return (
    <Container className="py-5">
      <h2 className="mb-4">Tất cả sản phẩm</h2>
      <Row>
        {/* BỘ LỌC BÊN TRÁI */}
        <Col md={3}>
          <Form onSubmit={handleFilterSubmit}>
            {/* Danh mục */}
            <SelectBox
              label="Danh mục"
              value={filters.category}
              options={categories}
              onChange={val => handleFilterChange('category', val)}
            />

            {/* Mức giá */}
            <SelectBox
              label="Mức giá"
              value={filters.price}
              options={[
                { id: '0-200000', name: 'Dưới 200k' },
                { id: '200000-500000', name: '200k - 500k' },
                { id: '500000-1000000', name: '500k - 1 triệu' },
              ]}
              onChange={val => handleFilterChange('price', val)}
            />

            {/* Size */}
            <SelectBox
              label="Size"
              value={filters.size}
              options={sizes}
              onChange={val => handleFilterChange('size', val)}
            />

            <div className="d-grid gap-2">
              <Button type="submit" variant="dark">Lọc</Button>
              <Button variant="outline-secondary" onClick={handleResetFilters}>
                Đặt lại bộ lọc
              </Button>
            </div>
          </Form>
        </Col>

        {/* DANH SÁCH SẢN PHẨM */}
        <Col md={9}>
          <div className="d-flex justify-content-between align-items-center mb-3">
            <div><strong>{total}</strong> sản phẩm được tìm thấy</div>
            <Form.Select
              style={{ width: '200px' }}
              value={filters.sort}
              onChange={(e) => handleFilterChange('sort', e.target.value)}
            >
              <option value="">Sắp xếp</option>
              <option value="latest">Mới nhất</option>
              <option value="price_asc">Giá tăng dần</option>
              <option value="price_desc">Giá giảm dần</option>
            </Form.Select>
          </div>

          {loading && page === 1 ? (
            <div className="text-center py-5">
              <Spinner animation="border" />
            </div>
          ) : products.length === 0 ? (
            <Alert variant="warning">Không tìm thấy sản phẩm phù hợp</Alert>
          ) : (
            <>
              <Row>
                {products.map(product => (
                  <Col key={product.id} md={4} className="mb-4">
                    <ProductCard product={product} />
                  </Col>
                ))}
              </Row>

              {loading && page > 1 && (
                <div className="text-center py-4">
                  <Spinner animation="border" />
                </div>
              )}

              {hasMore && !loading && (
                <div className="text-center mt-4">
                  <Button onClick={handleLoadMore} variant="outline-dark">Xem thêm</Button>
                </div>
              )}
            </>
          )}
        </Col>
      </Row>
    </Container>
  );
}

// ✅ Component tái sử dụng cho SelectBox
function SelectBox({ label, value, options = [], onChange }) {
  return (
    <Form.Group className="mb-3">
      <Form.Label>{label}</Form.Label>
      <Form.Select value={value} onChange={e => onChange(e.target.value)}>
        <option value="">Tất cả</option>
        {Array.isArray(options) && options.map(opt => (
          <option key={opt.id} value={opt.id}>{opt.name}</option>
        ))}
      </Form.Select>
    </Form.Group>
  );
}
