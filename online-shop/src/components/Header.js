import React, { useState } from 'react';
import { Navbar, Container, Nav, Form, FormControl, Button, NavDropdown, Badge } from 'react-bootstrap';
import { FaUser, FaShoppingCart } from 'react-icons/fa';
import { useNavigate, NavLink } from 'react-router-dom';

const Header = () => {
  const [search, setSearch] = useState('');
  const navigate = useNavigate();
  const isLoggedIn = true;
  const username = 'Nguyen Van A';
  const cartItemCount = 3;

  const handleSearch = (e) => {
    e.preventDefault();
    if (search.trim()) {
      navigate(`/products?search=${encodeURIComponent(search)}`);  // Điều hướng sang trang /products với từ khóa tìm kiếm
      setSearch('');
    }
  };

  return (
    <Navbar bg="light" expand="lg" sticky="top" className="shadow-sm">
      <Container>
        <Navbar.Brand as={NavLink} to="/">GiayXShop</Navbar.Brand>
        <Navbar.Toggle />
        <Navbar.Collapse>
          <Nav className="me-auto">
            <Nav.Link as={NavLink} to="/">Trang Chủ</Nav.Link>
            <Nav.Link as={NavLink} to="/products">Sản Phẩm</Nav.Link>  {/* Link chuyển đến trang danh sách sản phẩm */}
            <Nav.Link as={NavLink} to="/about">Giới Thiệu</Nav.Link>
            <Nav.Link as={NavLink} to="/contact">Liên Hệ</Nav.Link>
          </Nav>

          <Form className="d-flex me-3" onSubmit={handleSearch}>
            <FormControl
              type="search"
              placeholder="Tìm kiếm giày..."
              className="me-2"
              value={search}
              onChange={(e) => setSearch(e.target.value)}
            />
            <Button type="submit" variant="outline-primary">
              <i className="bi bi-search"></i>
            </Button>
          </Form>

          <Nav className="me-3">
            <Nav.Link as={NavLink} to="/cart" className="position-relative">
              <FaShoppingCart size={20} />
              {cartItemCount > 0 && (
                <Badge bg="danger" pill className="position-absolute top-0 start-100 translate-middle">
                  {cartItemCount}
                </Badge>
              )}
            </Nav.Link>
          </Nav>

          <Nav>
            {isLoggedIn ? (
              <NavDropdown title={<><FaUser /> {username}</>} id="user-dropdown">
                <NavDropdown.Item as={NavLink} to="/profile">Thông tin cá nhân</NavDropdown.Item>
                <NavDropdown.Item as={NavLink} to="/orders">Đơn hàng của tôi</NavDropdown.Item>
                <NavDropdown.Divider />
                <NavDropdown.Item as={NavLink} to="/logout">Đăng xuất</NavDropdown.Item>
              </NavDropdown>
            ) : (
              <NavDropdown title={<FaUser />} id="guest-dropdown">
                <NavDropdown.Item as={NavLink} to="/login">Đăng nhập</NavDropdown.Item>
                <NavDropdown.Item as={NavLink} to="/register">Đăng ký</NavDropdown.Item>
              </NavDropdown>
            )}
          </Nav>
        </Navbar.Collapse>
      </Container>
    </Navbar>
  );
};

export default Header;
