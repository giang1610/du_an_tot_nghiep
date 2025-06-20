import React, { useEffect, useState } from 'react';
import { Container, Row, Col, Image, Button, Spinner, Form, Alert } from 'react-bootstrap';
import { useParams, Link, useNavigate } from 'react-router-dom';
import axios from 'axios';
import Header from '../components/Header';
import { useAuth } from '../context/AuthContext';

axios.defaults.withCredentials = true;

const ProductDetail = () => {
  const { slug } = useParams();
  const { user } = useAuth();
  const navigate = useNavigate();

  const [product, setProduct] = useState(null);
  const [comments, setComments] = useState([]);
  const [commentContent, setCommentContent] = useState('');
  const [commentError, setCommentError] = useState('');
  const [loading, setLoading] = useState(true);
  const [addedToCart, setAddedToCart] = useState(false);
  const [commentSubmitting, setCommentSubmitting] = useState(false);
  const [relatedProducts, setRelatedProducts] = useState([]);

  // NEW: State cho biến thể
  const [selectedSize, setSelectedSize] = useState('');
  const [selectedColor, setSelectedColor] = useState('');

  useEffect(() => {
    const fetchData = async () => {
      try {
        const res = await axios.get(`http://localhost:8000/api/products/slug/${slug}`);
        setProduct(res.data.data);
        setComments(res.data.data.comments || []);
        setRelatedProducts(res.data.related || []);
      } catch (error) {
        console.error('Lỗi khi lấy sản phẩm:', error);
      } finally {
        setLoading(false);
      }
    };
    fetchData();
  }, [slug]);

  const handleAddToCart = () => {
    if (!selectedSize || !selectedColor) {
      alert('Vui lòng chọn kích cỡ và màu sắc!');
      return;
    }

    const variant = product.variants?.find(
      v => v.size === selectedSize && v.color === selectedColor
    );

    if (!variant) {
      alert('Biến thể không hợp lệ!');
      return;
    }

    let cart = JSON.parse(localStorage.getItem('cart')) || [];
    const existing = cart.find(item => item.variantId === variant.id);

    if (existing) {
      existing.quantity += 1;
    } else {
      cart.push({
        id: product.id,
        name: product.name,
        img: product.img,
        price: product.price,
        variantId: variant.id,
        size: selectedSize,
        color: selectedColor,
        quantity: 1,
      });
    }

    localStorage.setItem('cart', JSON.stringify(cart));
    setAddedToCart(true);
    setTimeout(() => setAddedToCart(false), 1500);
  };

 const handleGoToCart = () => {
  if (!selectedSize || !selectedColor) {
    alert('Vui lòng chọn kích cỡ và màu sắc!');
    return; 
  }

  handleAddToCart();
  navigate('/cart');
};

  const handleSubmitComment = async (e) => {
    e.preventDefault();
    setCommentError('');
    if (!commentContent.trim()) {
      setCommentError('Vui lòng nhập nội dung bình luận');
      return;
    }

    const token = localStorage.getItem('token');
    if (!token) {
      setCommentError('Bạn cần đăng nhập để bình luận');
      return;
    }

    setCommentSubmitting(true);
    try {
      const res = await axios.post(
        `http://localhost:8000/api/products/${slug}/comments`,
        { content: commentContent },
        {
          headers: {
            Authorization: `Bearer ${token}`
          }
        }
      );
      setComments([...comments, res.data.data]);
      setCommentContent('');
    } catch (err) {
      console.error('Lỗi gửi bình luận:', err.response || err.message);
      setCommentError(err.response?.data?.message || 'Không thể gửi bình luận');
    } finally {
      setCommentSubmitting(false);
    }
  };

  if (loading) return <div className="text-center my-5"><Spinner animation="border" /></div>;
  if (!product) return <div className="text-center text-danger">Không tìm thấy sản phẩm</div>;

  return (
    <>
      <Header />
      <Container className="my-5">
        <Row>
          <Col md={6}>
            <Image src={product.img} alt={product.name} fluid style={{ maxHeight: '500px', objectFit: 'cover' }} />
          </Col>
          <Col md={6}>
            <h2>{product.name}</h2>
            <h4 className="text-danger fw-bold">{product.price.toLocaleString()} đ</h4>
            <p>{product.description || "Chưa có mô tả sản phẩm."}</p>

            {/* Chọn kích cỡ */}
            <Form.Group className="mb-3">
              <Form.Label>Chọn kích cỡ</Form.Label>
              <Form.Select value={selectedSize} onChange={(e) => setSelectedSize(e.target.value)}>
                <option value="">-- Chọn kích cỡ --</option>
                {[...new Set(product.variants?.map(v => v.size))].map(size => (
                  <option key={size} value={size}>{size}</option>
                ))}
              </Form.Select>
            </Form.Group>

            {/* Chọn màu sắc */}
            <Form.Group className="mb-3">
              <Form.Label>Chọn màu sắc</Form.Label>
              <Form.Select value={selectedColor} onChange={(e) => setSelectedColor(e.target.value)}>
                <option value="">-- Chọn màu sắc --</option>
                {[...new Set(product.variants?.map(v => v.color))].map(color => (
                  <option key={color} value={color}>{color}</option>
                ))}
              </Form.Select>
            </Form.Group>

            <div className="d-flex gap-2 mt-4">
              <Button variant="primary" onClick={handleAddToCart}>Mua Ngay</Button>
              <Button variant="outline-success" onClick={handleGoToCart}>Thêm vào Giỏ Hàng</Button>
            </div>
            {addedToCart && <div className="mt-3 text-success">Đã thêm vào giỏ hàng!</div>}
          </Col>
        </Row>

        {/* Bình luận */}
        <div className="mt-5">
          <h5>Bình luận ({comments.length})</h5>
          {comments.length === 0 && <p>Chưa có bình luận nào.</p>}

          {comments.map((comment) => (
            <div key={comment.id} className="border rounded p-3 my-2">
              <strong>{comment.user.name}</strong> <small className="text-muted">{new Date(comment.created_at).toLocaleString()}</small>
              <p>{comment.content}</p>
            </div>
          ))}

          {user ? (
            <Form onSubmit={handleSubmitComment}>
              <Form.Group controlId="commentContent" className="mb-3">
                <Form.Label>Viết bình luận</Form.Label>
                <Form.Control
                  as="textarea"
                  rows={3}
                  value={commentContent}
                  onChange={(e) => setCommentContent(e.target.value)}
                  placeholder="Nhập bình luận..."
                />
              </Form.Group>
              {commentError && <Alert variant="danger">{commentError}</Alert>}
              <Button type="submit" disabled={commentSubmitting}>
                {commentSubmitting ? 'Đang gửi...' : 'Gửi bình luận'}
              </Button>
            </Form>
          ) : (
            <Alert variant="info">Bạn cần <Link to="/login">đăng nhập</Link> để bình luận.</Alert>
          )}
        </div>

        {/* Sản phẩm liên quan */}
        {relatedProducts.length > 0 && (
          <div className="mt-5">
            <h5>Sản phẩm liên quan</h5>
            <Row>
              {relatedProducts.map((rel) => (
                <Col md={3} key={rel.id} className="mb-3">
                  <Link to={`/products/${rel.slug}`} className="text-decoration-none text-dark">
                    <div className="border rounded p-2 h-100">
                      <img src={rel.img} alt={rel.name} style={{ width: '100%', height: '150px', objectFit: 'cover' }} />
                      <h6 className="mt-2">{rel.name}</h6>
                      <p className="text-danger fw-bold">{rel.price.toLocaleString()} đ</p>
                    </div>
                  </Link>
                </Col>
              ))}
            </Row>
          </div>
        )}
      </Container>
    </>
  );
};

export default ProductDetail;
