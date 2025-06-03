const SearchBar = ({ onSearch }) => {
  return (
    <input
      type="text"
      className="form-control"
      placeholder="Tìm kiếm sản phẩm..."
      onChange={(e) => onSearch(e.target.value)}
    />
  );
};

export default SearchBar;
