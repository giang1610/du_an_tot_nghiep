import React from "react";
import { Link } from "react-router-dom";

const ProductCard = ({ product }) => (
  <div className="col-6 col-md-3 mb-4">
    <div className="card h-100 border-0">
      <Link to={`/products/${product.id}`}>
        <img
          src={product.image_url}
          alt={product.name}
          className="card-img-top rounded"
        />
      </Link>
      <div className="card-body text-center">
        <h6 className="card-title fw-bold">{product.name}</h6>
        <p className="text-danger fw-semibold">
          {product.price.toLocaleString()}₫
        </p>
      </div>
    </div>
  </div>
);

export default ProductCard;
