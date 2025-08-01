import { Container, Row, Col } from 'react-bootstrap';
import { FaPhone, FaMapMarkerAlt, FaEnvelope, FaFacebook, FaInstagram } from 'react-icons/fa';

export default function Footer() {
  return (
    <footer className="bg-dark text-white pt-5 pb-3">
      <Container>
        
        <Row className="mb-4">
          <Col md={4}>
            <h5 className="fw-bold mb-3">ToranoStyle</h5>
            <p><FaMapMarkerAlt className="me-2" />123 Thời Trang, Q.1, TP.HCM</p>
            <p><FaPhone className="me-2" />0909.123.456</p>
            <p><FaEnvelope className="me-2" />support@torano.vn</p>
          </Col>
          <Col md={4}>
            <h6 className="fw-bold mb-3">Chính sách</h6>
            <ul className="list-unstyled">
              <li>– Chính sách đổi trả</li>
              <li>– Chính sách giao hàng</li>
              <li>– Bảo hành trọn đời</li>
            </ul>
          </Col>
          <Col md={4}>
            <h6 className="fw-bold mb-3">Kết nối với chúng tôi</h6>
            <FaFacebook size={24} className="me-3" />
            <FaInstagram size={24} />
            <p className="mt-3"><small>© 2025 Torano.vn – All rights reserved</small></p>

          </Col>
        </Row>
      </Container>
    </footer>
  );
}
