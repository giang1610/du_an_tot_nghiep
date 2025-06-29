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

  const getMinPrice = (variants = []) => {
    const now = new Date();
    const prices = variants.map((v) => {
      const start = v.sale_start_date ? new Date(v.sale_start_date) : null;
      const end = v.sale_end_date ? new Date(v.sale_end_date) : null;
      const onSale = v.sale_price && start && end && now >= start && now <= end;
      return onSale ? v.sale_price : v.price;
    });

    if (prices.length === 0) return 'Liên hệ';
    const min = Math.min(...prices);
    return `Từ ${min.toLocaleString()}₫`;
  };

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
                  className="card-img-top bg-light"
                  style={{
                    height: '240px',
                    objectFit: 'contain',
                    padding: '1rem',
                    borderTopLeftRadius: '0.5rem',
                    borderTopRightRadius: '0.5rem',
                  }}
                />
              </Link>
              <div className="card-body text-center d-flex flex-column">
                <Link to={`/products/${product.slug}`} className="text-dark text-decoration-none mb-2">
                  <h6 className="fw-bold">{product.name}</h6>
                </Link>
                <p className="text-danger fw-semibold mb-0">{getMinPrice(product.variants)}</p>
              </div>
            </div>
          </div>
        ))}
      </div>
    </div>
  );
}

export default ProductList;
