import { useEffect, useState } from "react";
import { useSearchParams, useNavigate } from "react-router-dom";
import {
  Container,
  Spinner,
  Row,
  Col,
  Image,
  Badge,
  Button,
  Alert,
  Card,
} from "react-bootstrap";
import axios from "axios";
import { ArrowLeft, CheckCircleFill, XCircleFill } from "react-bootstrap-icons";
import { useCart } from "../context/CartContext";

const STATUS_LABELS = {
  pending: 'Chờ xử lý',
  processing: 'Đang xử lý',
  picking: 'Đang lấy hàng',
  shipper_arrived: 'Shipper đến lấy hàng',
  in_warehouse: 'Hàng về kho',
  shipping: 'Đang giao hàng',
  shipped: 'Đã giao hàng',
  completed: 'Hoàn thành',
  return_requested: 'Đã yêu cầu hoàn hàng',
  returned: 'Hoàn hàng',
  cancelled: 'Đã hủy',
  failed: 'Giao hàng thất bại',
  failed_1: 'Giao hàng thất bại lần 1',
  failed_2: 'Giao hàng thất bại lần 2',
  restocked: 'Hàng đã trả kho',
};

export default function MomoReturn() {
  const [searchParams] = useSearchParams();
  const navigate = useNavigate();
  const { removeSelectedItems } = useCart();

  const [loading, setLoading] = useState(true);
  const [orderDetail, setOrderDetail] = useState(null);
  const [error, setError] = useState("");

useEffect(() => {
  const fetchOrder = async () => {
    const orderId = searchParams.get("orderId");
    const resultCode = searchParams.get("resultCode");

    if (!orderId || !resultCode) {
      setError("URL không hợp lệ");
      setLoading(false);
      return;
    }

    try {
      const res = await axios.get(
        `${process.env.REACT_APP_API_URL}/payment/momo/return?orderId=${orderId}&resultCode=${resultCode}`
      );
      setOrderDetail(res.data.data);

      if (res.data?.data?.payment_status === "paid") {
        await removeSelectedItems();
        localStorage.removeItem("buy_now");
      }
    } catch (err) {
      setError(err.response?.data?.message || "Lỗi xác minh thanh toán");
    } finally {
      setLoading(false);
    }
  };

  fetchOrder();
  // eslint-disable-next-line react-hooks/exhaustive-deps
}, []);


  if (loading)
    return <Spinner animation="border" className="d-block mx-auto mt-5" />;

  if (error)
    return (
      <Container className="py-5">
        <Alert variant="danger" className="text-center shadow-sm rounded">
          <h4>Đã xảy ra lỗi</h4>
          <div>{error}</div>
          <Button
            variant="outline-danger"
            className="mt-3"
            onClick={() => navigate("../orders")}
          >
            <ArrowLeft className="me-2" />
            Quay lại trang đơn hàng
          </Button>
        </Alert>
      </Container>
    );

  const isPaid = orderDetail?.payment_status === "paid";

  return (
    <Container className="py-5">
      <Card
        className="shadow-lg border-0 rounded-0 overflow-hidden"
        style={{ width: "900px", margin: "0 auto" }}
      >
        <Card.Header
        className={`text-center py-4 ${isPaid ? "text-white" : "bg-danger text-white"}`}
        style={isPaid ? { backgroundColor: "#e83e8c" } : {}}
      >
        <div className="d-flex justify-content-center align-items-center gap-2">
          {isPaid ? <CheckCircleFill size={30} /> : <XCircleFill size={30} />}
          <h3 className="mb-0">
            {isPaid ? "Thanh toán MoMo thành công" : "Thanh toán MoMo thất bại"}
          </h3>
        </div>
      </Card.Header>


        <Card.Body className="p-4">
          <Row>
            {/* Thông tin đơn hàng */}
            <Col md={5} >
              <h5 className="mb-3">Thông tin đơn hàng</h5>
              <ul className="list-group shadow-sm rounded-3">
                <li className="list-group-item d-flex justify-content-between">
                  <strong>Mã đơn hàng:</strong>
                  <span>{orderDetail?.order_number}</span>
                </li>
                <li className="list-group-item d-flex justify-content-between">
                  <strong>Trạng thái:</strong>
                  <Badge bg="primary">
                    {STATUS_LABELS[orderDetail?.status] || orderDetail?.status}
                  </Badge>
                </li>
                <li className="list-group-item d-flex justify-content-between">
                  <strong>Thanh toán:</strong>
                  {isPaid ? (
                    <Badge bg="success">Đã thanh toán</Badge>
                  ) : (
                    <Badge bg="warning" text="dark">
                      Chưa thanh toán
                    </Badge>
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
                  <strong>Email:</strong>
                  <span>{orderDetail?.user?.email}</span>
                </li>
                <li className="list-group-item">
                  <strong>Địa chỉ:</strong>
                  <div className="text-muted">{orderDetail?.shipping_address}</div>
                </li>
              </ul>
            </Col>

            {/* Sản phẩm */}
            <Col md={7}>
              <h5 className="mb-3">Danh sách sản phẩm</h5>
              <div className="d-flex flex-column gap-3">
                {orderDetail?.items?.map((item, idx) => {
                  const variant = item.product_variant || {};
                  const product = variant.product || {};
                  const color = variant.color || {};
                  const size = variant.size || {};
                  const total = Number(item.price) * Number(item.quantity);

                  return (
                    <Card key={idx} className="shadow-sm border-0">
                      <Card.Body className="d-flex align-items-center">
                        <Image
                          src={variant.thumbnail || "/placeholder.png"}
                          rounded
                          width={100}
                          height={100}
                          style={{ objectFit: "cover" }}
                          className="me-3 border"
                        />
                        <div className="flex-grow-1">
                          <div className="fw-bold">{product.name}</div>

                          <div className="d-flex justify-content-between text-muted small">
                            <span>Màu sắc:</span>
                            <span>{color.name}</span>
                          </div>

                          <div className="d-flex justify-content-between text-muted small">
                            <span>Kích cỡ:</span>
                            <span>{size.name}</span>
                          </div>

                          <div className="d-flex justify-content-between text-muted small">
                            <span>Số lượng:</span>
                            <span>{item.quantity}</span>
                          </div>

                          <div className="d-flex justify-content-between fw-semibold mt-2">
                            <span>Giá sản phẩm:</span>
                            <span>{Number(item.price).toLocaleString()} ₫</span>
                          </div>

                          <div className="d-flex justify-content-between text-success mt-1">
                          
                            <strong>Tổng cộng:</strong>
                            <span className="fw-bold text-danger">
                              {Number(orderDetail?.total).toLocaleString("vi-VN")} ₫
                            </span>
                          
                          </div>
                        </div>
                      </Card.Body>
                    </Card>
                  );
                })}
              </div>
            </Col>
          </Row>
        </Card.Body>
      </Card>

      <div className="text-center mt-4">
        <Button
          variant="outline-primary"
          size="lg"
          className="px-4 rounded-pill"
          onClick={() => navigate("../orders")}
        >
          <ArrowLeft className="me-2" />
          Quay về đơn hàng của tôi
        </Button>
      </div>
    </Container>
  );
}
