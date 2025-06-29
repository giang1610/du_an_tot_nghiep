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
  const [mainImage, setMainImage] = useState(null);
  const [errorMessage, setErrorMessage] = useState('');

  useEffect(() => {
    const fetchProduct = async () => {
      try {
        const res = await axios.get(`${process.env.REACT_APP_API_URI}/products/${slug}`);
        setProduct(res.data.data.product);
        setLoading(false);
      } catch (err) {
        console.error('Lỗi khi load chi tiết sản phẩm:', err);
        setLoading(false);
      }
    };
    fetchProduct();
  }, [slug]);

  const sizes = [...new Set(product?.variants?.map(v => v.size?.name).filter(Boolean))];
  const colors = [...new Set(product?.variants?.map(v => v.color?.name).filter(Boolean))];

  const getMatchingVariant = () => {
    return product?.variants?.find(
      v => v.size?.name === selectedSize && v.color?.name === selectedColor
    );
  };

  const selectedVariant = getMatchingVariant();

  useEffect(() => {
    if (product) {
      setMainImage(selectedVariant?.img || product.img);
    }
  }, [product, selectedSize, selectedColor, selectedVariant]);

  const handleAddToCart = () => {
    if (!selectedSize || !selectedColor) return alert('Vui lòng chọn kích cỡ và màu sắc!');
    if (!selectedVariant) return alert('Biến thể sản phẩm không tồn tại.');

    addToCart({
      id: selectedVariant.id,
      name: product.name,
      price: selectedVariant.price,
      image: selectedVariant.img || product.img,
      size: selectedSize,
      color: selectedColor,
    });

    setAddedToCart(true);
    setTimeout(() => setAddedToCart(false), 1500);
  };

  const handleGoToCart = () => {
    handleAddToCart();
    navigate('/cart');
  };

 const handleCommentSubmit = async (e) => {
  e.preventDefault();

  if (!commentText.trim()) {
    setErrorMessage('Đánh giá không được để trống!');
    return;
  }

  const token = localStorage.getItem('token');
  if (!token) {
    setErrorMessage('Vui lòng đăng nhập để đánh giá!');
    return;
  }

  setCommentSubmitting(true);
  setErrorMessage(''); // reset lỗi

  try {
    const response = await axios.post(
      `${process.env.REACT_APP_API_URI}/products/${product.id}/rate`,
      { content: commentText, rating },
      {
        headers: {
          Authorization: `Bearer ${token}`,
        },
      }
    );

    setProduct(prev => ({
      ...prev,
      comments: [response.data.data, ...(prev.comments || [])],
    }));
    setCommentText('');
  } catch (err) {
    if (err.response?.status === 403) {
      // ✅ THÊM THÔNG BÁO RÕ RÀNG KHI KHÔNG CÓ QUYỀN
      setErrorMessage('❌ Bạn cần mua sản phẩm này trước khi có thể đánh giá.');
    } else {
      setErrorMessage('Đã xảy ra lỗi khi gửi đánh giá.');
    }
  } finally {
    setCommentSubmitting(false);
  }
};


  if (loading) {
    return (
      <>
        <Header />
        <div className="text-center my-5">
          <Spinner animation="border" />
          <div>Đang tải sản phẩm...</div>
        </div>
      </>
    );
  }

  if (!product) {
    return (
      <>
        <Header />
        <div className="text-danger text-center my-5">Không tìm thấy sản phẩm</div>
      </>
    );
  }

  return (
    <>
      <Header />
      <Container className="my-5">
        <Row>
          <Col md={6}>
            <Card className="shadow-sm border-0 rounded-3 p-3 bg-white">
              <Row className="g-3">
                <Col xs="auto" className="d-flex flex-column gap-2">
                  {[product.img, ...(product.product_images || []).map(img => img.url)].map((img, idx) => (
                    <img
                      key={idx}
                      src={img}
                      alt={`Thumb ${idx}`}
                      onClick={() => setMainImage(img)}
                      className={`border rounded ${img === mainImage ? 'border-primary' : 'border-secondary'}`}
                      style={{ width: 60, height: 60, objectFit: 'cover', cursor: 'pointer' }}
                    />
                  ))}
                </Col>
                <Col>
                  <div className="ratio ratio-1x1 bg-light rounded">
                    <img
                      src={mainImage}
                      alt="Main"
                      className="img-fluid object-fit-contain rounded"
                    />
                  </div>
                </Col>
              </Row>
            </Card>
          </Col>

          <Col md={6}>
            <h3 className="fw-bold">{product.name}</h3>
            <p className="text-muted">{product.short_description}</p>

            <h5 className="mt-4">Kích cỡ</h5>
            <div className="mb-3 d-flex flex-wrap gap-2">
              {sizes.map(size => (
                <Button
                  key={size}
                  variant={selectedSize === size ? 'primary' : 'outline-secondary'}
                  onClick={() => setSelectedSize(size)}
                >
                  {size}
                </Button>
              ))}
            </div>

            <h5>Màu sắc</h5>
            <div className="mb-3 d-flex flex-wrap gap-2">
              {colors.map(color => (
                <Button
                  key={color}
                  variant={selectedColor === color ? 'primary' : 'outline-secondary'}
                  onClick={() => setSelectedColor(color)}
                >
                  {color}
                </Button>
              ))}
            </div>

            {selectedVariant && (
              <h5 className="text-danger fw-bold mb-3">
                Giá: {selectedVariant.price.toLocaleString()}₫
              </h5>
            )}

            <div className="d-flex gap-2 mb-3">
              <Button variant="primary" onClick={handleAddToCart}>
                {addedToCart ? '✔ Đã thêm!' : '🛒 Thêm vào giỏ'}
              </Button>
              <Button variant="success" onClick={handleGoToCart}>
                Mua ngay
              </Button>
            </div>
          </Col>
        </Row>

        <hr className="my-5" />

        {/* Đánh giá sản phẩm */}

        <h4 className="mb-3">Đánh giá sản phẩm</h4>
            {errorMessage && (
  <div className="alert alert-danger mt-3" role="alert">
    {errorMessage}
  </div>
)}
        {product.comments?.length > 0 && (
          <div className="mb-4 p-3 bg-light rounded">
            <h5 className="mb-2">
              ⭐ {(
                product.comments.reduce((acc, cmt) => acc + cmt.rating, 0) / product.comments.length
              ).toFixed(1)} / 5
            </h5>
            <p className="mb-0 text-muted">{product.comments.length} đánh giá</p>
          </div>
        )}

        <Form onSubmit={handleCommentSubmit} className="mb-4">
          <Form.Group controlId="comment">
            <Form.Control
              as="textarea"
              rows={3}
              placeholder="Viết đánh giá của bạn..."
              value={commentText}
              onChange={e => setCommentText(e.target.value)}
              className="mb-3"
            />
          </Form.Group>

          <Form.Group controlId="rating" className="mb-3">
            <Form.Label>Chọn số sao</Form.Label>
            <div className="d-flex gap-2">
              {[1, 2, 3, 4, 5].map(star => (
                <Button
                  key={star}
                  variant={rating === star ? 'warning' : 'outline-secondary'}
                  onClick={() => setRating(star)}
                >
                  {'★'.repeat(star)}
                </Button>
              ))}
            </div>
          </Form.Group>

          <Button type="submit" disabled={commentSubmitting}>
            {commentSubmitting ? (
              <>
                <Spinner size="sm" animation="border" className="me-2" />
                Đang gửi...
              </>
            ) : 'Gửi đánh giá'}
          </Button>
        </Form>

        {product.comments?.length > 0 ? (
          product.comments.map((cmt, index) => (
            <Card key={index} className="mb-3 shadow-sm border-0">
              <Card.Body>
                <div className="d-flex justify-content-between mb-2">
                  <div className="d-flex align-items-center gap-2">
                    <div className="rounded-circle bg-secondary text-white d-flex align-items-center justify-content-center" style={{ width: 40, height: 40 }}>
                      {cmt.user?.name?.charAt(0) || 'K'}
                    </div>
                    <strong>{cmt.user?.name || 'Khách'}</strong>
                  </div>
                  <div className="text-warning">
                    {'★'.repeat(cmt.rating)}{'☆'.repeat(5 - cmt.rating)}
                  </div>
                </div>
                <p className="mb-0">{cmt.content}</p>
              </Card.Body>
            </Card>
          ))
        ) : (
          <p className="text-muted">Chưa có đánh giá nào cho sản phẩm này.</p>
        )}

        <hr className="my-5" />

        <h4 className="mb-3">Sản phẩm liên quan</h4>
        <Row>
          {product.related_products?.map(rp => (
            <Col key={rp.id} md={3} sm={6} xs={12} className="mb-4">
              <Card className="h-100 shadow-sm border-0 rounded-3">
                <div className="bg-light d-flex align-items-center justify-content-center p-2 rounded-top" style={{ height: 180 }}>
                  <Card.Img
                    variant="top"
                    src={rp.img}
                    alt={rp.name}
                    className="img-fluid object-fit-contain"
                  />
                </div>
                <Card.Body>
                  <Card.Title className="text-truncate">{rp.name}</Card.Title>
                  <Button
                    variant="outline-primary"
                    size="sm"
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
