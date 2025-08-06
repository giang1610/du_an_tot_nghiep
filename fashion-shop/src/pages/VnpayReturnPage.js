import { useEffect, useState, useCallback } from 'react';
import { useLocation, useNavigate } from 'react-router-dom';
import { Container, Spinner, Row, Col, Image, Badge, Button } from 'react-bootstrap';
import axios from 'axios';
import PaymentToast from '../alert/Vnpay';
import { ArrowLeft } from 'react-bootstrap-icons';
import { useCart } from '../context/CartContext';

export default function VnpayReturn() {
  const location = useLocation();
  const navigate = useNavigate();
  const token = localStorage.getItem('token');

  const [urlData, setUrlData] = useState({});
  const [orderDetail, setOrderDetail] = useState(null);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState('');

  const { removeSelectedItems } = useCart();

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
  }, [location]);

  const fetchOrder = useCallback(async () => {
    if (!token) return setError('Bạn chưa đăng nhập');
    if (!urlData.order_id) return;

    try {
      const res = await axios.get(`${process.env.REACT_APP_API_URL}/orders/${urlData.order_id}`, {
        headers: { Authorization: `Bearer ${token}` },
      });
      setOrderDetail(res.data.data);
      if (urlData.payment_status === 'paid') {
        await removeSelectedItems();
        localStorage.removeItem('buy_now');
      }
    } catch {
      setError('Không thể tải chi tiết đơn hàng.');
    } finally {
      setLoading(false);
    }
  }, [urlData.order_id, urlData.payment_status, token, removeSelectedItems]);

  useEffect(() => {
    fetchOrder();
  }, [fetchOrder]);

  if (loading) return <Spinner animation="border" className="d-block mx-auto mt-5" />;
  if (error) return <div className="text-danger text-center mt-4">{error}</div>;

  return (
    <Container className="py-5">
      <div className="text-center text-black">
        <h3>Kết quả thanh toán : {urlData.message}</h3>
      </div>
      <PaymentToast message={urlData.message} status={urlData.payment_status} />

      <Row className="mt-4">
        {/* Thông tin đơn hàng */}
        <Col md={6}>
          <ul className="list-group shadow-sm">
            <li className="list-group-item d-flex justify-content-between">
              <strong>Mã đơn hàng:</strong><span>{urlData.order_number}</span>
            </li>
            <li className="list-group-item d-flex justify-content-between">
              <strong>Trạng thái:</strong>
              <Badge bg={urlData.status === 'processing' ? 'primary' : 'secondary'}>
                {urlData.status}
              </Badge>
            </li>
            <li className="list-group-item d-flex justify-content-between">
              <strong>Thanh toán:</strong>
              <Badge bg={urlData.payment_status === 'paid' ? 'success' : 'danger'}>
                {urlData.payment_status}
              </Badge>
            </li>
            <li className="list-group-item d-flex justify-content-between">
              <strong>Mã giao dịch:</strong><span>{urlData.transaction_id || 'N/A'}</span>
            </li>
          </ul>
        </Col>

        {/* Sản phẩm và người nhận */}
        <Col md={6}>
          <h5>Sản phẩm</h5>
          {orderDetail?.items?.map((item, idx) => {
            const price = item.price * item.quantity;
            const tax = price * 0.1;
            const total = price + tax;

            return (
              <div key={idx} className="d-flex justify-content-between border rounded p-2 mb-3">
                <div className="d-flex">
                  <Image
                    src={item.product_variant.thumbnail}
                    rounded
                    width={80}
                    height={80}
                    style={{ objectFit: 'cover' }}
                  />
                  <div className="ms-3">
                    <div>{item.product_variant.product.name}</div>
                    <div className="text-muted">Màu: {item.product_variant.color.name}</div>
                    <div className="text-muted">Size: {item.product_variant.size.name}</div>
                    <div className="text-muted">SL: {item.quantity}</div>
                  </div>
                </div>

                <div className="text-end ms-3" style={{ minWidth: 200 }}>
                  <div><strong>Người nhận</strong></div>
                  <div>{orderDetail.customer_name}</div>
                  <div><strong>SDT:</strong> {orderDetail.customer_phone}</div>
                  <div><strong>Địa chỉ:</strong> {orderDetail.shipping_address}</div>

                  {/* <div className="text-white m-1 bg-success p-2 rounded" style={{ fontSize: '14px', maxWidth: '220px', wordWrap: 'break-word' }}>
                    <div className="mb-1">Giá gốc: {price.toLocaleString()} ₫</div>
                    <div className="mb-1">Thuế (10%): {tax.toLocaleString()} ₫</div>
                    <div><strong>Tổng: {total.toLocaleString()} ₫</strong></div>
                  </div> */}
                </div>
              </div>
            );
          })}

        </Col>
      </Row>

      <div className="text-center mt-4">
        <Button variant="outline-primary" onClick={() => navigate('../orders')}>
          <ArrowLeft className="me-2" />
          Quay về đơn hàng của tôi
        </Button>
      </div>
    </Container>
  );
}
