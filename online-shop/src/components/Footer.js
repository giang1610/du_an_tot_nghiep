import React from 'react';
import { Link } from 'react-router-dom';

const Footer = () => (
  <footer className="bg-dark text-white mt-5 py-4">
    <div className="container">
      <div className="row gy-4">
        <div className="col-md-6">
          <h5 className="fw-bold">TORANO</h5>
          <p className="mb-1">Thời trang nam cao cấp | Áo sơ mi, quần âu, vest</p>
          <p className="mb-1">
            📧 Email:{' '}
            <a href="mailto:support@torano.vn" className="text-white text-decoration-none">
              support@torano.vn
            </a>
          </p>
          <p className="mb-0">
            📞 Hotline:{' '}
            <a href="tel:1900866644" className="text-white text-decoration-none">
              1900 866 644
            </a>
          </p>
        </div>

        <div className="col-md-6">
          <h6 className="fw-bold">Chính sách</h6>
          <ul className="list-unstyled mb-0">
            <li>
              <Link to="/chinh-sach-doi-tra" className="text-white text-decoration-none">
                Chính sách đổi trả
              </Link>
            </li>
            <li>
              <Link to="/chinh-sach-bao-hanh" className="text-white text-decoration-none">
                Chính sách bảo hành
              </Link>
            </li>
            <li>
              <Link to="/chinh-sach-bao-mat" className="text-white text-decoration-none">
                Chính sách bảo mật
              </Link>
            </li>
          </ul>
        </div>
      </div>

      <hr className="border-top border-secondary mt-4" />

      <p className="text-center mb-0 small">
        © {new Date().getFullYear()} TORANO. All rights reserved.
      </p>
    </div>
  </footer>
);

export default Footer;
