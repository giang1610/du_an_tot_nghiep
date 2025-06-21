import React, { useState, useEffect, useRef } from 'react';

const images = [
  "https://thegioidohoa.com/wp-content/uploads/2015/10/thiet-ke-banner-an-tuong-cho-web-thoi-trang.jpeg",
  "https://hellomida.vn/wp-content/uploads/2023/09/banner-hlmd-1.jpg",
  "https://img3.thuthuatphanmem.vn/uploads/2019/10/14/banner-thoi-trang-viet-nam_113858319.jpg",
];

const Banner = () => {
  const [index, setIndex] = useState(0);
  const intervalRef = useRef(null);

  useEffect(() => {
    intervalRef.current = setInterval(() => {
      setIndex((prevIndex) => (prevIndex + 1) % images.length);
    }, 3000);

    return () => clearInterval(intervalRef.current);
  }, []);

  return (
    <div className="container my-4">
      <img
        src={images[index]}
        alt={`Banner ${index + 1}`}
        className="img-fluid rounded shadow"
        style={{
          width: '100%',
          height: '400px',
          objectFit: 'cover',
          transition: 'opacity 0.5s ease-in-out',
        }}
      />
    </div>
  );
};

export default Banner;
