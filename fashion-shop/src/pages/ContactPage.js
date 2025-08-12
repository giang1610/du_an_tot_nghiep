
// pages/ContactPage.js

import { useState } from "react";

export default function ContactPage() {
  const [formData, setFormData] = useState({
    name: "",
    email: "",
    message: "",
  });
  const [submitted, setSubmitted] = useState(false);

  const handleChange = (e) => {
    setFormData((prev) => ({ ...prev, [e.target.name]: e.target.value }));
  };

  const handleSubmit = (e) => {
    e.preventDefault();
    setSubmitted(true);
  };

  return (
    <>
      <style>{`
        body, html {
          margin: 0; padding: 0; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
          background: #f0f4f8;
          min-height: 100vh;
          color: #222;
        }


        .container_content {

          max-width: 960px;
          margin: 3rem auto 5rem;
          display: flex;
          gap: 2rem;
          padding: 2rem;
          box-sizing: border-box;
        }

        .contact-info, .contact-form {
          background: white;
          border-radius: 12px;
          box-shadow: 0 8px 24px rgba(0,0,0,0.1);
          padding: 2rem;
          flex: 1;
        }

        .contact-info h2, .contact-form h2 {
          margin-top: 0;
          color: #0066cc;
          font-weight: 700;
          margin-bottom: 1.5rem;
        }

        .info-item {
          display: flex;
          align-items: center;
          margin-bottom: 1.2rem;
          font-size: 1.1rem;
          user-select: text;
        }
        .info-icon {
          width: 28px;
          height: 28px;
          margin-right: 12px;
          flex-shrink: 0;
          fill: #0066cc;
        }

        form {
          display: flex;
          flex-direction: column;
        }

        label {
          margin-bottom: 0.4rem;
          font-weight: 600;
        }

        input, textarea {
          padding: 10px 14px;
          margin-bottom: 1.2rem;
          border: 1.5px solid #ccc;
          border-radius: 8px;
          font-size: 1rem;
          resize: vertical;
          transition: border-color 0.3s ease;
          font-family: inherit;
        }

        input:focus, textarea:focus {
          outline: none;
          border-color: #0066cc;
          box-shadow: 0 0 8px rgba(0,102,204,0.4);
        }

        textarea {
          min-height: 100px;
        }

        button {
          padding: 14px 0;
          background-color: #0066cc;
          border: none;
          border-radius: 10px;
          color: white;
          font-size: 1.2rem;
          font-weight: 700;
          cursor: pointer;
          transition: background-color 0.3s ease, box-shadow 0.3s ease;
          user-select: none;
        }
        button:hover {
          background-color: #004a99;
          box-shadow: 0 10px 25px rgba(0,70,150,0.7);
        }

        .success-message {
          background-color: #d1e7dd;
          color: #0f5132;
          padding: 1rem 1.5rem;
          border-radius: 10px;
          font-weight: 600;
          text-align: center;
          margin-top: 1rem;
          user-select: text;
        }

        @media (max-width: 768px) {
          .container {
            flex-direction: column;
            margin: 2rem 1rem 4rem;
            padding: 1rem;
          }
        }
      `}</style>

      <div className="container_content" role="main" aria-label="Trang liên hệ MG Fashion">

        <section className="contact-info" aria-labelledby="contact-info-title">
          <h2 id="contact-info-title">Thông tin liên hệ</h2>
          <div className="info-item">
            <svg className="info-icon" viewBox="0 0 24 24" aria-hidden="true" focusable="false">
              <path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5A2.5 2.5 0 1112 6a2.5 2.5 0 010 5.5z" />
            </svg>
            <span>123 Đường MG, Quận 1, TP. Hồ Chí Minh</span>
          </div>
          <div className="info-item">
            <svg className="info-icon" viewBox="0 0 24 24" aria-hidden="true" focusable="false">
              <path d="M20 4H4a2 2 0 00-2 2v12a2 2 0 002 2h16a2 2 0 002-2V6a2 2 0 00-2-2zm0 2l-8 5-8-5h16zm0 12H4V8l8 5 8-5v10z" />
            </svg>
            <span>contact@mgfashion.com</span>
          </div>
          <div className="info-item">
            <svg className="info-icon" viewBox="0 0 24 24" aria-hidden="true" focusable="false">
              <path d="M6.62 10.79a15.05 15.05 0 006.59 6.59l2.2-2.2a1 1 0 011.1-.27 11.36 11.36 0 003.55.57 1 1 0 011 1v3.25a1 1 0 01-1 1A16 16 0 014 6a1 1 0 011-1h3.25a1 1 0 011 1 11.36 11.36 0 00.57 3.55 1 1 0 01-.27 1.1z" />
            </svg>
            <span>+84 912 345 678</span>
          </div>
          <div className="info-item">
            <svg className="info-icon" viewBox="0 0 24 24" aria-hidden="true" focusable="false">
              <path d="M12 7a5 5 0 015 5v2a5 5 0 01-10 0v-2a5 5 0 015-5zm0-4a9 9 0 00-9 9v2a9 9 0 0018 0v-2a9 9 0 00-9-9z" />
            </svg>
            <span>Giờ làm việc: 8h - 20h (T2 - CN)</span>
          </div>
        </section>

        <section className="contact-form" aria-labelledby="contact-form-title">
          <h2 id="contact-form-title">Gửi tin nhắn cho chúng tôi</h2>

          {submitted ? (
            <div className="success-message" role="alert">
              Cảm ơn bạn đã gửi liên hệ! Chúng tôi sẽ phản hồi sớm nhất có thể.
            </div>
          ) : (
            <form onSubmit={handleSubmit} noValidate>
              <label htmlFor="name">Họ và tên *</label>
              <input
                type="text"
                id="name"
                name="name"
                value={formData.name}
                onChange={handleChange}
                placeholder="Nhập họ và tên"
                required
                minLength={3}
              />

              <label htmlFor="email">Email *</label>
              <input
                type="email"
                id="email"
                name="email"
                value={formData.email}
                onChange={handleChange}
                placeholder="Nhập địa chỉ email"
                required
              />

              <label htmlFor="message">Nội dung *</label>
              <textarea
                id="message"
                name="message"
                value={formData.message}
                onChange={handleChange}
                placeholder="Nhập nội dung bạn muốn gửi"
                required
                minLength={10}
              />

              <button type="submit" aria-label="Gửi tin nhắn liên hệ">
                Gửi
              </button>
            </form>
          )}
        </section>
      </div>
    </>
  );

}
