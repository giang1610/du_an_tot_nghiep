import React from "react";
import { Link, useNavigate } from "react-router-dom";
import { useAuth } from "../hooks/useAuth";

const ProductCard = ({ product }) => {
  const navigate = useNavigate();
  const { isLoggedIn } = useAuth();

  if (!product || !product.id) return null;

  const handleBuyNow = () => {
    if (!isLoggedIn) {
      alert("Bạn cần đăng nhập để mua sản phẩm!");
      navigate("/login", { state: { from: `/checkout/${product.id}` } });
    } else {
      navigate(`/checkout/${product.id}`);
    }
  };

  return (
    <div className="col-6 col-md-4 col-lg-3 mb-4">
      <div className="card h-100 border shadow-sm rounded hover-shadow transition">
        <Link to={`/products/${product.slug}`} className="text-decoration-none">
          <img
            src={product.thumbnail}
            alt={product.name}
            className="card-img-top"
            style={{ height: '240px', objectFit: 'cover' }}
          />
        </Link>

        <div className="card-body d-flex flex-column text-center p-3">
          <Link to={`/products/${product.slug}`} className="text-decoration-none text-dark mb-2">
            <h6 className="card-title fw-bold">{product.name}</h6>
          </Link>
          <p className="text-danger fw-semibold mb-3">
            {product.price?.toLocaleString()}₫
          </p>

          <button className="btn btn-primary btn-sm mt-auto" onClick={handleBuyNow}>
            Mua ngay
          </button>
        </div>
      </div>
    </div>
  );
};

export default ProductCard;
