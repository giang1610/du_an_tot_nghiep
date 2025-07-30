import { Navbar, Nav, Container, NavDropdown, Badge } from 'react-bootstrap';
import { Link, useNavigate } from 'react-router-dom';
import { useAuth } from '../context/AuthContext';
import { FaUser, FaShoppingBag } from 'react-icons/fa';
import SearchBar from './SearchBar';
import Lottie from 'lottie-react';
import phiHanhGia from '../animation/phi_hanh_gia.json';
import MGLogo from './MGLogo';
import { useCart } from '../context/CartContext';

export default function CustomNavbar() {
  const { user, logout } = useAuth();
  const navigate = useNavigate();
  const { cart = [] } = useCart();

  const handleLogout = () => {
    logout();
    navigate('/');
  };

  // Tổng số lượng sản phẩm trong giỏ (ví dụ: 2 áo + 1 quần = 3)
  const totalItems = cart.reduce((sum, item) => sum + item.quantity, 0);

  return (
    <Navbar bg="light" expand="lg" className="shadow-sm">
      <Container>
        <Navbar.Brand as={Link} to="/">
          <div style={{ width: 80, height: 80 }}>
            <MGLogo />
          </div>
        </Navbar.Brand>

        <Navbar.Toggle />

        <Navbar.Collapse className="justify-content-between">
          {/* Left nav */}
          <Nav className="me-auto align-items-center">
            <Nav.Link as={Link} to="/">Trang chủ</Nav.Link>
            <Nav.Link as={Link} to="/products">Sản phẩm</Nav.Link>
            <Nav.Link as={Link} to="/about">Giới thiệu</Nav.Link>
            <Nav.Link as={Link} to="/contact">Liên hệ</Nav.Link>
          </Nav>

          {/* Center search */}
          <SearchBar />
          <Lottie animationData={phiHanhGia} loop={true} style={{ width: 50, height: 50 }} />

          {/* Right nav */}
          <Nav className="align-items-center ms-3">
            {/* Giỏ hàng với badge */}
            <Nav.Link as={Link} to="/cart" className="position-relative me-2 cart-icon">
              <FaShoppingBag size={22} />
              {totalItems > 0 && (
                <Badge
                  bg="danger"
                  pill
                  className="position-absolute top-0 start-100 translate-middle cart-badge"
                >
                  {totalItems}
                </Badge>
              )}
            </Nav.Link>

            {/* Dropdown tài khoản */}
            <NavDropdown
              align="end"
              id="account-dropdown"
              title={
                user ? (
                  <span className="d-flex align-items-center">
                    <img
                      src={
                        user.img_thumbnail
                          ? `${process.env.REACT_APP_IMAGE_BASE_URL}/storage/${user.img_thumbnail}`
                          : '/default-avatar.png'
                      }
                      alt="avatar"
                      width="30"
                      height="30"
                      className="rounded-circle me-2"
                    />
                    {user.name}
                  </span>
                ) : (
                  <FaUser size={20} />
                )
              }
            >
              {user ? (
                <>
                  <NavDropdown.Item as={Link} to="/profile">Cập nhật thông tin</NavDropdown.Item>
                  <NavDropdown.Item as={Link} to="/change-password">Đổi mật khẩu</NavDropdown.Item>
                  <NavDropdown.Item as={Link} to="/orders">Đơn hàng của tôi</NavDropdown.Item>
                  <NavDropdown.Divider />
                  <NavDropdown.Item onClick={handleLogout}>Đăng xuất</NavDropdown.Item>
                </>
              ) : (
                <>
                  <NavDropdown.Item as={Link} to="/login">Đăng nhập</NavDropdown.Item>
                  <NavDropdown.Item as={Link} to="/register">Đăng ký</NavDropdown.Item>
                </>
              )}
            </NavDropdown>
          </Nav>
        </Navbar.Collapse>
      </Container>
    </Navbar>
  );
}