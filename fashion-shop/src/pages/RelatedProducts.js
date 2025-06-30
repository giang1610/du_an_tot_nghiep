import { Row, Col, Image } from 'react-bootstrap';

export default function RelatedProducts({ relatedProducts }) {
  return (
    <>
      <h4 className="mt-5">Sản phẩm liên quan</h4>
      <Row>
        {relatedProducts.map(rp => (
          <Col md={3} key={rp.id} className="mb-3">
            <div className="border p-2 h-100 d-flex flex-column align-items-center">
              <Image
                src={rp.images?.[0]?.url || 'placeholder.jpg'}
                fluid
                alt={rp.name}
                style={{ maxHeight: 150, objectFit: 'contain' }}
              />
              <p className="fw-bold mt-2 text-center">{rp.name}</p>
            </div>
          </Col>
        ))}
      </Row>
    </>
  );
}
