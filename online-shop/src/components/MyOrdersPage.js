import React, { useEffect, useState } from 'react';
import axios from 'axios';
import { Spinner, Table, Badge, Container } from 'react-bootstrap';
import { useAuth } from '../context/AuthContext';
import { Link } from 'react-router-dom';

const MyOrdersPage = () => {
  const { token } = useAuth();
  const [orders, setOrders] = useState([]);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
  const fetchOrders = async () => {
    try {
      const res = await axios.get(`${process.env.REACT_APP_API_URI}/orders`, {
        headers: {
          Authorization: `Bearer ${token}`
        }
      });
      console.log(res.data); // debug xem có "orders" không
      setOrders(Array.isArray(res.data.orders) ? res.data.orders : []);
    } catch (err) {
      console.error('Lỗi khi tải đơn hàng:', err);
      setOrders([]); // fallback tránh undefined
    } finally {
      setLoading(false);
    }
  };

  fetchOrders();
}, [token]);

  if (loading) {
    return (
      <Container className="py-5 text-center">
        <Spinner animation="border" />
        <p className="mt-2">Đang tải đơn hàng...</p>
      </Container>
    );
  }

  return (
    <Container className="py-5">
      <h2 className="mb-4 fw-bold">📦 Đơn hàng của tôi</h2>
      {orders.length === 0 ? (
        <p>Chưa có đơn hàng nào.</p>
      ) : (
        <Table bordered hover responsive>
          <thead>
            <tr>
              <th>Mã đơn</th>
              <th>Tổng tiền</th>
              <th>Trạng thái</th>
              <th>Ngày đặt</th>
              <th></th>
            </tr>
          </thead>
          <tbody>
            {orders.map(order => (
              <tr key={order.id}>
                <td>{order.order_number}</td>
                <td>{order.total.toLocaleString()} đ</td>
                <td>
                  <Badge bg={order.status === 'completed' ? 'success' : 'warning'}>
                    {order.status}
                  </Badge>
                </td>
                <td>{new Date(order.created_at).toLocaleDateString()}</td>
                <td>
                  <Link to={`/orders/${order.id}`} className="btn btn-sm btn-outline-dark">
                    Xem chi tiết
                  </Link>
                </td>
              </tr>
            ))}
          </tbody>
        </Table>
      )}
    </Container>
  );
};

export default MyOrdersPage;
