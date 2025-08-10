import { useEffect, useState } from 'react';
import HeroBanner from '../components/HeroBanner';
import ServiceBar from '../components/ServiceBar';
import ProductSection from '../components/ProductSection';
import SpecialOffers from '../components/SpecialOffers';

export default function HomePage() {
  const [products, setProducts] = useState([]);

  useEffect(() => {
    fetch('http://localhost:8000/api/products')
      .then(res => res.json())
      .then(data => setProducts(data.data || []));
  }, []);

  return (
    <>
      <HeroBanner />
      <ServiceBar />
      <ProductSection title="Sản phẩm mới" products={products.slice(0, 8)} />
      {/* <CategoryBanner /> */}
      <SpecialOffers />
      <ProductSection title="Bán chạy nhất" products={products.slice(0, 8)} />
    </>
  );
}