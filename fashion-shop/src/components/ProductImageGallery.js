import { useState, useEffect } from 'react';
import { Row, Col, Image, Modal, Carousel } from 'react-bootstrap';

export default function ProductImageGallery({ images = [], productName, mainImage }) {
  const [mainIndex, setMainIndex] = useState(0);
  const [showModal, setShowModal] = useState(false);

  // Cập nhật ảnh chính theo mainImage nếu nó tồn tại trong images
  useEffect(() => {
    const index = images.findIndex(img => img.url === mainImage);
    if (index !== -1) {
      setMainIndex(index);
    } else {
      setMainIndex(0); // fallback nếu không tìm thấy
    }
  }, [mainImage, images]);

  const displayImage = images[mainIndex]?.url || 'https://via.placeholder.com/500x500?text=No+Image';

  return (
    <>
      <Row>
        {/* Thumbnail bên trái */}
        <Col xs={2} className="d-flex flex-column gap-2">
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
                borderRadius: 4
              }}
              onClick={() => setMainIndex(index)}
              alt={productName}
            />
          ))}
        </Col>

        {/* Ảnh chính */}
        <Col xs={10}>
          <Image
            src={displayImage}
            fluid
            onClick={() => setShowModal(true)}
            style={{
              border: '1px solid #ccc',
              maxHeight: 500,
              objectFit: 'contain',
              cursor: 'zoom-in'
            }}
            alt={productName}
          />
        </Col>
      </Row>

      {/* Modal xem lớn */}
      <Modal
        show={showModal}
        onHide={() => setShowModal(false)}
        size="lg"
        centered
      >
        <Modal.Header closeButton>
          <Modal.Title>{productName}</Modal.Title>
        </Modal.Header>
        <Modal.Body>
          <Carousel activeIndex={mainIndex} onSelect={setMainIndex} interval={null}>
            {images.map((img, idx) => (
              <Carousel.Item key={img.id || idx}>
                <img
                  src={img.url}
                  className="d-block w-100"
                  alt={productName}
                  style={{ maxHeight: '80vh', objectFit: 'contain' }}
                />
              </Carousel.Item>
            ))}
          </Carousel>
        </Modal.Body>
      </Modal>
    </>
  );
}
