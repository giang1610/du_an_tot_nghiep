// src/components/BlogSection.js
import React from "react";

const BlogSection = () => {
  const blogs = [
    {
      id: 1,
      title: "Xu hướng thời trang 2025",
      description: "Khám phá các xu hướng mới nhất trong ngành thời trang năm nay.",
      image: "https://via.placeholder.com/400x250",
    },
    {
      id: 2,
      title: "Cách phối đồ đơn giản",
      description: "Một số tips nhỏ giúp bạn luôn tự tin và phong cách.",
      image: "https://via.placeholder.com/400x250",
    },
    {
      id: 3,
      title: "Mẹo chọn phụ kiện",
      description: "Hướng dẫn chọn phụ kiện phù hợp với từng loại trang phục.",
      image: "https://via.placeholder.com/400x250",
    },
  ];

  return (
    <section className="container my-5">
      <h2 className="text-center mb-4">Blog thời trang</h2>
      <div className="row">
        {blogs.map((blog) => (
          <div className="col-md-4 mb-4" key={blog.id}>
            <div className="card h-100 shadow-sm">
              <img src={blog.image} className="card-img-top" alt={blog.title} />
              <div className="card-body">
                <h5 className="card-title">{blog.title}</h5>
                <p className="card-text">{blog.description}</p>
                <a href="#" className="btn btn-dark">
                  Đọc thêm
                </a>
              </div>
            </div>
          </div>
        ))}
      </div>
    </section>
  );
};

export default BlogSection;
