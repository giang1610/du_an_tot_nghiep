import React, { useEffect, useState } from 'react';
import { Container, Row, Col, Image, Button, Spinner } from 'react-bootstrap';
import { useParams } from 'react-router-dom';
import axios from 'axios';
import Header from '../components/Header';

const ProductDetail = () => {
  const { id } = useParams(); // lấy id từ URL
  const [product, setProduct] = useState(null);
  const [loading, setLoading] = useState(true);
  const [addedToCart, setAddedToCart] = useState(false);

  useEffect(() => {
    axios.get(`http://localhost:8000/api/products/${id}`)
      .then(res => {
        setProduct(res.data.data);
        setLoading(false);
      })
      .catch(error => {
        console.error('Lỗi khi lấy sản phẩm:', error);
        setLoading(false);
      });
  }, [id]);

  const handleAddToCart = () => {
    let cart = JSON.parse(localStorage.getItem('cart')) || [];
    const existing = cart.find(item => item.id === product.id);
    if (existing) {
      existing.quantity += 1;
    } else {
      cart.push({ ...product, quantity: 1 });
    }
    localStorage.setItem('cart', JSON.stringify(cart));
    setAddedToCart(true);
    setTimeout(() => setAddedToCart(false), 1500);
  };

  const handleBuyNow = () => {
    handleAddToCart();
    window.location.href = '/checkout'; // hoặc navigate('/checkout')
  };

  if (loading) return <div className="text-center my-5"><Spinner animation="border" /></div>;

  if (!product) return <div className="text-center text-danger">Không tìm thấy sản phẩm</div>;

  return (
    <>
    <Header/>
    <Container className="my-5">
      <Row>
        <Col md={6}>
          <Image src={product.img} alt={product.name} fluid style={{ maxHeight: '500px', objectFit: 'cover' }} />
        </Col>
        <Col md={6}>
          <h2>{product.name}</h2>
          <h4 className="text-danger fw-bold">{product.price.toLocaleString()} đ</h4>
          <p>{product.description || "Chưa có mô tả sản phẩm."}</p>

          <div className="d-flex gap-2 mt-4">
            <Button variant="primary" onClick={handleBuyNow}>Mua Ngay</Button>
            <Button variant="outline-success" onClick={handleAddToCart}>
              {addedToCart ? '✓ Đã thêm' : 'Thêm vào giỏ'}
            </Button>
          </div>
        </Col>
      </Row>
    </Container>
    </>
  );
};

export default ProductDetail;
