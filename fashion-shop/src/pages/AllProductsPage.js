import { useCallback, useEffect, useState } from 'react';
import { useLocation } from 'react-router-dom';
import axios from 'axios';
import {
  Container, Row, Col, Spinner, Alert, Form, Button, Card
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
          const price = Number(p.price_products ?? p.price ?? 0);
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
            result.sort((a, b) => Number(a.price ?? 0) - Number(b.price ?? 0));
            break;
        case 'price_desc':
            result.sort((a, b) => Number(b.price ?? 0) - Number(a.price ?? 0));
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
    <>
      <style>{`
        .products-bg {
          background: linear-gradient(120deg, #f0f4ff 0%, #f8fafc 100%);
          min-height: 100vh;
        }
        .products-sidebar {
          background: #fff;
          border-radius: 18px;
          box-shadow: 0 4px 24px rgba(59,130,246,0.07);
          padding: 2rem 1.5rem;
          margin-bottom: 2rem;
        }
        .products-sidebar .form-label {
          font-weight: 600;
          color: #2563eb;
        }
        .products-sidebar .form-select {
          border-radius: 8px;
        }
        .products-header {
          background: #fff;
          border-radius: 18px;
          box-shadow: 0 2px 12px rgba(59,130,246,0.06);
          padding: 1.5rem 2rem;
          margin-bottom: 2rem;
          display: flex;
          flex-wrap: wrap;
          align-items: center;
          justify-content: space-between;
        }
        .products-header h2 {
          font-weight: 700;
          color: #2563eb;
          margin-bottom: 0;
        }
        .products-sort {
          min-width: 200px;
        }
        .products-list {
          min-height: 400px;
        }
        .products-loadmore {
          margin-top: 2rem;
        }
        @media (max-width: 900px) {
          .products-sidebar {
            padding: 1rem 0.5rem;
          }
          .products-header {
            padding: 1rem 0.5rem;
          }
        }
      `}</style>
      <div className="products-bg py-5">
        <Container>
          <div className="products-header mb-4">
            <h2 className="mb-0">🛍️ Tất cả sản phẩm</h2>
            <div className="d-flex align-items-center gap-3">
              <span className="fw-semibold text-secondary">
                {anyFilterSelected
                  ? <>{total} sản phẩm được tìm thấy</>
                  : <>Tất cả sản phẩm</>
                }
              </span>
              <Form.Select
                className="products-sort"
                value={filters.sort}
                onChange={(e) => handleFilterChange('sort', e.target.value)}
              >
                <option value="">Sắp xếp</option>
                <option value="latest">Mới nhất</option>
                <option value="price_asc">Giá tăng dần</option>
                <option value="price_desc">Giá giảm dần</option>
              </Form.Select>
            </div>
          </div>
          <Row>
            {/* Sidebar bộ lọc */}
            <Col md={3}>
              <Card className="products-sidebar">
                <Card.Body>
                  <Form onSubmit={handleFilterSubmit}>
                    <SelectBox
                      label="Danh mục"
                      value={filters.category}
                      options={categories}
                      onChange={val => handleFilterChange('category', val)}
                    />
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
                    <div className="d-grid gap-2 mt-3">
                      <Button variant="outline-primary" onClick={handleResetFilters}>
                        🔄 Đặt lại bộ lọc
                      </Button>
                    </div>
                  </Form>
                </Card.Body>
              </Card>
            </Col>
            {/* Danh sách sản phẩm */}
            <Col md={9}>
              <div className="products-list">
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
                      <div className="products-loadmore text-center">
                        <Button onClick={handleLoadMore} variant="primary" size="lg">
                          Xem thêm sản phẩm
                        </Button>
                      </div>
                    )}
                  </>
                )}
              </div>
            </Col>
          </Row>
        </Container>
      </div>
    </>
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