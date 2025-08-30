import { useState, useEffect, useMemo } from 'react';
import axios from 'axios';

export default function useProductDetail(slug) {
  const [product, setProduct] = useState(null);
  const [reviews, setReviews] = useState([]);
  const [relatedProducts, setRelatedProducts] = useState([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState('');

  useEffect(() => {
    if (!slug) return;
    setLoading(true);
    setError('');

    const token = localStorage.getItem('token');
    const headers = token ? { Authorization: `Bearer ${token}` } : {};

    axios.get(`${process.env.REACT_APP_API_URL}/products/slug/${slug}`, { headers })
      .then(async res => {
        const { product, related_products } = res.data.data;
        setProduct(product);
        setRelatedProducts(related_products || []);

        // Load reviews riêng nếu cần xác thực
        try {
          const reviewRes = await axios.get(
            `${process.env.REACT_APP_API_URL}/reviews`,
            {
              params: { product_id: product.id },
              headers
            }
          );
          setReviews(reviewRes.data.data || []);
        } catch (reviewErr) {
          console.warn('Lỗi tải đánh giá:', reviewErr);
          setReviews([]);
        }
      })
      .catch(err => {
        console.error(err);
        setError('Không tải được thông tin sản phẩm.');
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

  return {
    product,
    reviews,
    relatedProducts,
    loading,
    error,
    sizes,
    colors
  };
}
