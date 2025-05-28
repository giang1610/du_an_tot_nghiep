import React from 'react';
import ProductList from './ProductList';
import Header from '../components/Header';
import Banner from '../components/Banner';
import Footer from '../components/Footer'
function HomePage() {
  return (
    <>
      <Header />
      <Banner />
      <div className="container mt-4">
        <h2>Sản phẩm mới nhất</h2>
        <ProductList />
      </div>
      <Footer />
    </>
  );
}

export default HomePage;
