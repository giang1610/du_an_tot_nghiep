import { Modal, Form, Button, Spinner } from 'react-bootstrap';

export default function BuyNowModal({
  show,
  onHide,
  onSubmit,
  form,
  setForm,
  errors,
  loading
}) {
  return (
    <Modal show={show} onHide={onHide}>
      <Modal.Header closeButton>
        <Modal.Title>Thông tin đặt hàng</Modal.Title>
      </Modal.Header>
      <Modal.Body>
        <Form>
          {['name', 'phone', 'address'].map(field => (
            <Form.Group className="mb-3" key={field}>
              <Form.Label>
                {field === 'name' ? 'Họ tên' : field === 'phone' ? 'Số điện thoại' : 'Địa chỉ'}
              </Form.Label>
              <Form.Control
                type="text"
                value={form[field]}
                onChange={e => setForm(prev => ({ ...prev, [field]: e.target.value }))}
                isInvalid={!!errors[field]}
              />
              <Form.Control.Feedback type="invalid">{errors[field]}</Form.Control.Feedback>
            </Form.Group>
          ))}
          <Form.Group className="mb-3">
            <Form.Label>Ghi chú</Form.Label>
            <Form.Control
              as="textarea"
              rows={3}
              value={form.notes}
              onChange={e => setForm(prev => ({ ...prev, notes: e.target.value }))}
            />
          </Form.Group>
        </Form>
      </Modal.Body>
      <Modal.Footer>
        <Button variant="secondary" onClick={onHide}>Hủy</Button>
        <Button variant="danger" onClick={onSubmit} disabled={loading}>
          {loading ? <Spinner animation="border" size="sm" className="me-2" /> : null}
          Xác nhận đặt hàng
        </Button>
      </Modal.Footer>
    </Modal>
  );
}
