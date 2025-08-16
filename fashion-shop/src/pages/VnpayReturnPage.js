// src/pages/VnpayReturn.jsx
import { useEffect, useState } from 'react';
import { useLocation, useNavigate } from 'react-router-dom';
import { Container, Spinner, Row, Col, Image, Badge, Button, Card } from 'react-bootstrap';
import axios from 'axios';
import PaymentToast from '../alert/Vnpay';
import { ArrowLeft } from 'react-bootstrap-icons';
import { useCart } from '../context/CartContext';

const formatCurrency = (num) => {
  const n = Number(num ?? 0);
  return n.toLocaleString('vi-VN');
};

export default function VnpayReturn() {
  const location = useLocation();
  const navigate = useNavigate();
  const token = localStorage.getItem('token');

  const [urlData, setUrlData] = useState({});
  const [orderDetail, setOrderDetail] = useState(null);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState('');

  const { removeSelectedItems } = useCart();

  // Lấy dữ liệu từ URL
  useEffect(() => {
    const query = new URLSearchParams(location.search);
    const data = {
      message: query.get('message'),
      order_id: query.get('order_id'),
      order_number: query.get('order_number'),
      status: query.get('status'),
      payment_status: query.get('payment_status'),
      transaction_id: query.get('transaction_id'),
    };
    setUrlData(data);
  }, [location.search]);

  // Fetch order khi có order_id
  useEffect(() => {
    const fetchOrder = async () => {
      if (!token) {
        setLoading(false);
        setError('Bạn chưa đăng nhập');
        return;
      }
      if (!urlData.order_id) {
        setLoading(false);
        return;
      }

      try {
        const res = await axios.get(`${process.env.REACT_APP_API_URL}/orders/${urlData.order_id}`, {
          headers: { Authorization: `Bearer ${token}` },
        });

        const ord = res.data.data ?? res.data ?? null;
        setOrderDetail(ord);

        // Nếu thanh toán thành công thì clear cart
        if (urlData.payment_status === 'paid') {
          await removeSelectedItems();
          localStorage.removeItem('buy_now');
        }
      } catch (err) {
        console.error(err);
        setError('Không thể tải chi tiết đơn hàng.');
      } finally {
        setLoading(false);
      }
    };

    fetchOrder();
  }, [urlData.order_id]); // Chỉ chạy khi order_id thay đổi

  if (loading) return <Spinner animation="border" className="d-block mx-auto mt-5" />;
  if (error) return <div className="text-danger text-center mt-4">{error}</div>;

  const orderTotal = Number(orderDetail?.total ?? orderDetail?.grand_total ?? orderDetail?.summary?.total ?? 0);

  return (
    <Container className="py-5">
      <div className="text-center text-black">
        <h3>Kết quả thanh toán : {urlData.message}</h3>
      </div>
      <PaymentToast message={urlData.message} status={urlData.payment_status} />

      <Row className="mt-4">
        {/* Cột trái - Sản phẩm */}
        <Col md={8} className="text-start">
          <h5>Sản phẩm</h5>

          {(!orderDetail?.items || orderDetail.items.length === 0) ? (
            <p>Không có sản phẩm.</p>
          ) : (
            <div>
              {orderDetail.items.map((item, idx) => {
                const itemTotal = Number(item.total ?? (item.price * item.quantity) ?? 0);
                const itemPrice = Number(item.price ?? 0);

                return (
                  <Card key={idx} className="mb-3 shadow-sm">
                    <Card.Body>
                      <Row className="align-items-center">
                        <Col xs={3} md={2}>
                          <Image
                            src={item.product_variant?.thumbnail || item.product_variant?.product?.img || '/placeholder.png'}
                            rounded
                            fluid
                            style={{ width: 80, height: 80, objectFit: 'cover' }}
                          />
                        </Col>

                        <Col xs={6} md={7}>
                          <div className="fw-semibold">{item.product_variant?.product?.name || item.name}</div>
                          <div className="text-muted">Màu: {item.product_variant?.color?.name || item.color || '-'}</div>
                          <div className="text-muted">Size: {item.product_variant?.size?.name || item.size || '-'}</div>
                          <div className="text-muted">SL: {item.quantity}</div>
                        </Col>

                        <Col xs={3} md={3} className="text-end">
                          <div className="text-muted mb-1">Đơn giá: {formatCurrency(itemPrice)} ₫</div>
                          <div className="fw-bold">Thành tiền: {formatCurrency(itemTotal)} ₫</div>
                        </Col>
                      </Row>
                    </Card.Body>
                  </Card>
                );
              })}
            </div>
          )}
        </Col>

        {/* Cột phải - Thông tin đơn hàng + người nhận */}
        <Col md={4}>
          <Card className="mb-4 shadow-sm">
            <Card.Body>
              <h6>Thông tin đơn hàng</h6>
              <ul className="list-group list-group-flush mb-3">
                <li className="list-group-item d-flex justify-content-between align-items-center px-0">
                  <span className="fw-semibold">Mã đơn hàng:</span>
                  <span>{urlData.order_number || orderDetail?.order_number}</span>
                </li>
                <li className="list-group-item d-flex justify-content-between align-items-center px-0">
                  <span className="fw-semibold">Trạng thái:</span>
                  <Badge bg={urlData.status === 'processing' ? 'primary' : 'secondary'}>
                    {urlData.status ?? orderDetail?.status}
                  </Badge>
                </li>
                <li className="list-group-item d-flex justify-content-between align-items-center px-0">
                  <span className="fw-semibold">Thanh toán:</span>
                  <Badge bg={urlData.payment_status === 'paid' ? 'success' : 'danger'}>
                    {urlData.payment_status ?? orderDetail?.payment_status}
                  </Badge>
                </li>
                <li className="list-group-item d-flex justify-content-between align-items-center px-0">
                  <span className="fw-semibold">Mã giao dịch:</span>
                  <span>{urlData.transaction_id || orderDetail?.transaction_id || 'N/A'}</span>
                </li>
              </ul>

              <h6>Người nhận</h6>
              <div className="mb-3">
                <div className="fw-semibold">{orderDetail?.customer_name}</div>
                <div className="text-muted">SDT: {orderDetail?.customer_phone}</div>
                <div className="text-muted">Địa chỉ: {orderDetail?.shipping_address}</div>
              </div>

              <hr />

              <div className="d-flex justify-content-between align-items-center mb-2">
                <div>Tổng đơn hàng:</div>
                <div className="fw-bold">{formatCurrency(orderTotal)} ₫</div>
              </div>

              <div className="d-grid">
                <Button variant="outline-primary" onClick={() => navigate('../orders')}>
                  <ArrowLeft className="me-2" />
                  Quay về đơn hàng của tôi
                </Button>
              </div>
            </Card.Body>
          </Card>
        </Col>
      </Row>
    </Container>
  );
}