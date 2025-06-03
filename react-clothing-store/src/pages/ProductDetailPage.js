import React, { useEffect, useState } from "react";
import { useParams } from "react-router-dom";
import API from "../services/api";

const ProductDetailPage = () => {
  const { id } = useParams();
  const [product, setProduct] = useState(null);
  const [selectedSize, setSelectedSize] = useState("");
  const [selectedColor, setSelectedColor] = useState("");
  const [quantity, setQuantity] = useState(1);

  useEffect(() => {
    API.get(`/products/${id}`)
      .then((res) => {
        setProduct(res.data);
        if (res.data.sizes && res.data.sizes.length > 0) {
          setSelectedSize(res.data.sizes[0]);
        }
        if (res.data.colors && res.data.colors.length > 0) {
          setSelectedColor(res.data.colors[0]);
        }
      })
      .catch((err) => console.error(err));
  }, [id]);

  const handleAddToCart = () => {
    const cart = JSON.parse(localStorage.getItem("cart")) || [];
    const item = {
      productId: product.id,
      name: product.name,
      price: product.price,
      size: selectedSize,
      color: selectedColor,
      quantity,
      image: product.image_url,
    };

    const index = cart.findIndex(
      (c) =>
        c.productId === item.productId &&
        c.size === item.size &&
        c.color === item.color
    );
    if (index >= 0) {
      cart[index].quantity += quantity;
    } else {
      cart.push(item);
    }

    localStorage.setItem("cart", JSON.stringify(cart));
    alert("Đã thêm vào giỏ hàng!");
  };

  if (!product) return <div className="container mt-4">Đang tải...</div>;

  return (
    <div className="container mt-4">
      <div className="row">
        {/* Ảnh lớn */}
        <div className="col-md-6">
          <img
            src={product.image_url}
            alt={product.name}
            className="img-fluid rounded"
          />
        </div>

        {/* Thông tin */}
        <div className="col-md-6">
          <h2>{product.name}</h2>
          <h4 className="text-danger">{product.price.toLocaleString()}₫</h4>
          <p>{product.description}</p>

          {product.sizes && product.sizes.length > 0 && (
            <>
              <label className="form-label">Size:</label>
              <select
                className="form-select mb-3"
                value={selectedSize}
                onChange={(e) => setSelectedSize(e.target.value)}
              >
                {product.sizes.map((size) => (
                  <option key={size} value={size}>
                    {size}
                  </option>
                ))}
              </select>
            </>
          )}

          {product.colors && product.colors.length > 0 && (
            <>
              <label className="form-label">Màu:</label>
              <select
                className="form-select mb-3"
                value={selectedColor}
                onChange={(e) => setSelectedColor(e.target.value)}
              >
                {product.colors.map((color) => (
                  <option key={color} value={color}>
                    {color}
                  </option>
                ))}
              </select>
            </>
          )}

          <label className="form-label">Số lượng:</label>
          <input
            type="number"
            min="1"
            className="form-control mb-3"
            value={quantity}
            onChange={(e) => setQuantity(Math.max(1, Number(e.target.value)))}
          />

          <button className="btn btn-primary" onClick={handleAddToCart}>
            Thêm vào giỏ hàng
          </button>
        </div>
      </div>
    </div>
  );
};

export default ProductDetailPage;
