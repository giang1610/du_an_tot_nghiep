export default function AboutPage() {
  return (
    <>
      <style>{`
        /* Animation fade in/out nhẹ cho tiêu đề */
        @keyframes pulseTitle {
          0%, 100% { opacity: 1; transform: scale(1); }
          50% { opacity: 0.8; transform: scale(1.05); }
        }

        /* Animation fade in cho nút */
        @keyframes fadeInBtn {
          from { opacity: 0; }
          to { opacity: 1; }
        }

        h2 {
          animation: pulseTitle 3s ease-in-out infinite;
          color: #007bff;
          font-weight: 700;
          margin-bottom: 1.5rem;
          text-align: center;
        }

        .about-image {
          max-height: 400px;
          object-fit: cover;
          border-radius: 12px;
          box-shadow: 0 8px 20px rgba(0,0,0,0.15);
          transition: transform 0.3s ease;
          display: block;
          margin-left: auto;
          margin-right: auto;
        }
        .about-image:hover {
          transform: scale(1.05);
        }
        .btn-custom {
          background-color: #007bff;
          border: none;
          padding: 12px 30px;
          font-size: 1.2rem;
          border-radius: 8px;
          transition: background-color 0.3s ease, box-shadow 0.3s ease, transform 0.3s ease;
          animation: fadeInBtn 1.5s ease forwards;
          display: inline-block;
          margin: 0 auto;
          cursor: pointer;
          color: white;
          text-decoration: none;
          text-align: center;
        }
        .btn-custom:hover {
          background-color: #0056b3;
          box-shadow: 0 6px 12px rgba(0,123,255,0.6);
          transform: scale(1.1);
          text-decoration: none;
          color: white;
        }
      `}</style>

      <div className="container py-5">
        <h2>Giới thiệu về chúng tôi</h2>
        <p>
          <strong>MG Fashion</strong> tự hào là thương hiệu thời trang mang đến cho bạn những 
          sản phẩm quần áo chất lượng cao, thiết kế tinh tế và bắt kịp xu hướng. 
          Chúng tôi tin rằng mỗi bộ trang phục không chỉ đơn thuần là quần áo, 
          mà còn là cách bạn thể hiện cá tính và phong cách sống của riêng mình.
        </p>
        <p>
          Với tiêu chí <em>“Đẹp – Chất – Giá hợp lý”</em>, MG luôn chọn lọc chất liệu tốt, 
          quy trình may tỉ mỉ và chú trọng từng đường kim mũi chỉ. Từ những bộ đồ công sở thanh lịch 
          đến trang phục dạo phố trẻ trung, chúng tôi luôn mong muốn mang đến trải nghiệm mua sắm 
          tuyệt vời nhất cho khách hàng.
        </p>
        <p>
          MG không ngừng cập nhật xu hướng mới, kết hợp giữa phong cách hiện đại và sự thoải mái 
          khi mặc, để bạn luôn tự tin tỏa sáng ở bất cứ đâu.
        </p>
        <p>
          Cảm ơn bạn đã đồng hành cùng MG trên hành trình lan tỏa vẻ đẹp và phong cách!
        </p>

        <div className="text-center my-4">
          <img
            src="https://images.unsplash.com/photo-1521334884684-d80222895322?auto=format&fit=crop&w=800&q=80"
            alt="MG Fashion"
            className="about-image img-fluid"
          />
        </div>

        <div className="text-center">
          <a href="/products" className="btn btn-custom">
            Khám phá bộ sưu tập
          </a>
        </div>
      </div>
    </>
  );

}
