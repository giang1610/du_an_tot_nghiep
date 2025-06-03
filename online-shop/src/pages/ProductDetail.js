import React, { useEffect, useState } from 'react';
import {
  Container,
  Row,
  Col,
  Image,
  Button,
  Spinner,
  Form,
  Alert,
} from 'react-bootstrap';
import { useParams, Link, useNavigate } from 'react-router-dom';
import axios from 'axios';
import Header from '../components/Header';
import { useCart } from '../context/CartContext';
import { useAuth } from '../context/AuthContext';

axios.defaults.withCredentials = true;

const ProductDetail = () => {
  const { id } = useParams();
  const navigate = useNavigate();
  const { addToCart } = useCart();
  const { user, token } = useAuth();

  const [product, setProduct] = useState(null);
  const [loading, setLoading] = useState(true);
  const [addedToCart, setAddedToCart] = useState(false);

  const [selectedSize, setSelectedSize] = useState('');
  const [selectedColor, setSelectedColor] = useState('');
  const [availableSizes, setAvailableSizes] = useState([]);
  const [availableColors, setAvailableColors] = useState([]);

  const [comments, setComments] = useState([]);
  const [commentContent, setCommentContent] = useState('');
  const [commentError, setCommentError] = useState('');
  const [commentSubmitting, setCommentSubmitting] = useState(false);

  const [relatedProducts, setRelatedProducts] = useState([]);

  useEffect(() => {
    const fetchProduct = async () => {
      try {
        const res = await axios.get(`http://localhost:8000/api/products/${id}`);
        setProduct(res.data.data);
        setComments(res.data.data.comments || []);
        setRelatedProducts(res.data.related || []);
      } catch (error) {
        console.error(error);
      } finally {
        setLoading(false);
      }
    };
    fetchProduct();
  }, [id]);

  useEffect(() => {
    if (product && product.variants) {
      const sizes = [...new Set(product.variants.map((v) => v.size))];
      const colors = [...new Set(product.variants.map((v) => v.color))];
      setAvailableSizes(sizes);
      setAvailableColors(colors);
    }
  }, [product]);

  const handleAddToCart = () => {
    if (!selectedSize || !selectedColor) {
      alert('Vui lòng chọn kích thước và màu sắc trước khi thêm vào giỏ hàng.');
      return;
    }

    const selectedVariant = product.variants.find(
      (v) => v.size === selectedSize && v.color === selectedColor
    );

    if (!selectedVariant) {
      alert('Không tìm thấy biến thể phù hợp.');
      return;
    }

    const productWithVariant = {
      ...product,
      selectedVariant,
    };

    addToCart(productWithVariant);
    setAddedToCart(true);
    setTimeout(() => setAddedToCart(false), 1500);
  };

  const handleBuyNow = () => {
    if (!selectedSize || !selectedColor) {
      alert('Vui lòng chọn kích thước và màu sắc trước khi mua.');
      return;
    }

    const selectedVariant = product.variants.find(
      (v) => v.size === selectedSize && v.color === selectedColor
    );

    if (!selectedVariant) {
      alert('Không tìm thấy biến thể phù hợp.');
      return;
    }

    const productWithVariant = {
      ...product,
      selectedVariant,
    };

    addToCart(productWithVariant);
    navigate('/checkout');
  };

  const handleSubmitComment = async (e) => {
    e.preventDefault();
    setCommentError('');
    if (!commentContent.trim()) {
      setCommentError('Vui lòng nhập nội dung bình luận');
      return;
    }
    if (!token) {
      setCommentError('Bạn cần đăng nhập để bình luận');
      return;
    }

    setCommentSubmitting(true);
    try {
      await axios.get('http://localhost:8000/sanctum/csrf-cookie');

      const res = await axios.post(
        `http://localhost:8000/api/products/${id}/comments`,
        { content: commentContent },
        {
          headers: { Authorization: `Bearer ${token}` },
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

  if (loading)
    return (
      <div className="text-center my-5">
        <Spinner animation="border" />
      </div>
    );
  if (!product)
    return <div className="text-center text-danger">Không tìm thấy sản phẩm</div>;

  return (
    <>
      <Header />
      <Container className="my-5">
        <Row>
          <Col md={6}>
            <Image
              src={product.img}
              alt={product.name}
              fluid
              style={{ maxHeight: '500px', objectFit: 'cover' }}
            />
          </Col>
          <Col md={6}>
            <h2>{product.name}</h2>
            <h4 className="text-danger fw-bold">
              {product.price.toLocaleString()} đ
            </h4>
            <p>{product.description || 'Chưa có mô tả sản phẩm.'}</p>

            {/* Biến thể */}
            <Form className="mt-3">
  {/* Kích thước */}
  <Form.Group controlId="radioSize" className="mb-3">
    <Form.Label>Chọn kích thước</Form.Label>
    <div>
      {availableSizes.map((size) => (
        <Form.Check
          inline
          key={size}
          label={size}
          name="size"
          type="radio"
          id={`size-${size}`}
          value={size}
          checked={selectedSize === size}
          onChange={(e) => setSelectedSize(e.target.value)}
        />
      ))}
    </div>
  </Form.Group>

  {/* Màu sắc */}
  <Form.Group controlId="radioColor" className="mb-3">
    <Form.Label>Chọn màu sắc</Form.Label>
    <div>
      {availableColors.map((color) => (
        <Form.Check
          inline
          key={color}
          label={color}
          name="color"
          type="radio"
          id={`color-${color}`}
          value={color}
          checked={selectedColor === color}
          onChange={(e) => setSelectedColor(e.target.value)}
        />
      ))}
    </div>
  </Form.Group>
</Form>


            <div className="d-flex gap-2 mt-4">
              <Button variant="primary" onClick={handleAddToCart}>
                Thêm vào giỏ hàng
              </Button>
              <Button variant="success" onClick={handleBuyNow}>
                Mua ngay
              </Button>
              {/* <Button as={Link} to="/cart" variant="outline-secondary">
                Xem giỏ hàng
              </Button> */}
            </div>
            {addedToCart && (
              <div className="mt-3 text-success">Đã thêm vào giỏ hàng!</div>
            )}
          </Col>
        </Row>

        {/* Bình luận */}
        <div className="mt-5">
          <h5>Bình luận ({comments.length})</h5>
          {comments.length === 0 && <p>Chưa có bình luận nào.</p>}

          {comments.map((comment) => (
            <div key={comment.id} className="border rounded p-3 my-2">
              <strong>{comment.user.name}</strong>{' '}
              <small className="text-muted">
                {new Date(comment.created_at).toLocaleString()}
              </small>
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
            <Alert variant="info">
              Bạn cần <Link to="/login">đăng nhập</Link> để bình luận.
            </Alert>
          )}
        </div>

        {/* Sản phẩm liên quan */}
        {relatedProducts.length > 0 && (
          <div className="mt-5">
            <h5>Sản phẩm liên quan</h5>
            <Row>
              {relatedProducts.map((rel) => (
                <Col md={3} key={rel.id} className="mb-3">
                  <Link
                    to={`/products/${rel.id}`}
                    className="text-decoration-none text-dark"
                  >
                    <div className="border rounded p-2 h-100">
                      <img
                        src={rel.img}
                        alt={rel.name}
                        style={{ width: '100%', height: '150px', objectFit: 'cover' }}
                      />
                      <h6 className="mt-2">{rel.name}</h6>
                      <p className="text-danger fw-bold">
                        {rel.price.toLocaleString()} đ
                      </p>
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
