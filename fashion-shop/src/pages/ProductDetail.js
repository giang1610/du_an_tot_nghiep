import { useEffect, useState, useMemo } from 'react';
import { useParams, useNavigate } from 'react-router-dom';
import {
  Container, Row, Col, Spinner, Alert, Button, ButtonGroup, ToggleButton, Form
} from 'react-bootstrap';
import useProductDetail from '../hooks/useProductDetail';
import CheckoutForm from '../components/CheckoutForm';
<<<<<<< HEAD
import ProductImageGallery from '../components/ProductImageGallery'; // ✅ Thêm dòng này
=======
import ProductReview from './ProductReview';
import '../css/ProductDetail.css';
import axios from 'axios';
>>>>>>> 4242ec0 (hoàn thiện)

export default function ProductDetail() {
  const { slug } = useParams();
  const navigate = useNavigate();

<<<<<<< HEAD
  const [product, setProduct] = useState(null);
  const [reviews, setReviews] = useState([]);
  const [relatedProducts, setRelatedProducts] = useState([]);
  const [loading, setLoading] = useState(true);
=======
  const {
    product, reviews, relatedProducts, loading, error, sizes, colors
  } = useProductDetail(slug);

  const [mainImage, setMainImage] = useState('');
>>>>>>> 4242ec0 (hoàn thiện)
  const [selectedSize, setSelectedSize] = useState('');
  const [selectedColor, setSelectedColor] = useState('');
  const [selectedVariantId, setSelectedVariantId] = useState(null);
  const [quantity, setQuantity] = useState(1);
  const [alertMsg, setAlertMsg] = useState('');
  const [showCheckoutForm, setShowCheckoutForm] = useState(false);
  const [shippingAddress, setShippingAddress] = useState('');
  const [customerPhone, setCustomerPhone] = useState('');
  const [paymentMethod, setPaymentMethod] = useState('cod');

  useEffect(() => {
<<<<<<< HEAD
    setLoading(true);
    axios.get(`${process.env.REACT_APP_API_URL}/products/slug/${slug}`)
      .then(res => {
        const { product, reviews, related_products } = res.data.data;
        setProduct(product);
        setReviews(reviews || []);
        setRelatedProducts(related_products || []);
      })
      .catch(err => {
        console.error(err);
        setAlertMsg('Không tải được sản phẩm.');
      })
      .finally(() => setLoading(false));
  }, [slug]);

  const sizes = useMemo(() => {
    if (!product) return [];
    const uniqueSizes = new Map();
    product.variants.forEach(v => {
      if (v.size?.id) uniqueSizes.set(v.size.id, v.size);
    });
    return Array.from(uniqueSizes.values());
  }, [product]);

  const colors = useMemo(() => {
    if (!product) return [];
    const uniqueColors = new Map();
    product.variants.forEach(v => {
      if (v.color?.id) uniqueColors.set(v.color.id, v.color);
    });
    return Array.from(uniqueColors.values());
  }, [product]);

  useEffect(() => {
=======
>>>>>>> 4242ec0 (hoàn thiện)
    if (!product || !selectedSize || !selectedColor) {
      setSelectedVariantId(null);
      setQuantity(1);
      return;
    }
    const matched = product.variants.find(
      v => v.size?.id === Number(selectedSize) && v.color?.id === Number(selectedColor)
    );
    setSelectedVariantId(matched?.id || null);
    setQuantity(1);
  }, [selectedSize, selectedColor, product]);

  const selectedVariant = useMemo(() => {
    return product?.variants.find(v => v.id === selectedVariantId);
  }, [selectedVariantId, product]);

 const dynamicMainImage = useMemo(() => {
  const base = process.env.REACT_APP_IMAGE_BASE_URL;
  const imagePath = selectedVariant?.img || product?.img || 'https://via.placeholder.com/300';
  if (!imagePath.startsWith('http')) {
    return `${base}/storage/${imagePath}`;
  }
  return imagePath;
}, [selectedVariant, product]);


  const additionalImages = useMemo(() => {
    return selectedVariant?.images_urls || [];
  }, [selectedVariant]);

  useEffect(() => {
    setMainImage(dynamicMainImage);
  }, [dynamicMainImage]);

  const maxQuantity = selectedVariant?.stock?.quantity ?? 1;

  const handleQuantityChange = (e) => {
    let val = Number(e.target.value);
    if (isNaN(val) || val < 1) val = 1;
    else if (val > maxQuantity) val = maxQuantity;
    setQuantity(val);
  };

  const requireLoginAndVariant = () => {
    const token = localStorage.getItem('token');
    if (!token) {
      setAlertMsg('Vui lòng đăng nhập để tiếp tục.');
      navigate('/login');
      return false;
    }
    if (!selectedVariantId) {
      setAlertMsg('Vui lòng chọn size và màu.');
      return false;
    }
    return true;
  };

  const handleAddToCart = async () => {
    if (!requireLoginAndVariant()) return;
    if (quantity > maxQuantity) {
      setAlertMsg(`Số lượng tối đa là ${maxQuantity}.`);
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
      setAlertMsg('Đã thêm vào giỏ hàng!');
    } catch (error) {
      console.error(error);
      setAlertMsg('Lỗi khi thêm vào giỏ hàng.');
    }
  };

  const handleBuyNow = async () => {
    if (!requireLoginAndVariant()) return;
    if (!shippingAddress || !customerPhone) {
      setAlertMsg('Vui lòng nhập địa chỉ và số điện thoại.');
      return;
    }

    try {
      const token = localStorage.getItem('token');
      const userEmail = localStorage.getItem('user_email') || 'user@example.com';
      const price = selectedVariant.sale_price ?? selectedVariant.price;
      const subtotal = price * quantity;
      const tax = Math.round(subtotal * 0.1);
      const shipping = 20000;
      const total = subtotal + tax + shipping;

      const res = await axios.post(`${process.env.REACT_APP_API_URL}/orders/checkout`, {
        payment_method: paymentMethod,
        shipping_address: shippingAddress,
        customer_phone: customerPhone,
        customer_email: userEmail,
        items: [{ product_variant_id: selectedVariantId, quantity }],
        subtotal,
        tax,
        shipping,
        total
      }, {
        headers: { Authorization: `Bearer ${token}` },
      });

      const paymentUrl = res?.data?.data?.payment_url;
      const orderId = res?.data?.data?.order?.id;
      if (paymentUrl) {
        window.location.href = paymentUrl;
      } else {
        setAlertMsg('Đặt hàng thành công!');
        navigate(`/orders/${orderId || ''}`);
      }
    } catch (error) {
      const msg = error?.response?.data?.message || 'Đã xảy ra lỗi khi đặt hàng.';
      console.error(error);
      setAlertMsg(msg);
    }
  };

  if (loading) return <div className="text-center py-5"><Spinner animation="border" /></div>;
  if (!product) return <Alert variant="danger">{error || alertMsg || 'Không tìm thấy sản phẩm.'}</Alert>;

  return (
    <Container className="py-5">
      {alertMsg && (
        <Alert variant="info" onClose={() => setAlertMsg('')} dismissible className="mb-4">
          {alertMsg}
        </Alert>
      )}

      <Row>
        <Col md={6}>
<<<<<<< HEAD
          <ProductImageGallery images={product.images} productName={product.name} />
=======
          <Image
            src={mainImage}
            fluid
            className="border"
            alt={product.name}
          />
          <div className="d-flex mt-3 gap-2 flex-wrap">
            {additionalImages.map((imgUrl, idx) => (
              <Image
                key={idx}
                src={imgUrl}
                width={70}
                height={70}
                className={`thumbnail-img ${mainImage === imgUrl ? 'active' : ''}`}
                onClick={() => setMainImage(imgUrl)}
                alt={product.name}
              />
            ))}
          </div>
>>>>>>> 4242ec0 (hoàn thiện)
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
              <p className="text-success fw-bold">
                Giá: {(selectedVariant.sale_price ?? selectedVariant.price).toLocaleString()}₫
              </p>
              <p className="text-muted">Kho: {maxQuantity} sản phẩm</p>

              <Form.Group style={{ maxWidth: 120 }} className="mb-3">
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
            <Button
              variant="danger"
              onClick={() => {
                if (!requireLoginAndVariant()) return;
                setShowCheckoutForm(true);
              }}
              disabled={!selectedVariant}
            >
              ⚡ Mua ngay
            </Button>
          </div>

          {showCheckoutForm && (
            <CheckoutForm
              shippingAddress={shippingAddress}
              setShippingAddress={setShippingAddress}
              customerPhone={customerPhone}
              setCustomerPhone={setCustomerPhone}
              paymentMethod={paymentMethod}
              setPaymentMethod={setPaymentMethod}
              onSubmit={handleBuyNow}
            />
          )}

          <div className="mt-5">
            {reviews.map(r => (
              <div key={r.id} className="mb-3 border-bottom pb-2">
                <strong>{r.user?.name || 'Khách hàng'}</strong>
                <p>{r.comment}</p>
              </div>
            ))}
            <ProductReview
              productId={product.id}
              selectedVariantId={selectedVariantId}
            />
          </div>
        </Col>
      </Row>

      <div className="mt-5">
        <h4>Sản phẩm liên quan</h4>
        <Row>
          {relatedProducts.map(rp => (
            <Col md={3} key={rp.id} className="mb-3">
<<<<<<< HEAD
              <div className="border p-2 h-100 d-flex flex-column align-items-center">
                <img
                  src={rp.images?.[0]?.url || 'placeholder.jpg'}
=======
              <div className="border p-2 text-center">
                <Image
                  src={
                    rp.variants?.[0]?.img ||
                    rp.thumbnail ||
                    'https://via.placeholder.com/150'
                  }
                  fluid
                  className="border"
>>>>>>> 4242ec0 (hoàn thiện)
                  alt={rp.name}
                  style={{ maxHeight: 150, objectFit: 'contain' }}
                />
                <p className="fw-bold mt-2">{rp.name}</p>
              </div>
            </Col>
          ))}
        </Row>
      </div>
    </Container>
  );
}
