import { Container, Row, Col } from 'react-bootstrap';
import { Truck, Gift, Repeat, Headphones } from 'react-bootstrap-icons';

export default function ServiceBar() {
  const services = [
    {
      icon: <Truck size={32} className="text-warning" />,
      title: 'MIỄN PHÍ GIAO HÀNG ĐƠN TỪ 500K',
      desc: 'Giao hàng toàn quốc'
    },
    {
      icon: <Gift size={32} className="text-warning" />,
      title: 'KIỂM TRA HÀNG KHI THANH TOÁN',
      desc: 'Nhận hàng, kiểm tra ưng ý mới thanh toán'
    },
    {
      icon: <Repeat size={32} className="text-warning" />,
      title: 'ĐỔI HÀNG LINH HOẠT',
      desc: 'Đổi hàng lên tới 30 ngày kể từ ngày mua'
    },
    {
      icon: <Headphones size={32} className="text-warning" />,
      title: 'TƯ VẤN NHANH CHÓNG',
      desc: 'Hỗ trợ 24/7 qua hotline:\n0888.566.599'
    }
  ];

  return (
    <Container className="py-4">
      <Row className="text-center">
        {services.map((item, idx) => (
          <Col key={idx} xs={12} sm={6} md={3} className="mb-4">
            <div className="d-flex flex-column align-items-center">
              <div className="mb-2">{item.icon}</div>
              <div className="fw-bold">{item.title}</div>
              <div className="text-muted small text-center" style={{ whiteSpace: 'pre-line' }}>{item.desc}</div>
            </div>
          </Col>
        ))}
      </Row>
    </Container>
  );
}
