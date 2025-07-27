import { useEffect, useState, useMemo } from 'react';
import { useParams, useNavigate } from 'react-router-dom';
import axios from 'axios';
import {
  Container, Row, Col, Spinner, Alert, Button, ButtonGroup, ToggleButton, Form
} from 'react-bootstrap';
import ProductReview from './ProductReview';

import { toast, ToastContainer } from 'react-toastify';
import 'react-toastify/dist/ReactToastify.css';

import ProductImageGallery from '../components/ProductImageGallery';
import { listenToStockUpdates } from '../realtime/stockRealtime';

export default function ProductDetail() {
  const { slug } = useParams();
  const navigate = useNavigate();

  const [product, setProduct] = useState(null);
  const [reviews, setReviews] = useState([]);
  const [relatedProducts, setRelatedProducts] = useState([]);
  const [loading, setLoading] = useState(true);
  const [selectedSize, setSelectedSize] = useState('');
  const [selectedColor, setSelectedColor] = useState('');
  const [selectedVariantId, setSelectedVariantId] = useState(null);
  const [quantity, setQuantity] = useState(1);

  useEffect(() => {
    setLoading(true);
    axios.get(`${process.env.REACT_APP_API_URL}/products/slug/${slug}`)
      .then(res => {
        const { product, reviews, related_products } = res.data.data;
        setProduct(product);
        setReviews(reviews || []);
        setRelatedProducts(related_products || []);
      })
      .catch(() => {
        toast.error('Không tải được sản phẩm.');
      })
      .finally(() => setLoading(false));
  }, [slug]);

  useEffect(() => {
    if (!product) return;
    const unsubscribe = listenToStockUpdates(({ variantId, stock }) => {
      setProduct((prev) => {
        if (!prev) return prev;
        const updatedVariants = prev.variants.map((v) =>
          v.id === variantId && v.stock
            ? { ...v, stock: { ...v.stock, quantity: stock } }
            : v
        );
        return { ...prev, variants: updatedVariants };
      });
    });
    return unsubscribe;
  }, [product]);

  const imageList = useMemo(() => {
    if (!product) return [];
    const main = product.img ? [{ url: product.img }] : [];
    const variants = product.variants?.map(v => v.img).filter(Boolean).map(url => ({ url })) || [];
    const unique = Array.from(new Set([...main, ...variants].map(i => i.url))).map(url => ({ url }));
    return unique.length ? unique : [{ url: 'https://via.placeholder.com/500x500?text=No+Image' }];
  }, [product]);

  const sizes = useMemo(() => {
    const map = new Map();
    product?.variants.forEach(v => v.size?.id && map.set(v.size.id, v.size));
    return Array.from(map.values());
  }, [product]);

  const colors = useMemo(() => {
    const map = new Map();
    product?.variants.forEach(v => v.color?.id && map.set(v.color.id, v.color));
    return Array.from(map.values());
  }, [product]);

  useEffect(() => {
    if (!product || !selectedSize || !selectedColor) {
      setSelectedVariantId(null);
      setQuantity(1);
      return;
    }
    const match = product.variants.find(
      v => v.size?.id === Number(selectedSize) && v.color?.id === Number(selectedColor)
    );
    setSelectedVariantId(match?.id || null);
    setQuantity(1);
  }, [selectedSize, selectedColor, product]);

  const selectedVariant = useMemo(
    () => product?.variants.find(v => v.id === selectedVariantId),
    [selectedVariantId, product]
  );

  const maxQuantity = selectedVariant?.stock?.quantity ?? 1;

  const handleQuantityChange = (e) => {
    let val = Number(e.target.value);
    if (isNaN(val) || val < 1) val = 1;
    else if (val > maxQuantity) val = maxQuantity;
    setQuantity(val);
  };

 

  const handleAddToCart = async () => {
    if (quantity > maxQuantity) {
      toast.info(`Số lượng tối đa là ${maxQuantity}.`);
      return;
    }

    try {
      await axios.post(`${process.env.REACT_APP_API_URL}/cart/add`, {
        product_variant_id: selectedVariantId,
        quantity,
        color_id: Number(selectedColor),
        size_id: Number(selectedSize),
        note: '',
      }, {
        headers: { Authorization: `Bearer ${localStorage.getItem('token')}` },
      });
      toast.success('Đã thêm vào giỏ hàng!');
    } catch (error) {
      const msg = error?.response?.data?.message || ' Lỗi khi thêm vào giỏ hàng.';
      toast.error(msg);
    }
  };

  const handleBuyNow = () => {
    if (quantity > maxQuantity) {
      toast.info(`Số lượng tối đa là ${maxQuantity}.`);
      return;
    }

    const item = {
      product_name: product.name,
      variant_id: selectedVariant.id,
      product_variant_id: selectedVariant.id,
      quantity,
      price: selectedVariant.sale_price ?? selectedVariant.price,
      image: selectedVariant.img || product.img,
      size: selectedVariant.size?.name,
      color: selectedVariant.color?.name,
      size_id: selectedVariant.size?.id,
      color_id: selectedVariant.color?.id,
    };

    localStorage.setItem('buy_now', JSON.stringify(item));
    navigate('/checkout?buy_now=1');
  };

  if (loading) return <div className="text-center py-5"><Spinner animation="border" /></div>;
  if (!product) return <Alert variant="danger">Sản phẩm không tồn tại</Alert>;

  return (
    <Container className="py-5">
      <ToastContainer position="top-right" autoClose={3000} />

      <Row>
        <Col md={6}>
          <ProductImageGallery
            images={imageList}
            mainImage={selectedVariant?.img || product.img}
            productName={product.name}
          />
        </Col>

        <Col md={6}>
          <h2>{product.name}</h2>
          <p className="text-muted">{product.category?.name}</p>
          <h4 className="text-danger">{product.price_original?.toLocaleString()}₫</h4>
          <p>{product.description}</p>

          <h5 className="mt-4">Chọn kích cỡ:</h5>
          <ButtonGroup className="mb-3 flex-wrap">
            {sizes.map(size => (
              <ToggleButton
                key={size.id}
                id={`size-${size.id}`}
                type="radio"
                variant={selectedSize === String(size.id) ? 'dark' : 'outline-dark'}
                name="size"
                value={size.id}
                checked={selectedSize === String(size.id)}
                onChange={e => setSelectedSize(e.currentTarget.value)}
              >
                {size.name}
              </ToggleButton>
            ))}
          </ButtonGroup>

          <h5>Chọn màu sắc:</h5>
          <ButtonGroup className="mb-3 flex-wrap">
            {colors.map(color => (
              <ToggleButton
                key={color.id}
                id={`color-${color.id}`}
                type="radio"
                variant={selectedColor === String(color.id) ? 'primary' : 'outline-primary'}
                name="color"
                value={color.id}
                checked={selectedColor === String(color.id)}
                onChange={e => setSelectedColor(e.currentTarget.value)}
              >
                {color.name}
              </ToggleButton>
            ))}
          </ButtonGroup>

          {selectedVariant && (
            <>
              <p className="mt-3 text-success fw-bold">
                Giá: {(selectedVariant.sale_price ?? selectedVariant.price).toLocaleString()}₫
              </p>
              <p className="text-muted">Kho: {maxQuantity} sản phẩm</p>

              <Form.Group className="mb-3" style={{ maxWidth: 120 }}>
                <Form.Label>Số lượng:</Form.Label>
                <Form.Control
                  type="number"
                  min={1}
                  max={maxQuantity}
                  value={quantity}
                  onChange={handleQuantityChange}
                />
              </Form.Group>
            </>
          )}

          <div className="mt-4 d-flex gap-3 flex-wrap">
            <Button variant="dark" onClick={handleAddToCart} disabled={!selectedVariant}>
              🛒 Thêm vào giỏ
            </Button>
            <Button variant="danger" onClick={handleBuyNow} disabled={!selectedVariant}>
              ⚡ Mua ngay
            </Button>
          </div>

          <div className="mt-5">
            {reviews.map(r => (
              <div key={r.id} className="mb-3 border-bottom pb-2">
                <strong>{r.user?.name || 'Khách hàng'}</strong>
                <div>
                  {[...Array(r.rating)].map((_, i) => (
                    <span key={i} style={{ color: '#ffc107' }}>★</span>
                  ))}
                </div>
                <p>{r.content}</p>
              </div>
            ))}
            <ProductReview productId={product.id} selectedVariantId={selectedVariantId} />
          </div>
        </Col>
      </Row>

      <div className="mt-5">
        <h4>Sản phẩm liên quan</h4>
        <Row>
          {relatedProducts.map((rp) => {
            const imageUrl = rp.variants?.[0]?.thumbnail || 'https://via.placeholder.com/150x150?text=No+Image';
            return (
              <Col md={3} key={rp.id} className="mb-3">
                <div className="border p-2 h-100 d-flex flex-column align-items-center">
                  <img src={imageUrl} alt={rp.name} style={{ maxHeight: 150, objectFit: 'contain' }} />
                  <p className="fw-bold mt-2 text-center">{rp.name}</p>
                </div>
              </Col>
            );
          })}
        </Row>
      </div>
    </Container>
  );
}
