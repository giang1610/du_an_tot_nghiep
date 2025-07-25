import { Form, Button, Card, Alert, Badge } from 'react-bootstrap';
import { XCircle, CheckCircle } from 'react-bootstrap-icons';

const VoucherInput = ({ type, code, setCode, onApply, info, label, variant }) => {
  const handleRemove = () => {
    if (type === 'product') {
      setCode('');
      onApply('remove_product');
    } else if (type === 'shipping') {
      setCode('');
      onApply('remove_shipping');
    }
  };

  return (
    <Card className="mb-3 shadow-sm">
      <Card.Body>
        {!info ? (
          <Form
            className="d-flex"
            onSubmit={e => {
              e.preventDefault();
              onApply(type);
            }}
          >
            <Form.Control
              type="text"
              placeholder={label}
              value={code}
              onChange={e => setCode(e.target.value)}
              className="me-2"
            />
            <Button variant={variant} onClick={() => onApply(type)}>
              Áp dụng
            </Button>
          </Form>
        ) : (
          <div className="d-flex align-items-center justify-content-between">
            <div className="flex-grow-1">
              <Alert variant="success" className="d-flex align-items-center mb-0 py-2 px-3">
                <CheckCircle className="me-2 text-success" />
                <div>
                  <strong>{info.code}</strong>{' '}
                  <Badge bg="success" className="ms-2">
                    {info.type === 'percent'
                      ? `-${info.value}%`
                      : `-${info.value.toLocaleString()} đ`}
                  </Badge>
                </div>
              </Alert>
            </div>
            <Button variant="outline-danger" size="sm" onClick={handleRemove}>
              <XCircle />
            </Button>
          </div>
        )}
      </Card.Body>
    </Card>
  );
};

export default VoucherInput;
