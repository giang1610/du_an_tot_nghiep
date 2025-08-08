import React from "react";
import { Carousel } from "react-bootstrap";

export default function HeroBanner() {
  const slides = [
    {
      title: "Siêu sale mùa hè",
      desc: "Giảm đến 50% cho nhiều sản phẩm - Đặt ngay kẻo lỡ",
      img: "https://d3design.vn/uploads/summer_sale_holiday_podium_display_on_yellow_background.jpg",
    },
    {
      title: "Bộ sưu tập mới 2025",
      desc: "Phong cách hiện đại, giá tốt",
      img: "https://dongphuchaianh.com/wp-content/uploads/2025/03/banner-bst-academy-1.jpg",
    },
    {
      title: "Flash Sale mỗi ngày",
      desc: "Ưu đãi giới hạn, số lượng có hạn",
      img: "https://media3.coolmate.me/cdn-cgi/image/width=1000,quality=90,format=auto/uploads/July2025/Hero_Banner_-_1920_x_788_-_Runnng_Deal2.jpg",
    },
  ];

  return (
    <section>
      <Carousel
        fade
        indicators={false}
        interval={4500}
        pause={false}
      >
        {slides.map((s, idx) => (
          <Carousel.Item key={idx} className="bg-light">
            <div className="container py-5 d-flex align-items-center flex-column flex-md-row">
              <div className="col-md-6 text-center text-md-start">
                <h1 className="display-5 fw-bold">{s.title}</h1>
                <p className="lead text-muted">{s.desc}</p>
              </div>
              <div className="col-md-6 text-center mt-4 mt-md-0">
                <img
                  src={s.img}
                  alt={s.title}
                  className="img-fluid rounded-4"
                  style={{ maxHeight: 380 }}
                />
              </div>
            </div>
          </Carousel.Item>
        ))}
      </Carousel>
    </section>
  );
}