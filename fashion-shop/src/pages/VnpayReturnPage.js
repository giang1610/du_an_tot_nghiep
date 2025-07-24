// src/pages/VnpayReturn.jsx
import { useEffect, useState } from 'react';
import { useLocation, useNavigate } from 'react-router-dom';
import axios from 'axios';
import { Alert, Spinner, Container } from 'react-bootstrap';

export default function VnpayReturn() {
  const location = useLocation();
  const navigate = useNavigate();
  const [message, setMessage] = useState('');
  const [success, setSuccess] = useState(false);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    const fetchResult = async () => {
      try {
        const { data } = await axios.get(
          `${process.env.REACT_APP_API_URL}/payment/vnpay-return${location.search}`
        );
        setMessage(data.message);
        setSuccess(true);
        setLoading(false);
        setTimeout(() => navigate('/orders'), 3000);
      } catch (error) {
        setMessage(error.response?.data?.message || 'Lỗi không xác định');
        setSuccess(false);
        setLoading(false);
      }
    };

    fetchResult();
  }, [location, navigate]);

  return (
    <Container className="py-5">
      <h3>Kết quả thanh toán</h3>
      {loading ? (
        <Spinner animation="border" />
      ) : (
        <Alert variant={success ? 'success' : 'danger'}>{message}</Alert>
      )}
    </Container>
  );
}
