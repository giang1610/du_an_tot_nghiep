import 'bootstrap/dist/css/bootstrap.min.css';

export default function AboutPage() {
  return (
    <div className="container py-5">
      {/* Banner section */}
      <div className="row mb-5">
        <div className="col-12">
          <div className="bg-primary bg-gradient rounded-4 p-4 p-md-5 d-flex flex-column flex-md-row align-items-center justify-content-between shadow-sm">
            <div className="text-white mb-4 mb-md-0" style={{ maxWidth: 600 }}>
              <h1 className="display-5 fw-bold mb-3">MG Fashion – Khẳng định phong cách riêng</h1>
              <p className="lead mb-3">
                MG Fashion là thương hiệu thời trang hiện đại, mang đến cho bạn những sản phẩm chất lượng cao, thiết kế tinh tế và luôn cập nhật xu hướng mới nhất.
              </p>
              <a href="/products" className="btn btn-light btn-lg px-4 fw-bold shadow-sm">
                Khám phá sản phẩm
              </a>
            </div>
            <div className="ms-md-5 flex-shrink-0">
              <img
                src="https://images.unsplash.com/photo-1521334884684-d80222895322?auto=format&fit=crop&w=800&q=80"
                alt="MG Fashion"
                className="rounded-4 shadow-lg"
                style={{ width: 320, maxWidth: '100%', height: 'auto', objectFit: 'cover' }}
              />
            </div>
          </div>
        </div>
      </div>

      {/* Info + Core values section */}
      <div className="row g-4">
        <div className="col-lg-7">
          <div className="bg-white rounded-4 shadow-sm p-4 h-100">
            <h2 className="fw-bold text-primary mb-3">Về chúng tôi</h2>
            <p className="mb-3 text-secondary">
              Chúng tôi tin rằng thời trang không chỉ là quần áo, mà còn là cách bạn thể hiện cá tính, phong cách sống và sự tự tin của bản thân. MG Fashion luôn đặt khách hàng làm trung tâm, lắng nghe và thấu hiểu để mang đến những trải nghiệm tốt nhất.
            </p>
            <ul className="list-group list-group-flush mb-4">
              <li className="list-group-item bg-transparent ps-0 border-0">
                <span className="fw-semibold text-dark">Chất liệu cao cấp</span> – Đảm bảo sự thoải mái và bền đẹp.
              </li>
              <li className="list-group-item bg-transparent ps-0 border-0">
                <span className="fw-semibold text-dark">Thiết kế đa dạng</span> – Phù hợp mọi phong cách, mọi lứa tuổi.
              </li>
              <li className="list-group-item bg-transparent ps-0 border-0">
                <span className="fw-semibold text-dark">Giá cả hợp lý</span> – Mang lại giá trị tốt nhất cho khách hàng.
              </li>
              <li className="list-group-item bg-transparent ps-0 border-0">
                <span className="fw-semibold text-dark">Dịch vụ tận tâm</span> – Hỗ trợ khách hàng nhanh chóng, chu đáo.
              </li>
            </ul>
            <p className="mb-3 text-secondary">
              Đội ngũ thiết kế của MG Fashion luôn sáng tạo, cập nhật xu hướng mới nhất trên thế giới để mang đến cho bạn những bộ sưu tập độc đáo, phù hợp với mọi dịp và mọi đối tượng khách hàng.
            </p>
            <p className="mb-3 text-secondary">
              Ngoài ra, chúng tôi còn chú trọng đến dịch vụ chăm sóc khách hàng, chính sách đổi trả linh hoạt và các chương trình ưu đãi hấp dẫn dành cho thành viên thân thiết. MG Fashion mong muốn trở thành người bạn đồng hành cùng bạn trên hành trình khẳng định phong cách riêng.
            </p>
          </div>
        </div>
        <div className="col-lg-5">
          <div className="bg-light rounded-4 shadow-sm p-4 h-100 d-flex flex-column justify-content-center">
            <h4 className="fw-bold text-primary mb-3">Giá trị cốt lõi</h4>
            <ul className="mb-3 ps-3">
              <li>Khách hàng là trung tâm của mọi hoạt động.</li>
              <li>Luôn đổi mới, sáng tạo và dẫn đầu xu hướng.</li>
              <li>Chất lượng sản phẩm và dịch vụ là ưu tiên hàng đầu.</li>
              <li>Đội ngũ chuyên nghiệp, tận tâm và nhiệt huyết.</li>
              <li>Chính sách minh bạch, bảo vệ quyền lợi khách hàng.</li>
            </ul>
            <h5 className="fw-bold text-primary mb-2 mt-4">Sứ mệnh của MG Fashion</h5>
            <p className="mb-0 text-secondary">
              Lan tỏa vẻ đẹp, sự tự tin và phong cách sống hiện đại đến mọi khách hàng. MG Fashion không ngừng sáng tạo, đổi mới để mang lại trải nghiệm mua sắm tuyệt vời nhất.
              <br />
              <br />
              Chúng tôi cam kết mang đến những sản phẩm chất lượng, dịch vụ chuyên nghiệp và không ngừng phát triển để đáp ứng nhu cầu ngày càng cao của khách hàng.
              Hãy để MG Fashion giúp bạn tỏa sáng mỗi ngày!
            </p>
            <div className="mt-4">
              <span className="text-secondary">
                Mọi ý kiến đóng góp xin gửi về <a href="/contact" className="text-primary fw-semibold">trang liên hệ</a> của chúng tôi.
              </span>
            </div>
          </div>
        </div>
      </div>
    </div>
  );
}
