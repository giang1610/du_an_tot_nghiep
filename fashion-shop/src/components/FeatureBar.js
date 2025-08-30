const features = [
  { icon: "flaticon-money", title: "Money back guarantee", desc: "Shall open divide a one" },
  { icon: "flaticon-truck", title: "Free Delivery", desc: "Shall open divide a one" },
  { icon: "flaticon-support", title: "Always Support", desc: "Shall open divide a one" },
  { icon: "flaticon-blockchain", title: "Secure Payment", desc: "Shall open divide a one" },
];

export default function FeatureBar() {
  return (
    <section className="feature-area section_gap_bottom_custom">
      <div className="container">
        <div className="row">
          {features.map((f, i) => (
            <div className="col-lg-3 col-md-6" key={i}>
              <div className="single-feature">
                <a href="#" className="title">
                  <i className={f.icon}></i>
                  <h3>{f.title}</h3>
                </a>
                <p>{f.desc}</p>
              </div>
            </div>
          ))}
        </div>
      </div>
    </section>
  );
}
    