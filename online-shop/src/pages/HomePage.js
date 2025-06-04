import React, { useEffect, useState } from "react";
import API from "../services/api";
import Banner from "../components/Banner";
import ProductCard from "../components/ProductCard";

const HomePage = () => {
  const [latestProducts, setLatestProducts] = useState([]);

  useEffect(() => {
    API.get("/products")
      .then((res) => {
        console.log(res.data);
        const sorted = res.data.data
          .sort((a, b) => new Date(b.createdAt) - new Date(a.createdAt))
          .slice(0, 5);
        setLatestProducts(sorted);
      })
      .catch(console.error);
  }, []);

  return (
    <>
      <Banner />
      <div className="container my-4">
        <h2>Sản phẩm mới nhất</h2>
        <div className="row">
          {latestProducts.length > 0 ? (
            latestProducts.map((product) => (
              <ProductCard key={product.id} product={product} />
            ))
          ) : (
            <p>Đang tải sản phẩm...</p>
          )}
        </div>
      </div>
    </>
  );
};

export default HomePage;
