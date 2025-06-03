import React, { useEffect, useState } from "react";
import API from "../services/api";
import ProductCard from "./ProductCard";

const ProductGrid = () => {
  const [products, setProducts] = useState([]);

  useEffect(() => {
    API.get("/products")
      .then((res) => setProducts(res.data))
      .catch(console.error);
  }, []);

  return (
    <div className="container my-5">
      <h3 className="mb-4 text-uppercase">Sản phẩm nổi bật</h3>
      <div className="row">
        {products.map((product) => (
          <ProductCard key={product.id} product={product} />
        ))}
      </div>
    </div>
  );
};

export default ProductGrid;
