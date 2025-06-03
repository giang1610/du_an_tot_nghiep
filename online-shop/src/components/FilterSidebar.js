const FilterSidebar = ({ filters, onFilterChange }) => {
  const handleChange = (field, value) => {
    onFilterChange((prev) => ({ ...prev, [field]: value }));
  };

  return (
    <div>
      <h5>Bộ lọc</h5>

      <label>Danh mục</label>
      <select
        className="form-select mb-2"
        value={filters.category}
        onChange={(e) => handleChange("category", e.target.value)}
      >
        <option value="">Tất cả</option>
        <option value="1">Giày Nam</option>
        <option value="2">Giày Nữ</option>
      </select>

      <label>Thương hiệu</label>
      <select
        className="form-select mb-2"
        value={filters.brand}
        onChange={(e) => handleChange("brand", e.target.value)}
      >
        <option value="">Tất cả</option>
        <option value="1">Nike</option>
        <option value="2">Adidas</option>
      </select>

      <label>Giá từ</label>
      <input
        type="number"
        className="form-control mb-2"
        value={filters.price_min}
        onChange={(e) => handleChange("price_min", e.target.value)}
      />

      <label>Đến</label>
      <input
        type="number"
        className="form-control mb-2"
        value={filters.price_max}
        onChange={(e) => handleChange("price_max", e.target.value)}
      />
    </div>
  );
};

export default FilterSidebar;
