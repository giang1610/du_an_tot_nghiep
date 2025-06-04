import React from "react";

const ProductFilter = ({
  searchTerm,
  onSearchChange,
  sizeFilter,
  onSizeChange,
  colorFilter,
  onColorChange,
  categoryFilter,
  onCategoryChange,
  priceRange,
  onPriceRangeChange,
  availableSizes,
  availableColors,
  availableCategories,
}) => {
  return (
    <div className="mb-4">
      <div className="row g-3">
        <div className="col-md-3">
          <input
            type="text"
            className="form-control"
            placeholder="Tìm kiếm sản phẩm..."
            value={searchTerm}
            onChange={(e) => onSearchChange(e.target.value)}
          />
        </div>
        <div className="col-md-2">
          <select
            className="form-select"
            value={categoryFilter}
            onChange={(e) => onCategoryChange(e.target.value)}
          >
            <option value="">Chọn danh mục</option>
            {availableCategories.map((cat) => (
              <option key={cat} value={cat}>
                {cat}
              </option>
            ))}
          </select>
        </div>
        <div className="col-md-2">
          <select
            className="form-select"
            value={sizeFilter}
            onChange={(e) => onSizeChange(e.target.value)}
          >
            <option value="">Chọn size</option>
            {availableSizes.map((size) => (
              <option key={size} value={size}>
                {size}
              </option>
            ))}
          </select>
        </div>
        <div className="col-md-2">
          <select
            className="form-select"
            value={colorFilter}
            onChange={(e) => onColorChange(e.target.value)}
          >
            <option value="">Chọn màu</option>
            {availableColors.map((color) => (
              <option key={color} value={color}>
                {color}
              </option>
            ))}
          </select>
        </div>
        <div className="col-md-3 d-flex">
          <input
            type="number"
            className="form-control me-2"
            placeholder="Giá từ (VNĐ)"
            value={priceRange.min}
            onChange={(e) =>
              onPriceRangeChange({ ...priceRange, min: e.target.value })
            }
          />
          <input
            type="number"
            className="form-control"
            placeholder="Đến (VNĐ)"
            value={priceRange.max}
            onChange={(e) =>
              onPriceRangeChange({ ...priceRange, max: e.target.value })
            }
          />
        </div>
        <div className="col-md-12 mt-2">
          <button
            className="btn btn-secondary w-100"
            onClick={() => {
              onSearchChange("");
              onSizeChange("");
              onColorChange("");
              onCategoryChange("");
              onPriceRangeChange({ min: "", max: "" });
            }}
          >
            Xóa bộ lọc
          </button>
        </div>
      </div>
    </div>
  );
};

export default ProductFilter;
