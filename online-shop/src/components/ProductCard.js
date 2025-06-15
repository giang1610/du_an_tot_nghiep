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
    <div className="col-6 col-md-3 mb-4">
      <div className="card h-100 border-0">
        <Link to={`/products/${product.slug}`}>
          <img
            src={product.thumbnail}
            alt={product.name}
            className="card-img-top rounded"
          />
        </Link>
        <div className="card-body text-center">
        <Link to={`/products/${product.slug}`}>
          <h6 className="card-title fw-bold">{product.name}</h6>
          <p className="text-danger fw-semibold">
            {product.price?.toLocaleString()}₫
          </p>
        </Link>
        <button className="btn btn-primary btn-sm" onClick={handleBuyNow}>
            Mua ngay
          </button>
        </div>
      </div>
    </div>
  );
};

export default ProductCard;
