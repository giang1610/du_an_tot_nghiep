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

      // Lấy mảng data an toàn
      let result = Array.isArray(res.data.data) ? res.data.data : [];

      // --- Client-side filtering (nếu backend chưa filter hoặc bạn muốn chắc chắn) ---
      // Lưu ý: các field id có thể là string, convert khi so sánh nếu cần
      if (filters.category) {
        // nếu category id lưu là number trong product, chuyển filters.category sang number
        const cat = isNaN(Number(filters.category)) ? filters.category : Number(filters.category);
        result = result.filter(p => {
          // tùy cấu trúc product: thử các trường thường gặp
          return p.category_id === cat || p.category === cat || p.category?.id === cat;
        });
      }

      if (filters.size) {
        const size = isNaN(Number(filters.size)) ? filters.size : Number(filters.size);
        result = result.filter(p => {
          return p.size_id === size || p.size === size || p.sizes?.some(s => s.id === size);
        });
      }

      if (filters.price) {
        // kỳ vọng filters.price dạng "min-max" như "0-200000"
        const [minStr, maxStr] = String(filters.price).split('-');
        const min = Number(minStr ?? 0);
        const max = Number(maxStr ?? Infinity);
        result = result.filter(p => {
          const price = Number(p.price_original ?? p.price ?? 0);
          return price >= min && price <= max;
        });
      }

      if (filters.search && filters.search.trim() !== '') {
        const q = filters.search.trim().toLowerCase();
        result = result.filter(p => {
          // search vào tên, mô tả, mã sản phẩm nếu có
          const name = String(p.name ?? p.title ?? '').toLowerCase();
          const desc = String(p.description ?? '').toLowerCase();
          const sku = String(p.sku ?? '').toLowerCase();
          return name.includes(q) || desc.includes(q) || sku.includes(q);
        });
      }

      // --- Sorting (client-side) ---
      switch (filters.sort) {
        case 'latest':
          result.sort((a, b) => new Date(b.created_at) - new Date(a.created_at));
          break;
        case 'price_asc':
          result.sort((a, b) => (Number(a.price_original ?? a.price ?? 0)) - (Number(b.price_original ?? b.price ?? 0)));
          break;
        case 'price_desc':
          result.sort((a, b) => (Number(b.price_original ?? b.price ?? 0)) - (Number(a.price_original ?? a.price ?? 0)));
          break;
        default:
          break;
      }

      // --- Cập nhật products (ghi đè khi page === 1, nối khi load more) ---
      setProducts(prev => (page === 1 ? result : [...prev, ...result]));

      // --- Cập nhật total (ưu tiên API trả tổng) ---
      const apiTotal = res.data.total;
      if (typeof apiTotal === 'number') {
        setTotal(apiTotal);
      } else {
        // fallback: nếu API không trả total, tính tạm từ kết quả hiện tại
        if (page === 1) {
          setTotal(result.length);
        } else {
          setTotal(prevTotal => prevTotal + result.length);
        }
      }

      // hasMore dựa trên số item trả về so với LIMIT
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

  // ✅ Nếu người dùng chưa chọn filter/search nào thì anyFilterSelected = false
  const anyFilterSelected = Boolean(
    filters.category ||
    filters.price ||
    filters.size ||
    filters.sort ||
    (filters.search && filters.search.trim() !== '')
  );

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
              <Button type="submit" variant="primary">
                🔍 Lọc
              </Button>
              <Button variant="outline-primary" onClick={handleResetFilters}>
                🔄 Đặt lại
              </Button>
            </div>
          </Form>
        </Col>

        {/* DANH SÁCH SẢN PHẨM */}
        <Col md={9}>
          <div className="d-flex justify-content-between align-items-center mb-3">
            <div>
              {anyFilterSelected ? (
                <span><strong>{total}</strong> sản phẩm được tìm thấy</span>
              ) : (
                <span className="text-muted">Tất cả sản phẩm</span>
              )}
            </div>

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
          ) : products.length === 0 && anyFilterSelected ? (
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