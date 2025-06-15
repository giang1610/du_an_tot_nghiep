import React from 'react';

const Footer = () => (
  <footer className="bg-dark text-white mt-5 py-4">
    <div className="container d-flex justify-content-between flex-wrap">
      <div>
        <h5 className="fw-bold">TORANO</h5>
        <p>Thời trang nam cao cấp | Áo sơ mi, quần âu, vest</p>
        <p>Email: support@torano.vn</p>
        <p>Hotline: 1900 866 644</p>
      </div>
      <div>
        <h6 className="fw-bold">Chính sách</h6>
        <ul className="list-unstyled">
          <li>Chính sách đổi trả</li>
          <li>Chính sách bảo hành</li>
          <li>Chính sách bảo mật</li>
        </ul>
      </div>
    </div>
  </footer>
);

export default Footer;
