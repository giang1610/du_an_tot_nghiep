import React from 'react';

export const ProductCard = ({ product }) => {
  return (
    <div className="card h-100">
      <img
        src={product.img}
        alt={product.name}
        className="card-img-top"
        style={{ height: "200px", objectFit: "cover" }}
      />
      <div className="card-body">
        <h6 className="card-title">{product.name}</h6>
        <p className="text-danger fw-bold">{product.price.toLocaleString()} đ</p>
      </div>
    </div>
  );
};
