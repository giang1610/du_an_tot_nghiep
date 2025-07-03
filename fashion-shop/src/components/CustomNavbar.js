import { Navbar, Nav, Container, NavDropdown } from 'react-bootstrap';
import { Link, useNavigate } from 'react-router-dom';
import { useAuth } from '../context/AuthContext';
import { FaUser, FaShoppingCart } from 'react-icons/fa';
import SearchBar from './SearchBar';

export default function CustomNavbar() {
  const { user, logout } = useAuth();
  const navigate = useNavigate();

  const handleLogout = () => {
    logout();
    navigate('/');
  };

  return (
    <Navbar bg="light" expand="lg" className="shadow-sm">
      <Container>
        <Navbar.Brand as={Link} to="/">
          <img
            src="https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcRNxJnW1NiZVAaDlLsRqlSZqhEq0juTQShoQg&s"
            alt="MG Logo"
            height="30"
            className="d-inline-block align-top"
          />
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

          {/* Right nav */}
          <Nav className="align-items-center ms-3">
            <Nav.Link as={Link} to="/cart" className="me-2">
              <FaShoppingCart size={20} />
            </Nav.Link>

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
