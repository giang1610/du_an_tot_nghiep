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
  }, [product, selectedSize, selectedColor,selectedVariant]);


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
    if (!commentText.trim()) return alert('Bình luận không được để trống!');
    const token = localStorage.getItem('token');
    if (!token) return alert('Vui lòng đăng nhập để bình luận!');

    setCommentSubmitting(true);
    try {
      const response = await axios.post(
        `${process.env.REACT_APP_API_URI}/products/${product.id}/comments`,
        { content: commentText, rating },
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

        <h4 className="mb-3">Bình luận</h4>
        <Form onSubmit={handleCommentSubmit} className="mb-4">
          <Form.Group controlId="comment">
            <Form.Control
              as="textarea"
              rows={3}
              placeholder="Nhập bình luận..."
              value={commentText}
              onChange={e => setCommentText(e.target.value)}
              className="mb-3"
            />
          </Form.Group>

          <Form.Group controlId="rating" className="mb-3">
            <Form.Label>Đánh giá</Form.Label>
            <Form.Select value={rating} onChange={e => setRating(Number(e.target.value))}>
              {[5, 4, 3, 2, 1].map(r => (
                <option key={r} value={r}>{r} sao</option>
              ))}
            </Form.Select>
          </Form.Group>

          <Button type="submit" disabled={commentSubmitting}>
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
            <Card key={index} className="mb-3 shadow-sm border-0">
              <Card.Body>
                <div className="d-flex justify-content-between align-items-center mb-1">
                  <strong className="text-primary">{cmt.user?.name || 'Khách'}</strong>
                  <div className="text-warning">
                    {'★'.repeat(cmt.rating)}{'☆'.repeat(5 - cmt.rating)}
                  </div>
                </div>
                <p className="mb-0">{cmt.content}</p>
              </Card.Body>
            </Card>
          ))
        ) : (
          <p className="text-muted">Chưa có bình luận nào.</p>
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
