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
  const [mainImage, setMainImage] = useState('');


  useEffect(() => {
    const fetchProduct = async () => {
      try {
        const url = `${process.env.REACT_APP_API_URI}/products/${slug}`;
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

  const sizes = [...new Set(product?.variants?.map(v => v.size?.name).filter(Boolean))];
  const colors = [...new Set(product?.variants?.map(v => v.color?.name).filter(Boolean))];

  const getMatchingVariant = () => {
    return product?.variants?.find(
      v => v.size?.name === selectedSize && v.color?.name === selectedColor
    );
  };

  const selectedVariant = getMatchingVariant();

  const handleAddToCart = () => {
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

    setAddedToCart(true);
    setTimeout(() => setAddedToCart(false), 1500);
  };
useEffect(() => {
  if (product) {
    setMainImage(selectedVariant?.img || product.img);
  }
}, [product, selectedVariant]);

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
           <Card className="shadow-sm border-0 rounded-3 p-3 bg-white position-relative">
  <div className="d-flex" style={{ height: '300px' }}>
    {/* Cột ảnh nhỏ bên trái */}
    <div className="d-flex flex-column align-items-start gap-2 me-3">
      {[product.img, ...(product.product_images || []).map(imgObj => imgObj.url)].map((img, idx) => (
        <img
          key={idx}
          src={img}
          alt={`Thumb ${idx}`}
          onClick={() => setMainImage(img)}
          style={{
            width: 60,
            height: 60,
            objectFit: 'cover',
            borderRadius: '0.25rem',
            border: img === mainImage ? '2px solid #0d6efd' : '1px solid #ccc',
            cursor: 'pointer',
            backgroundColor: '#fff'
          }}
        />
      ))}
    </div>

    {/* Ảnh lớn */}
    <div className="flex-grow-1 d-flex justify-content-center align-items-center bg-light rounded-3 w-100">
      <Card.Img
        src={mainImage}
        className="img-fluid"
        style={{ maxHeight: '100%', objectFit: 'contain' }}
      />
    </div>
  </div>
</Card>

          </Col>

          <Col md={6}>
            <h3 className="fw-bold">{product.name}</h3>
            <p className="text-muted">{product.short_description}</p>

            <h5 className="mt-4">Kích cỡ</h5>
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
                  className="me-3"
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
                  className="me-3"
                />
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
                <strong className="text-primary">{cmt.user?.name || 'Khách'}</strong>
                <p className="mb-1">{cmt.content}</p>
                <div className="text-warning">
                  {'★'.repeat(cmt.rating)}{'☆'.repeat(5 - cmt.rating)}
                </div>
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
                <div
                  className="bg-light d-flex align-items-center justify-content-center rounded-top"
                  style={{ height: '180px' }}
                >
                  <Card.Img
                    variant="top"
                    src={rp.img}
                    style={{ maxHeight: '100%', objectFit: 'contain', maxWidth: '100%' }}
                    alt={rp.name}
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
