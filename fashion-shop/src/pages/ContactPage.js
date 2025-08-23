import { useState } from "react";

export default function ContactPage() {
  const [formData, setFormData] = useState({
    name: "",
    email: "",
    message: "",
  });
  const [submitted, setSubmitted] = useState(false);
  const [errors, setErrors] = useState({});
  const [loading, setLoading] = useState(false);

  // Validate form
  const validate = () => {
    const newErrors = {};
    if (!formData.name || formData.name.length < 3) {
      newErrors.name = "Vui lòng nhập họ tên (tối thiểu 3 ký tự)";
    }
    if (!formData.email || !/\S+@\S+\.\S+/.test(formData.email)) {
      newErrors.email = "Vui lòng nhập email hợp lệ";
    }
    if (!formData.message || formData.message.length < 10) {
      newErrors.message = "Nội dung phải từ 10 ký tự trở lên";
    }
    return newErrors;
  };

  const handleChange = (e) => {
    setFormData((prev) => ({ ...prev, [e.target.name]: e.target.value }));
    setErrors((prev) => ({ ...prev, [e.target.name]: undefined }));
  };

  const handleSubmit = (e) => {
    e.preventDefault();
    const newErrors = validate();
    if (Object.keys(newErrors).length) {
      setErrors(newErrors);
      return;
    }
    setLoading(true);
    setTimeout(() => {
      setSubmitted(true);
      setLoading(false);
    }, 1200); // Giả lập gửi form
  };

  return (
    <>
      <style>{`
        .contact-container {
          max-width: 1100px;
          margin: 40px auto 60px auto;
          background: #fff;
          border-radius: 16px;
          box-shadow: 0 8px 32px rgba(0,0,0,0.10);
          display: flex;
          gap: 0;
          overflow: hidden;
        }
        .contact-left {
          flex: 1.1;
          background: linear-gradient(120deg, #e0e7ff 0%, #f8fafc 100%);
          padding: 48px 38px 48px 48px;
          display: flex;
          flex-direction: column;
          justify-content: center;
        }
        .contact-left h2 {
          font-size: 2.2rem;
          font-weight: 700;
          color: #2563eb;
          margin-bottom: 1.2rem;
        }
        .contact-left p {
          color: #334155;
          font-size: 1.15rem;
          margin-bottom: 2.2rem;
        }
        .contact-info-list {
          margin-top: 1.2rem;
        }
        .contact-info-item {
          display: flex;
          align-items: center;
          gap: 14px;
          margin-bottom: 1.2rem;
          font-size: 1.08rem;
          color: #222;
        }
        .contact-info-icon {
          width: 28px;
          height: 28px;
          fill: #2563eb;
          flex-shrink: 0;
        }
        .contact-right {
          flex: 1.3;
          padding: 48px 48px 48px 38px;
          display: flex;
          flex-direction: column;
          justify-content: center;
          background: #f8fafc;
        }
        .contact-right h3 {
          font-size: 1.5rem;
          font-weight: 700;
          color: #222;
          margin-bottom: 1.5rem;
        }
        .contact-form label {
          font-weight: 600;
          margin-bottom: 0.3rem;
          color: #334155;
        }
        .contact-form input,
        .contact-form textarea {
          width: 100%;
          padding: 12px 14px;
          border-radius: 8px;
          border: 1.5px solid #cbd5e1;
          font-size: 1rem;
          margin-bottom: 0.7rem;
          background: #fff;
          transition: border-color 0.2s;
        }
        .contact-form input:focus,
        .contact-form textarea:focus {
          border-color: #2563eb;
          outline: none;
        }
        .contact-form textarea {
          min-height: 100px;
          resize: vertical;
        }
        .error-text {
          color: #ef4444;
          font-size: 0.97rem;
          margin-bottom: 0.7rem;
          margin-top: -0.2rem;
        }
        .contact-btn {
          width: 100%;
          padding: 13px 0;
          background: linear-gradient(90deg, #2563eb 60%, #6366f1 100%);
          color: #fff;
          font-size: 1.15rem;
          font-weight: 700;
          border: none;
          border-radius: 10px;
          cursor: pointer;
          margin-top: 0.5rem;
          transition: background 0.2s, box-shadow 0.2s;
          box-shadow: 0 4px 16px rgba(59,130,246,0.08);
        }
        .contact-btn:disabled {
          background: #cbd5e1;
          color: #64748b;
          cursor: not-allowed;
        }
        .contact-btn:not(:disabled):hover {
          background: linear-gradient(90deg, #1d4ed8 60%, #6366f1 100%);
          box-shadow: 0 8px 24px rgba(59,130,246,0.18);
        }
        .success-message {
          background: #e0f2fe;
          color: #0369a1;
          padding: 1.2rem 1rem;
          border-radius: 10px;
          font-weight: 600;
          text-align: center;
          margin-top: 1.5rem;
          font-size: 1.1rem;
        }
        @media (max-width: 900px) {
          .contact-container {
            flex-direction: column;
            margin: 24px 8px 40px 8px;
          }
          .contact-left, .contact-right {
            padding: 32px 16px;
          }
        }
      `}</style>
      <div className="contact-container" role="main" aria-label="Trang liên hệ MG Fashion">
        <section className="contact-left">
          <h2>Liên hệ MG Fashion</h2>
          <p>
            Nếu bạn có thắc mắc, góp ý hoặc cần hỗ trợ, hãy liên hệ với chúng tôi.<br />
            Đội ngũ MG Fashion sẽ phản hồi sớm nhất có thể!
          </p>
          <div className="contact-info-list">
            <div className="contact-info-item">
              <svg className="contact-info-icon" viewBox="0 0 24 24"><path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5A2.5 2.5 0 1112 6a2.5 2.5 0 010 5.5z"/></svg>
              <span>123 Đường ABC, Quận 1, TP. Hồ Chí Minh</span>
            </div>
            <div className="contact-info-item">
              <svg className="contact-info-icon" viewBox="0 0 24 24"><path d="M20 4H4a2 2 0 00-2 2v12a2 2 0 002 2h16a2 2 0 002-2V6a2 2 0 00-2-2zm0 2l-8 5-8-5h16zm0 12H4V8l8 5 8-5v10z"/></svg>
              <span>contact@mgfashion.com</span>
            </div>
            <div className="contact-info-item">
              <svg className="contact-info-icon" viewBox="0 0 24 24"><path d="M6.62 10.79a15.05 15.05 0 006.59 6.59l2.2-2.2a1 1 0 011.1-.27 11.36 11.36 0 003.55.57 1 1 0 011 1v3.25a1 1 0 01-1 1A16 16 0 014 6a1 1 0 011-1h3.25a1 1 0 011 1 11.36 11.36 0 00.57 3.55 1 1 0 01-.27 1.1z"/></svg>
              <span>+84 912 345 678</span>
            </div>
            <div className="contact-info-item">
              <svg className="contact-info-icon" viewBox="0 0 24 24"><path d="M12 7a5 5 0 015 5v2a5 5 0 01-10 0v-2a5 5 0 015-5zm0-4a9 9 0 00-9 9v2a9 9 0 0018 0v-2a9 9 0 00-9-9z"/></svg>
              <span>Giờ làm việc: 8h - 20h (T2 - CN)</span>
            </div>
          </div>
        </section>
        <section className="contact-right">
          <h3>Gửi tin nhắn cho chúng tôi</h3>
          <form className="contact-form" onSubmit={handleSubmit} noValidate>
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
              aria-invalid={!!errors.name}
              aria-describedby="name-error"
              autoComplete="name"
            />
            {errors.name && <div className="error-text" id="name-error">{errors.name}</div>}

            <label htmlFor="email">Email *</label>
            <input
              type="email"
              id="email"
              name="email"
              value={formData.email}
              onChange={handleChange}
              placeholder="Nhập địa chỉ email"
              required
              aria-invalid={!!errors.email}
              aria-describedby="email-error"
              autoComplete="email"
            />
            {errors.email && <div className="error-text" id="email-error">{errors.email}</div>}

            <label htmlFor="message">Nội dung *</label>
            <textarea
              id="message"
              name="message"
              value={formData.message}
              onChange={handleChange}
              placeholder="Nhập nội dung bạn muốn gửi"
              required
              minLength={10}
              aria-invalid={!!errors.message}
              aria-describedby="message-error"
            />
            {errors.message && <div className="error-text" id="message-error">{errors.message}</div>}

            <button type="submit" className="contact-btn" aria-label="Gửi tin nhắn liên hệ" disabled={loading}>
              {loading ? "Đang gửi..." : "Gửi liên hệ"}
            </button>
          </form>
          {submitted && (
            <div className="success-message" role="alert">
              Cảm ơn bạn đã gửi liên hệ! Chúng tôi sẽ phản hồi sớm nhất có thể.
            </div>
          )}
        </section>
      </div>
    </>
  );
}
