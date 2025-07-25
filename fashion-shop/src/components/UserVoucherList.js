import React, { useEffect, useState } from 'react';
import axios from 'axios';
import { useAuth } from '../context/AuthContext'; // ✅ Đường dẫn đúng theo project
import { Container, ListGroup, Badge, Alert, Spinner } from 'react-bootstrap';

const UserVoucherList = () => {
  const { token } = useAuth(); // ✅ Sử dụng useAuth để lấy token
  const [vouchers, setVouchers] = useState([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState(null);

  useEffect(() => {
    const fetchVouchers = async () => {
      try {
        const res = await axios.get(`${process.env.REACT_APP_API_URL}/vouchers/my`, {
          headers: {
            Authorization: `Bearer ${token}`,
          },
        });
        console.log('Vouchers từ API:', res.data);
        setVouchers(res.data);
      } catch (err) {
        setError('Không thể tải danh sách mã giảm giá');
      } finally {
        setLoading(false);
      }
    };

    if (token) {
      fetchVouchers();
    }
  }, [token]);

  return (
    <Container className="my-4">
      <h3>Danh sách mã giảm giá của bạn</h3>

      {loading && <Spinner animation="border" />}
      {error && <Alert variant="danger">{error}</Alert>}
      {!loading && vouchers.length === 0 && (
        <Alert variant="info">Bạn chưa có mã giảm giá nào.</Alert>
      )}

      <ListGroup>
        {vouchers.map((voucher) => (
          <ListGroup.Item
            key={voucher.id}
            className="d-flex justify-content-between align-items-center"
          >
            <div>
              <strong>{voucher.code}</strong> -{' '}
              {voucher.type === 'percent'
                ? `Giảm ${voucher.value}%`
                : `Giảm ${voucher.value.toLocaleString()}đ`}
              {' '}({voucher.type === 'product' ? 'Sản phẩm' : 'Vận chuyển'})
            </div>
            <Badge bg="secondary">
              HSD: {voucher.end_date ? new Date(voucher.end_date).toLocaleDateString('vi-VN') : 'Không giới hạn'}
            </Badge>
          </ListGroup.Item>
        ))}
      </ListGroup>
    </Container>
  );
};

export default UserVoucherList;
