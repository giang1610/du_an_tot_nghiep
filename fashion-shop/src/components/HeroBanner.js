import { Swiper, SwiperSlide } from 'swiper/react';
import 'swiper/css';
import 'swiper/css/autoplay';
import { Autoplay } from 'swiper/modules';

export default function HeroBanner() {
  const banners = [
    "https://taru.vn/image/cat_images/hmv-thoitrangnam.jpg",
    "https://upcontent.vn/wp-content/uploads/2024/06/banner-thoi-trang-nam-4.jpg",
    "https://cdn.eva.vn/upload/3-2021/images/2021-08-10/11-1628570889-953-width660height367.jpg",
  ];

  return (
    <Swiper
      modules={[Autoplay]}
      autoplay={{ delay: 4000 }}
      loop
      className="hero-swiper"
    >
      {banners.map((src, index) => (
        <SwiperSlide key={index}>
          <img src={src} alt={`Banner ${index + 1}`} className="img-fluid w-100" />
        </SwiperSlide>
      ))}
    </Swiper>
  );
}
