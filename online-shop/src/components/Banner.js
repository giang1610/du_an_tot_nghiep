import { Carousel, Container } from 'react-bootstrap';

const Banner = () => {
  return (
    <Container className="my-4">
      <Carousel fade className="shadow rounded-4 overflow-hidden">
        <Carousel.Item>
          <img
            className="d-block w-100"
            src="https://intphcm.com/data/upload/banner-thoi-trang-nam-dep.jpg"
            alt="Thời trang Nam"
          />
        </Carousel.Item>
        <Carousel.Item>
          <img
            className="d-block w-100"
            src="https://arena.fpt.edu.vn/wp-content/uploads/2022/10/banner-thoi-trang-la-mot-phan-khong-the-thieu-trong-truyen-thong-1.jpg"
            alt="Thời trang Nữ"
          />
        </Carousel.Item>
      </Carousel>
    </Container>
  );
};

export default Banner;
