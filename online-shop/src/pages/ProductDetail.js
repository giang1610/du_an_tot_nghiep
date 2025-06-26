import React, { useEffect, useState } from 'react';
import { useParams, useNavigate } from 'react-router-dom';
import { Container, Row, Col, Card, Button, Spinner, Form } from 'react-bootstrap';
import axios from 'axios';
import Header from '../components/Header';
import { useCart } from '../context/CartContext';

const ProductDetail = () => {
  const { slug } = useParams();
  const navigate = useNavigate();
  const { addToCart } = useCart();

  const [product, setProduct] = useState(null);
  const [selectedSize, setSelectedSize] = useState('');
  const [selectedColor, setSelectedColor] = useState('');
  const [quantity, setQuantity] = useState(1);
  const [mainImage, setMainImage] = useState(null);
  const [addedToCart, setAddedToCart] = useState(false);
  const [loading, setLoading] = useState(true);

  // Lấy dữ liệu sản phẩm theo slug
  useEffect(() => {
    const fetchProduct = async () => {
      try {
        const res = await axios.get(`${process.env.REACT_APP_API_URI}/products/${slug}`);
        setProduct(res.data.data.product);
      } catch (err) {
        console.error('Lỗi khi load sản phẩm:', err);
      } finally {
        setLoading(false);
      }
    };
    fetchProduct();
  }, [slug]);

  const sizes = [...new Set(product?.variants?.map(v => v.size?.name).filter(Boolean))];
  const colors = [...new Set(product?.variants?.map(v => v.color?.name).filter(Boolean))];

  const selectedVariant = product?.variants?.find(
    v => v.size?.name === selectedSize && v.color?.name === selectedColor
  );

  // Cập nhật ảnh chính theo biến thể
  useEffect(() => {
    if (product) {
      setMainImage(selectedVariant?.img || product.img);
    }
  }, [product, selectedVariant]);

  const handleAddToCart = async () => {
    if (!selectedSize || !selectedColor) return alert('Vui lòng chọn kích cỡ và màu sắc!');
    if (!selectedVariant) return alert('Biến thể không hợp lệ.');
    if (selectedVariant.stock <= 0) return alert('Sản phẩm đã hết hàng!');

    const token = localStorage.getItem('token');
    if (!token) return alert('Vui lòng đăng nhập!');

    try {
      await axios.post(`${process.env.REACT_APP_API_URI}/cart/add`, {
        product_variant_id: selectedVariant.id,
        size_id: selectedVariant.size?.id,
        color_id: selectedVariant.color?.id,
        quantity,
      }, {
        headers: { Authorization: `Bearer ${token}` }
      });

      addToCart({
        id: selectedVariant.id,
        product_variant_id: selectedVariant.id,
        name: product.name,
        price: selectedVariant.price,
        image: selectedVariant.img || product.img,
        size: selectedSize,
        color: selectedColor,
        quantity,
      });

      setAddedToCart(true);
      setTimeout(() => setAddedToCart(false), 1500);
    } catch (err) {
      console.error('Lỗi khi thêm vào giỏ hàng:', err);
      alert('Lỗi khi thêm vào giỏ hàng.');
    }
  };

  const handleBuyNow = async () => {
    await handleAddToCart();
    navigate('/checkout');
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
          {/* Hình ảnh */}
          <Col md={6}>
            <Card className="shadow-sm border-0 rounded-3 p-3 bg-white">
              <Row className="g-3">
                <Col xs="auto" className="d-flex flex-column gap-2">
                  {[selectedVariant?.img || product.img, ...(selectedVariant?.product_images || product.product_images || []).map(img => img.url)].map((img, idx) => (
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

          {/* Thông tin sản phẩm */}
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
              <>
                <h5 className="text-danger fw-bold mb-2">
                  Giá: {selectedVariant.price.toLocaleString()}₫
                </h5>
                <h6 className="mb-3 text-success">
                  Tồn kho: {selectedVariant.stock ?? 0} sản phẩm
                </h6>

                <Form.Group controlId="quantity" className="mb-3" style={{ maxWidth: 150 }}>
                  <Form.Label>Số lượng</Form.Label>
                  <Form.Control
                    type="number"
                    min={1}
                    max={selectedVariant?.stock ?? 1}
                    value={quantity}
                    onChange={(e) =>
                      setQuantity(Math.min(
                        selectedVariant?.stock ?? 1,
                        Math.max(1, parseInt(e.target.value) || 1)
                      ))
                    }
                  />
                </Form.Group>
              </>
            )}

            <div className="d-flex gap-2 mb-3">
              <Button variant="primary" onClick={handleAddToCart}>
                {addedToCart ? '✔ Đã thêm!' : '🛒 Thêm vào giỏ'}
              </Button>
              <Button variant="success" onClick={handleBuyNow}>
                Mua ngay
              </Button>
            </div>
          </Col>
        </Row>
      </Container>
    </>
  );
};

export default ProductDetail;
