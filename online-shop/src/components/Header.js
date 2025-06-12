import React from "react";
import { Link } from "react-router-dom";
import { FaShoppingCart, FaSearch, FaUser } from "react-icons/fa";
import { useAuth } from "../context/AuthContext";

const Header = () => {
  const { user, logoutUser } = useAuth(); // Lấy thông tin user từ context

  return (
    <header className="border-bottom bg-white shadow-sm">
      <div className="container py-2 d-flex justify-content-between align-items-center">
        <Link to="/" className="navbar-brand fw-bold fs-3 text-dark">
          TORANO
        </Link>

        <form className="d-flex" style={{ maxWidth: 400, width: "100%" }}>
          <input
            className="form-control rounded-start"
            type="search"
            placeholder="Tìm sản phẩm..."
          />
          <button className="btn btn-dark rounded-end" type="submit">
            <FaSearch />
          </button>
        </form>

        <div className="d-flex align-items-center gap-3">
          <Link to="/cart" className="btn btn-outline-dark">
            <FaShoppingCart /> Giỏ hàng
          </Link>

          <div className="dropdown">
            <button
              className="btn btn-outline-secondary dropdown-toggle d-flex align-items-center"
              type="button"
              data-bs-toggle="dropdown"
              aria-expanded="false"
            >
              <FaUser className="me-2" />
              {user ? user.name : "Tài khoản"}
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

      <nav className="bg-light border-top">
        <div className="container d-flex justify-content-center gap-4 py-2">
          <Link className="text-dark fw-semibold" to="/">
            Trang chủ
          </Link>
          <Link className="text-dark fw-semibold" to="/products">
            Sản phẩm
          </Link>
          <Link className="text-dark fw-semibold" to="/collections">
            Bộ sưu tập
          </Link>
          <Link className="text-dark fw-semibold" to="/about">
            Giới thiệu
          </Link>
          <Link className="text-dark fw-semibold" to="/contact">
            Liên hệ
          </Link>
        </div>
      </nav>
    </header>
  );
};

export default Header;
