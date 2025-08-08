import { useState, useEffect } from 'react';
import { Row, Col, Image, Modal, Carousel } from 'react-bootstrap';

export default function ProductImageGallery({ images = [], productName, mainImage, onClickMain }) {
  const [mainIndex, setMainIndex] = useState(0);
  const [showModal, setShowModal] = useState(false);

  // Cập nhật mainIndex khi mainImage thay đổi
  useEffect(() => {
    const index = images.findIndex(img => img.url === mainImage);
    if (index !== -1) setMainIndex(index);
    else setMainIndex(0);
  }, [mainImage, images]);

  const displayImage = images[mainIndex]?.url || 'https://via.placeholder.com/500x500?text=No+Image';

  return (
    <>
      <Row>
        {/* Cột ảnh phụ bên trái */}
        <Col
          xs={2}
          className="d-flex flex-column gap-2"
          style={{
            maxHeight: 500,
            overflowY: 'auto',
            overflowX: 'hidden', //  <-- Ẩn thanh cuộn ngang
            paddingRight: '0.5rem', // tùy chọn cho khoảng cách
          }}
        >
          {images.map((img, index) => (
            <Image
              key={img.id || index}
              src={img.url}
              width={60}
              height={60}
              style={{
                objectFit: 'cover',
                border: index === mainIndex ? '2px solid #000' : '1px solid #ddd',
                cursor: 'pointer',
                borderRadius: 4,
                flexShrink: 0,
                display: 'block', // tránh inline-block margin overflow
              }}
              onClick={() => setMainIndex(index)}
              alt={productName}
              thumbnail
              draggable={false}
            />
          ))}
        </Col>

        {/* Cột ảnh chính bên phải */}
        <Col xs={10}>
          <Image
            src={displayImage}
            fluid
            onClick={() => {
              setShowModal(true);
              if (onClickMain) onClickMain(); 
            }}
            style={{
              border: '1px solid #ccc',
              maxHeight: 500,
              objectFit: 'contain',
              cursor: 'zoom-in',
              width: '100%',
            }}
            alt={productName}
          />
        </Col>
      </Row>

      {/* Modal xem ảnh lớn */}
      <Modal show={showModal} onHide={() => setShowModal(false)} size="lg" centered>
        <Modal.Header closeButton>
          <Modal.Title>{productName}</Modal.Title>
        </Modal.Header>
        <Modal.Body>
          <Carousel activeIndex={mainIndex} onSelect={setMainIndex} interval={null} indicators={images.length > 1}>
            {images.map((img, idx) => (
              <Carousel.Item key={img.id || idx}>
                <img
                  src={img.url}
                  alt={productName}
                  className="d-block w-100"
                  style={{ maxHeight: '80vh', objectFit: 'contain' }}
                  draggable={false}
                />
              </Carousel.Item>
            ))}
          </Carousel>
        </Modal.Body>
      </Modal>
    </>
  );
}
