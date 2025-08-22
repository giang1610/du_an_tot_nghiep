import ProductCard from './ProductCard';

export default function ProductSection({ title, products }) {
  return (
    <div className="container py-5">
      <div className="d-flex justify-content-between align-items-center mb-4">
        <h3 className="fw-bold">{title}</h3>
        <a href="/products" className="text-decoration-none text-primary fw-semibold">Xem tất cả →</a>
      </div>
      <div className="row g-4">
        {products.map((product) => (
          <div className="col-6 col-md-3" key={product.id}>
            <ProductCard product={product} />
          </div>
        ))}
      </div>
    </div>
  );
}