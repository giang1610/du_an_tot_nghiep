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

  if (status === 'loading') {
    return (
      <div className="text-center py-5">
        <div className="spinner-border text-primary" role="status" />
        <p className="mt-3">Đang tải sản phẩm...</p>
      </div>
    );
  }

  return (
    <div className="container py-5">
      <h2 className="mb-4 text-center fw-bold">Sản phẩm</h2>
      <div className="row">
        {products.map((product) => (
          <div key={product.id} className="col-6 col-md-4 col-lg-3 mb-4">
            <div className="card h-100 shadow-sm border-0">
              <Link to={`/products/${product.slug}`} className="text-decoration-none">
                <img
                  src={`http://localhost:8000/storage/${product.thumbnail}`}
                  alt={product.name}
                  className="card-img-top"
                  style={{ height: '240px', objectFit: 'cover' }}
                />
              </Link>
              <div className="card-body text-center d-flex flex-column">
                <Link to={`/products/${product.slug}`} className="text-dark text-decoration-none mb-2">
                  <h6 className="fw-bold">{product.name}</h6>
                </Link>
                <p className="text-danger fw-semibold mb-0">
                  Từ {Math.min(...product.variants.map(v => v.current_price)).toLocaleString()}₫
                </p>
              </div>
            </div>
          </div>
        ))}
      </div>
    </div>
  );
}

export default ProductList;
