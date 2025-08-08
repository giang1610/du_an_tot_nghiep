import { Card } from "react-bootstrap";
import { Link } from "react-router-dom";

export default function CategoryCard({ category }) {
  return (
    <Card className="text-center border-0 shadow-sm h-100">
      <Link to={`/category/${category.id}`} className="text-decoration-none">
        <Card.Img
          variant="top"
          src={category.image}
          alt={category.name}
          style={{ height: "120px", objectFit: "cover" }}
        />
        <Card.Body>
          <Card.Title className="fs-6 text-dark">{category.name}</Card.Title>
        </Card.Body>
      </Link>
    </Card>
  );
}
