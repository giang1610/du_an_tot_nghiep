import React, { useEffect, useState } from "react";
import { useParams } from "react-router-dom";
import { useCart } from "../contexts/CartContext";
import axios from "axios";

function ProductDetailPage() {
  const { id } = useParams();
  const [product, setProduct] = useState(null);
  const { dispatch } = useCart();

  useEffect(() => {
    const fetchProduct = async () => {
      try {
        const res = await axios.get(`http://localhost:8000/api/products/${id}`);
        
        setProduct(res.data.data);
      } catch (err) {
        console.error("Lỗi khi tải sản phẩm", err);
      }
    };
    fetchProduct();
  }, [id]);

  const handleAddToCart = () => {
    if (!product) return;
    dispatch({
      type: "ADD_TO_CART",
      payload: { ...product, quantity: 1 },
    });
  };

  if (!product) return <div className="container mt-4">Đang tải sản phẩm...</div>;

  return (
    <div className="container mt-4">
      <div className="row">
        <div className="col-md-6">
          <img
            src={product.image}
            alt={product.name}
            className="img-fluid"
          />
        </div>
        <div className="col-md-6">
          <h2>{product.name}</h2>
          <p>{product.description}</p>
          <p>Giá: {product.price.toLocaleString()}₫</p>
          <button className="btn btn-primary" onClick={handleAddToCart}>
            Thêm vào giỏ
          </button>
        </div>
      </div>
    </div>
  );
}

export default ProductDetailPage;
