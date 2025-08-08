
import React, { useEffect, useState } from "react";
import { Link } from "react-router-dom";

export default function CategoryBanner() {
  const [categories, setCategories] = useState([]);

  useEffect(() => {
    fetch("http://localhost:8000/api/categories")
      .then((r) => r.json())
      .then((data) => {
        // Accept either array or { success,data }
        const arr = Array.isArray(data) ? data : data?.data ?? data;
        setCategories(Array.isArray(arr) ? arr : []);
      })
      .catch((err) => {
        console.error("Lỗi lấy categories:", err);
      });
  }, []);

  return (
    <section className="py-5">
      <div className="container">
        <div className="text-center mb-4">
          <h3 className="fw-bold">Mua sắm theo danh mục</h3>
          <p className="text-muted">Khám phá các bộ sưu tập nổi bật</p>
        </div>

        <div className="row g-3">
          {categories.length === 0 && (
            <>
              {/* fallback: placeholder 6 */}
              {["Thời trang nam","Thời trang nữ","Phụ kiện","Giày dép","Túi xách","Đồng hồ"].map((name,i)=>(
                <div className="col-6 col-md-4 col-lg-2" key={i}>
                  <div className="d-block text-center p-4 rounded-4 shadow-sm text-decoration-none category-item">
                    <h6 className="mb-0 fw-semibold text-dark">{name}</h6>
                  </div>
                </div>
              ))}
            </>
          )}

          {categories.map((cat) => (
            <div className="col-6 col-md-4 col-lg-2" key={cat.id}>
              <Link
                to={`/category/${cat.slug}`}
                className="d-block text-center p-4 rounded-4 shadow-sm text-decoration-none category-item"
              >
                <h6 className="mb-0 fw-semibold text-dark">{cat.name}</h6>
              </Link>
            </div>
          ))}
        </div>
      </div>

      <style>{`
        .category-item {
          background-color: #f8f9fa;
          transition: all .25s ease;
        }
        .category-item:hover {
          background-color: #0aad0a;
          color: #fff !important;
          transform: translateY(-4px);
        }
      `}</style>
    </section>
  );
}
