import React from "react";

export default function ServiceBar() {
  const services = [
    { icon: "🚚", title: "Miễn phí giao hàng", desc: "Đơn từ 500K" },
    { icon: "🔁", title: "Đổi trả dễ dàng", desc: "Trong 7 ngày" },
    { icon: "💳", title: "Thanh toán an toàn", desc: "Bảo mật" },
    { icon: "📞", title: "Hỗ trợ 24/7", desc: "Tư vấn mọi lúc" },
  ];

  return (
    <section className="bg-white border-top border-bottom">
      <div className="container py-3">
        <div className="row text-center g-3">
          {services.map((s, i) => (
            <div key={i} className="col-6 col-md-3">
              <div className="d-flex flex-column align-items-center">
                <div className="fs-2 mb-2">{s.icon}</div>
                <div className="fw-bold">{s.title}</div>
                <small className="text-muted">{s.desc}</small>
              </div>
            </div>
          ))}
        </div>
      </div>
    </section>
  );
}