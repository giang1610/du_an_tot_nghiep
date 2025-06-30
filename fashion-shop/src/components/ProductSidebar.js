import { Form } from 'react-bootstrap';

export default function ProductSidebar({ filters, onFilterChange }) {
  return (
    <div className="border rounded p-3 mb-4">
      <h6 className="fw-bold mb-3">Lọc sản phẩm</h6>

      <Form.Group className="mb-3">
        <Form.Label>Khoảng giá</Form.Label>
        <Form.Select
          value={filters.price}
          onChange={(e) => onFilterChange({ ...filters, price: e.target.value })}
        >
          <option value="">Tất cả</option>
          <option value="low">Dưới 300K</option>
          <option value="mid">300K - 500K</option>
          <option value="high">Trên 500K</option>
        </Form.Select>
      </Form.Group>

      <Form.Group>
        <Form.Label>Size</Form.Label>
        <div className="d-flex gap-2 flex-wrap">
          {['S', 'M', 'L', 'XL'].map(size => (
            <Form.Check
              key={size}
              type="checkbox"
              label={size}
              value={size}
              onChange={(e) => {
                const newSizes = e.target.checked
                  ? [...filters.size, size]
                  : filters.size.filter(s => s !== size);
                onFilterChange({ ...filters, size: newSizes });
              }}
              checked={filters.size.includes(size)}
            />
          ))}
        </div>
      </Form.Group>
    </div>
  );
}
