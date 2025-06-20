import React, { useEffect, useState } from 'react';
import { useParams, useNavigate } from 'react-router-dom';
import { Container, Row, Col, Card, Button, Spinner, Form } from 'react-bootstrap';
import axios from 'axios';
import Header from '../components/Header';
import { useCart } from '../context/CartContext';
const ProductDetail = () => {
  const { slug } = useParams();
  const navigate = useNavigate();
  const [product, setProduct] = useState(null);
  const [selectedSize, setSelectedSize] = useState('');
  const [selectedColor, setSelectedColor] = useState('');
  const [addedToCart, setAddedToCart] = useState(false);
  const [commentText, setCommentText] = useState('');
  const [commentSubmitting, setCommentSubmitting] = useState(false);
  const [loading, setLoading] = useState(true);
  const [rating, setRating] = useState(5);
  const { addToCart } = useCart();

  // Load product detail
  useEffect(() => {
    const fetchProduct = async () => {
      try {
        const url = `${process.env.REACT_APP_API_URI}/products/${slug}`;
        console.log("Server:", url);
        const res = await axios.get(url);
        setProduct(res.data.data.product);
        setLoading(false);
      } catch (err) {
        console.error('Lỗi khi load chi tiết sản phẩm:', err);
        setLoading(false);
      }
    };
    fetchProduct();
  }, [slug]);

  // Get sizes and colors
  const sizes = [...new Set(product?.variants?.map(v => v.size?.name).filter(Boolean))];
  const colors = [...new Set(product?.variants?.map(v => v.color?.name).filter(Boolean))];

  const getMatchingVariant = () => {
    return product?.variants?.find(
      v => v.size?.name === selectedSize && v.color?.name === selectedColor
    );
  };
  const selectedVariant = getMatchingVariant();
  // const addToCart = () => {
  //   const variant = getMatchingVariant();
  //   if (!variant) return alert('Biến thể sản phẩm không tồn tại.');

  //   const cartItem = {
  //     id: product.id,
  //     name: product.name,
  //     image: product.image,
  //     variant_id: variant.id,
  //     size: selectedSize,
  //     color: selectedColor,
  //     price: variant.price,
  //     quantity: 1,
  //   };

  //   const cart = JSON.parse(localStorage.getItem('cart')) || [];
  //   const existingIndex = cart.findIndex(item => item.variant_id === cartItem.variant_id);
  //   if (existingIndex !== -1) {
  //     cart[existingIndex].quantity += 1;
  //   } else {
  //     cart.push(cartItem);
  //   }
  //   localStorage.setItem('cart', JSON.stringify(cart));
  // };

  const handleAddToCart = () => {
    if (!selectedSize || !selectedColor) {
      return alert('Vui lòng chọn kích cỡ và màu sắc!');
    }

    const variant = getMatchingVariant();
    if (!variant) return alert('Biến thể sản phẩm không tồn tại.');

    addToCart({
      id: variant.id, // dùng variant.id để phân biệt sản phẩm khác size/color
      name: product.name,
      price: variant.price,
      image: product.image,
      size: selectedSize,
      color: selectedColor,
    });

    setAddedToCart(true);
    setTimeout(() => setAddedToCart(false), 1500);
  };


  const handleGoToCart = () => {
    if (!selectedSize || !selectedColor) {
      return alert('Vui lòng chọn kích cỡ và màu sắc!');
    }

    const variant = getMatchingVariant();
    if (!variant) return alert('Biến thể sản phẩm không tồn tại.');

    addToCart({
      id: variant.id,
      name: product.name,
      price: variant.price,
      image: product.image,
      size: selectedSize,
      color: selectedColor,
    });

    navigate('/cart');
  };

  const handleCommentSubmit = async (e) => {
    e.preventDefault();
    if (!commentText.trim()) return alert('Bình luận không được để trống!');

    const token = localStorage.getItem('token');
    if (!token) return alert('Vui lòng đăng nhập để bình luận!');

    setCommentSubmitting(true);

    try {
      const response = await axios.post(
        `${process.env.REACT_APP_API_URI}/products/${product.id}/comments`,
        {
          content: commentText,
          rating: rating
        },

        { headers: { Authorization: `Bearer ${token}` } }

      );
      setProduct(prev => ({
        ...prev,
        comments: [response.data.data, ...(prev.comments || [])],
      }));
      setCommentText('');
    } catch (err) {
      console.error('Lỗi gửi bình luận:', err);
    } finally {
      setCommentSubmitting(false);
    }


  };

  if (loading) {
    <Header />
    return (
      <div className="text-center my-5">
        <Spinner animation="border" />
        <div>Đang tải sản phẩm...</div>
      </div>
    );
  }

  if (!product) {
    <Header />
    return <div className="text-danger text-center">Không tìm thấy sản phẩm</div>;
  }

  return (
    <>
      <Header />
      <Container className="my-5">
        <Row>
          <Col md={6}>
            <Card>
              <Card.Img variant="top" src={product.image} />
            </Card>
          </Col>
          <Col md={6}>
            <h3>{product.name}</h3>
            <p>{product.description}</p>

            <h5>Kích cỡ</h5>
            <div className="mb-3">
              {sizes.map(size => (
                <Form.Check
                  inline
                  key={size}
                  type="radio"
                  name="sizeOptions"
                  label={size}
                  checked={selectedSize === size}
                  onChange={() => setSelectedSize(size)}
                />
              ))}
            </div>

            <h5>Màu sắc</h5>
            <div className="mb-3">
              {colors.map(color => (
                <Form.Check
                  inline
                  key={color}
                  type="radio"
                  name="colorOptions"
                  label={color}
                  checked={selectedColor === color}
                  onChange={() => setSelectedColor(color)}
                />
              ))}
            </div>
            {selectedVariant && (
              <h5 className="text-danger mb-3">
                Giá: {selectedVariant
                  ? selectedVariant.price.toLocaleString()
                  : product.price?.toLocaleString() || 'Vui lòng chọn size và màu'}₫
              </h5>
            )}
            <div className="mb-3">
              <Button variant="primary" onClick={handleAddToCart} className="me-2">
                {addedToCart ? 'Đã thêm!' : 'Thêm vào giỏ'}
              </Button>
              <Button variant="success" onClick={handleGoToCart}>
                Mua ngay
              </Button>
            </div>
          </Col>
        </Row>

        <hr className="my-5" />

        <h4>Bình luận</h4>
        <Form onSubmit={handleCommentSubmit} className="mb-4">
          <Form.Group controlId="comment">
            <Form.Control
              as="textarea"
              rows={3}
              placeholder="Nhập bình luận..."
              value={commentText}
              onChange={e => setCommentText(e.target.value)}
            />
          </Form.Group>

          {/* ⭐ Giao diện chọn số sao */}
          <Form.Group controlId="rating" className="mt-3">
            <Form.Label>Đánh giá</Form.Label>
            <Form.Select value={rating} onChange={e => setRating(Number(e.target.value))}>
              {[5, 4, 3, 2, 1].map(r => (
                <option key={r} value={r}>{r} sao</option>
              ))}
            </Form.Select>
          </Form.Group>

          <Button type="submit" disabled={commentSubmitting} className="mt-2">
            {commentSubmitting ? (
              <>
                <Spinner size="sm" animation="border" className="me-2" />
                Đang gửi...
              </>
            ) : 'Gửi bình luận'}
          </Button>
        </Form>


        {product.comments?.length > 0 ? (
          product.comments.map((cmt, index) => (
            <Card key={index} className="mb-2">
              <Card.Body>
                <strong>{cmt.user?.name || 'Khách'}</strong>
                <p>{cmt.content}</p>
              </Card.Body>
            </Card>
          ))
        ) : (
          <p>Chưa có bình luận nào.</p>

        )}

        <hr className="my-5" />

        <h4>Sản phẩm liên quan</h4>
        <Row>
          {product.related_products?.map(rp => (
            <Col key={rp.id} md={3} className="mb-4">
              <Card>
                <Card.Img variant="top" src={rp.image} />
                <Card.Body>
                  <Card.Title>{rp.name}</Card.Title>
                  <Button
                    variant="outline-primary"
                    onClick={() => navigate(`/products/${rp.slug}`)}
                  >
                    Xem chi tiết
                  </Button>
                </Card.Body>
              </Card>
            </Col>
          ))}
        </Row>
      </Container>
    </>

  );
};

export default ProductDetail;
