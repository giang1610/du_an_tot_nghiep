import { useEffect, useState } from 'react';
import { useLocation, useNavigate } from 'react-router-dom';
import { Container, Spinner, Row, Col, Image, Badge, Button, Alert, Card } from 'react-bootstrap';
import axios from 'axios';
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
  const [fetched, setFetched] = useState(false);

  const { removeSelectedItems } = useCart();

  useEffect(() => {
    const query = new URLSearchParams(location.search);
    const data = {
      message: query.get('message'),
      order_id: query.get('orderId') || query.get('order_id'),
      order_number: query.get('order_number'),
      status: query.get('status'),
      payment_status: query.get('payment_status'),
      transaction_id: query.get('transaction_id'),
    };
    setUrlData(data);
  }, [location.search]);

  useEffect(() => {
    const fetchOrder = async () => {
      if (!token) return setError('Bạn chưa đăng nhập');
      if (!urlData.order_id || fetched) return;

      try {
        const res = await axios.get(
          `${process.env.REACT_APP_API_URL}/payment/vnpay/verify?orderId=${urlData.order_id}`,
          { headers: { Authorization: `Bearer ${token}` } }
        );
        setOrderDetail(res.data.data);
        if (res.data.success) {
          await removeSelectedItems();
          localStorage.removeItem('buy_now');
        }
      } catch {
        setError('Không thể tải chi tiết đơn hàng.');
      } finally {
        setFetched(true);
        setLoading(false);
      }
    };

    fetchOrder();
  }, [urlData.order_id, token, removeSelectedItems, fetched]);

  if (loading) return <Spinner animation="border" className="d-block mx-auto mt-5" />;
  if (error) return (
    <Container className="py-5">
      <Alert variant="danger" className="text-center">
        <h4>Đã xảy ra lỗi</h4>
        <div>{error}</div>
        <Button variant="outline-danger" className="mt-3" onClick={() => navigate('../orders')}>
          <ArrowLeft className="me-2" />
          Quay lại trang đơn hàng
        </Button>
      </Alert>
    </Container>
  );

  return (
    <Container className="py-5">
      <Card className="mb-4 shadow-sm">
        <Card.Body>
          <h3 className="text-center mb-4">
            Kết quả thanh toán:{" "}
            <span className={orderDetail?.payment_status === 'paid' ? "text-success" : "text-danger"}>
              {urlData.message || (orderDetail?.payment_status === 'paid' ? "Thành công" : "Chưa hoàn tất")}
            </span>
          </h3>
          <Row>
            <Col md={6}>
              <ul className="list-group shadow-sm">
                <li className="list-group-item d-flex justify-content-between">
                  <strong>Mã đơn hàng:</strong>
                  <span>{orderDetail?.order_number}</span>
                </li>
                <li className="list-group-item d-flex justify-content-between">
                  <strong>Trạng thái đơn hàng:</strong>{' '}
                  {orderDetail?.status === 'processing' ? (
                    <span className="badge bg-primary">Đang xử lý</span>
                  ) : (
                    <span className="badge bg-secondary"></span>
                  )}
                </li>
                <li className="list-group-item d-flex justify-content-between">
                  <strong>Thanh toán:</strong>{' '}
                  {orderDetail?.payment_status === 'paid' ? (
                    <span className="badge bg-success">Đã thanh toán</span>
                  ) : (
                    <span className="badge bg-warning text-dark">Chưa thanh toán</span>
                  )}
                </li>
                <li className="list-group-item d-flex justify-content-between">
                  <strong>Người nhận:</strong>
                  <span>{orderDetail?.user?.name}</span>
                </li>
                <li className="list-group-item d-flex justify-content-between">
                  <strong>SĐT:</strong>
                  <span>{orderDetail?.user?.phone}</span>
                </li>
                <li className="list-group-item d-flex justify-content-between">
                  <strong>Địa chỉ:</strong>
                  <span>{orderDetail?.shipping_address}</span>
                </li>
                <li className="list-group-item d-flex justify-content-between">
                  <strong>Email:</strong>
                  <span>{orderDetail?.user?.email}</span>
                </li>
              </ul>
            </Col>
            <Col md={6}>
              <h5 className="mb-1">Sản phẩm</h5>
              {orderDetail?.items?.map((item, idx) => {
                const variant = item.product_variant || {};
                const product = variant.product || {};
                const color = variant.color || {};
                const size = variant.size || {};
                const price = Number(item.price) * Number(item.quantity);
                const tax = price * 0.1;
                const shipping = Number(orderDetail.shipping) || 0;
                const total = price + tax + shipping;

                return (
                  <Card key={idx} className="mb-3 shadow-sm">
                    <Card.Body className="d-flex">
                      <Image
                        src={variant.thumbnail}
                        rounded
                        width={230}
                        height={230}
                        style={{ objectFit: 'cover' }}
                        className="me-5"
                      />
                      <div className="flex-grow-1">
                    <div className="d-flex justify-content-between">
                      <strong>{product.name}</strong>
                    </div>
                    <div className="d-flex justify-content-between text-muted">
                      <span>Màu:</span>
                      <span>{color.name}</span>
                    </div>
                    <div className="d-flex justify-content-between text-muted">
                      <span>Size:</span>
                      <span>{size.name}</span>
                    </div>
                    <div className="d-flex justify-content-between text-muted">
                      <span>Số lượng:</span>
                      <span>{item.quantity}</span>
                    </div>
                    <div className="d-flex justify-content-between mt-2">
                      <span>Giá:</span>
                      <strong>{Number(item.price).toLocaleString()} ₫</strong>
                    </div>
                    <div className="d-flex justify-content-between mt-1">
                      <span>Thuế (10%):</span>
                      <strong>{tax.toLocaleString()} ₫</strong>
                    </div>
                    <div className="d-flex justify-content-between mt-1">
                      <span>Phí ship:</span>
                      <strong>{shipping.toLocaleString()} ₫</strong>
                    </div>
                    <div className="d-flex justify-content-between mt-2 text-success">
                      <span>Tổng:</span>
                      <strong>{total.toLocaleString()} ₫</strong>
                    </div>
                  </div>

                    </Card.Body>
                  </Card>
                );
              })}
            </Col>
          </Row>
        </Card.Body>
      </Card>
      <div className="text-center mt-4">
        <Button variant="outline-primary" size="lg" onClick={() => navigate('../orders')}>
          <ArrowLeft className="me-2" />
          Quay về đơn hàng của tôi
        </Button>
      </div>
    </Container>
  );
}