import React, { useEffect, useState } from "react";
import API from "../services/api";
import ProductCard from "../components/ProductCard";
import ProductFilter from "../components/ProductFilter";

const AllProducts = () => {
  const [products, setProducts] = useState([]);
  const [searchTerm, setSearchTerm] = useState("");
  const [sizeFilter, setSizeFilter] = useState("");
  const [colorFilter, setColorFilter] = useState("");
  const [categoryFilter, setCategoryFilter] = useState("");
  const [priceRange, setPriceRange] = useState({ min: "", max: "" });

  useEffect(() => {
    API.get("/products")
      .then((res) => {
         console.log("API response:", res.data); 
        setProducts(res.data.data);
      })
      .catch(console.error);
  }, []);

  // Lấy danh sách size, màu, danh mục
  const allSizes = Array.from(new Set(products.flatMap((p) => p.sizes))).sort();
  const allColors = Array.from(new Set(products.flatMap((p) => p.colors))).sort();
  const allCategories = Array.from(new Set(products.map((p) => p.category))).sort();

  const filteredProducts = products.filter((product) => {
    const matchSearch = product.name.toLowerCase().includes(searchTerm.toLowerCase());
    const matchSize = sizeFilter ? product.sizes.includes(sizeFilter) : true;
    const matchColor = colorFilter ? product.colors.includes(colorFilter) : true;
    const matchCategory = categoryFilter ? product.category === categoryFilter : true;

    // Lọc theo khoảng giá
    const minPrice = priceRange.min ? parseFloat(priceRange.min) : 0;
    const maxPrice = priceRange.max ? parseFloat(priceRange.max) : Infinity;
    const matchPrice = product.price >= minPrice && product.price <= maxPrice;

    return matchSearch && matchSize && matchColor && matchCategory && matchPrice;
  });

  return (
    <div className="container my-4">
      <h2>Tất cả sản phẩm</h2>
      <ProductFilter
        searchTerm={searchTerm}
        onSearchChange={setSearchTerm}
        sizeFilter={sizeFilter}
        onSizeChange={setSizeFilter}
        colorFilter={colorFilter}
        onColorChange={setColorFilter}
        categoryFilter={categoryFilter}
        onCategoryChange={setCategoryFilter}
        priceRange={priceRange}
        onPriceRangeChange={setPriceRange}
        availableSizes={allSizes}
        availableColors={allColors}
        availableCategories={allCategories}
      />
      <div className="row">
        {filteredProducts.length > 0 ? (
          filteredProducts.map((product) => (
            <ProductCard key={product.id} product={product} />
          ))
        ) : (
          <p className="text-center mt-4">Không có sản phẩm phù hợp.</p>
        )}
      </div>
    </div>
  );
};

export default AllProducts;
