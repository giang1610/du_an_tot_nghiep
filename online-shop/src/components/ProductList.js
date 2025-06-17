// src/components/ProductList.js
import React, { useEffect } from 'react';
import { useSelector, useDispatch } from 'react-redux';
import { Link } from 'react-router-dom';
import { getProducts } from '../store/productSlice';

function ProductList() {
  const dispatch = useDispatch();
  const { products, status } = useSelector((state) => state.products);

  useEffect(() => {
    dispatch(getProducts());
  }, [dispatch]);

  if (status === 'loading') return <div>Loading products...</div>;

  return (
    <div>
      <h2>Products</h2>
      <div className="product-grid">
        {products.map((product) => (
          <div key={product.id} className="product-card">
            <Link to={`/products/${product.id}`}>
              <img 
                src={`http://localhost:8000/storage/${product.thumbnail}`} 
                alt={product.name}
              />
              <h3>{product.name}</h3>
              <p>From ${Math.min(...product.variants.map(v => v.current_price))}</p>
            </Link>
          </div>
        ))}
      </div>
    </div>
  );
}

export default ProductList;