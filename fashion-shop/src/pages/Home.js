import { useEffect, useState, useCallback } from 'react';
import { Container } from 'react-bootstrap';
import { Link } from 'react-router-dom';
import ProductCard from '../components/ProductCard';
import { Swiper, SwiperSlide } from 'swiper/react';
import 'swiper/css';
import HeroBanner from '../components/HeroBanner';
import ServiceBar from '../components/ServiceBar';
import { listenToProductChanged } from '../realtime/productRealtime';

export default function HomePage() {
  const [data, setData] = useState([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState('');

  const fetchProducts = useCallback(async () => {
    try {
      setLoading(true);
      const res = await fetch(`${process.env.REACT_APP_API_URL}/products`);
      const json = await res.json();

      if (!json.success || !Array.isArray(json.data)) {
        throw new Error('Dữ liệu sản phẩm không hợp lệ');
      }

      setData(json.data); 
    } catch (err) {
      console.error('❌ Lỗi khi fetch sản phẩm:', err);
      setError(err.message || 'Lỗi khi tải sản phẩm');
    } finally {
      setLoading(false);
    }
  }, []);

  useEffect(() => {
    fetchProducts();
  }, [fetchProducts]);

  useEffect(() => {
    const unsubscribe = listenToProductChanged(() => {
      console.log(' Laravel báo thay đổi → gọi lại API');
      fetchProducts();
    });

    return () => {
      if (unsubscribe) unsubscribe();
    };
  }, [fetchProducts]);

  const latestProducts = [...data]
    .sort((a, b) => new Date(b.created_at) - new Date(a.created_at))
    .slice(0, 10);

  const saleProducts = data.filter(p =>
    p.variants?.some(v => v.sale_price != null && v.sale_price < v.price)
  );

  const renderSection = (title, products) => (
    <>
      <div className="d-flex justify-content-between align-items-center mt-5 mb-3">
        <h4>{title}</h4>
        <Link to="/products" className="text-decoration-none fw-semibold text-dark">
          Xem tất cả &raquo;
        </Link>
      </div>

      {products.length === 0 ? (
        <p>Không có sản phẩm nào phù hợp.</p>
      ) : (
        <Swiper
          spaceBetween={20}
          slidesPerView={1}
          breakpoints={{
            576: { slidesPerView: 2 },
            768: { slidesPerView: 3 },
            992: { slidesPerView: 4 },
          }}
        >
          {products.map(product => (
            <SwiperSlide key={product.id}>
              <ProductCard product={product} />
            </SwiperSlide>
          ))}
        </Swiper>
      )}
    </>
  );

  if (loading) {
    return (
      <>
        <HeroBanner />
        <ServiceBar />
        <Container className="py-5 text-center">
          <div className="spinner-border" role="status" />
          <span className="visually-hidden">Đang tải...</span>
        </Container>
      </>
    );
  }

  if (error) {
    return (
      <>
        <HeroBanner />
        <ServiceBar />
        <Container className="py-5 text-center text-danger">
          <p>{error}</p>
        </Container>
      </>
    );
  }

  return (
    <>
      <HeroBanner />
      <ServiceBar />
      <Container className="py-5">
        {renderSection('💥 Sản phẩm khuyến mãi', saleProducts)}
        {renderSection('🆕 Sản phẩm mới nhất', latestProducts)}
      </Container>
    </>
  );
}
