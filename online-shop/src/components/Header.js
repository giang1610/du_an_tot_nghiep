import React from 'react';
import { Link, useNavigate } from 'react-router-dom';
import { FaShoppingCart, FaSearch, FaUser } from 'react-icons/fa';
import { useAuth } from '../context/AuthContext';

const Header = () => {
  const { user, logoutUser } = useAuth();
  const navigate = useNavigate();

  const handleSearch = (e) => {
    e.preventDefault();
    const query = e.target.elements.search.value.trim();
    if (query) navigate(`/search?q=${encodeURIComponent(query)}`);
  };

  return (
    <header className="border-bottom bg-white shadow-sm">
      <div className="container py-2 d-flex justify-content-between align-items-center flex-wrap gap-3">
        {/* Logo */}
        <Link to="/" className="navbar-brand fw-bold fs-3 text-dark">
          TORANO
        </Link>

        {/* Search bar */}
        <form
          className="d-flex flex-grow-1 mx-3"
          onSubmit={handleSearch}
          style={{ maxWidth: 400 }}
        >
          <input
            name="search"
            className="form-control rounded-start"
            type="search"
            placeholder="Tìm sản phẩm..."
            aria-label="Search"
          />
          <button className="btn btn-dark rounded-end" type="submit">
            <FaSearch />
          </button>
        </form>

        {/* Actions */}
        <div className="d-flex align-items-center gap-3">
          {/* Cart */}
          <Link to="/cart" className="btn btn-outline-dark">
            <FaShoppingCart className="me-1" />
            Giỏ hàng
          </Link>

          {/* Account dropdown */}
          <div className="dropdown">
            <button
              className="btn btn-outline-secondary dropdown-toggle d-flex align-items-center"
              type="button"
              data-bs-toggle="dropdown"
              aria-expanded="false"
            >
              <FaUser className="me-2" />
              {user ? user.name : 'Tài khoản'}
            </button>
            <ul className="dropdown-menu dropdown-menu-end">
              {user ? (
                <>
                  <li>
                    <Link className="dropdown-item" to="/account">
                      Tài khoản của tôi
                    </Link>
                  </li>
                  <li>
                    <button className="dropdown-item" onClick={logoutUser}>
                      Đăng xuất
                    </button>
                  </li>
                </>
              ) : (
                <>
                  <li>
                    <Link className="dropdown-item" to="/login">
                      Đăng nhập
                    </Link>
                  </li>
                  <li>
                    <Link className="dropdown-item" to="/register">
                      Đăng ký
                    </Link>
                  </li>
                </>
              )}
            </ul>
          </div>
        </div>
      </div>

      {/* Navigation */}
      <nav className="bg-light border-top">
        <div className="container d-flex flex-wrap justify-content-center gap-4 py-2">
          <Link className="text-dark fw-semibold text-decoration-none" to="/">
            Trang chủ
          </Link>
          <Link className="text-dark fw-semibold text-decoration-none" to="/products">
            Sản phẩm
          </Link>
          <Link className="text-dark fw-semibold text-decoration-none" to="/collections">
            Bộ sưu tập
          </Link>
          <Link className="text-dark fw-semibold text-decoration-none" to="/about">
            Giới thiệu
          </Link>
          <Link className="text-dark fw-semibold text-decoration-none" to="/contact">
            Liên hệ
          </Link>
        </div>
      </nav>
    </header>
  );
};

export default Header;
