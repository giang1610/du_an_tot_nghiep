import React from 'react';
import { Carousel } from 'react-bootstrap';

const Banner = () => {
  return (
    <Carousel>
      <Carousel.Item>
        <img
          className="d-block w-100"
          src="https://img.pikbest.com/origin/10/00/55/34mpIkbEsTI9M.jpg!w700wp"
          alt="Banner 1"
          style={{ maxHeight: '400px', objectFit: 'cover' }}
        />
        <Carousel.Caption>
          <h3>Giày chất lượng, giá hợp lý</h3>
          <p>Thỏa sức lựa chọn các mẫu giày thời thượng</p>
        </Carousel.Caption>
      </Carousel.Item>
      <Carousel.Item>
        <img
          className="d-block w-100"
          src="https://intphcm.com/data/upload/poster-giay-dep-mat.jpg"
          alt="Banner 2"
          style={{ maxHeight: '400px', objectFit: 'cover' }}
        />
        <Carousel.Caption>
          <h3>Khuyến mãi lớn trong tháng này</h3>
          <p>Mua ngay hôm nay để nhận ưu đãi hấp dẫn</p>
        </Carousel.Caption>
      </Carousel.Item>
    </Carousel>
  );
};

export default Banner;
