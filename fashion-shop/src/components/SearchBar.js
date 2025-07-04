import { useEffect, useState } from 'react';
import { useNavigate } from 'react-router-dom';
import axios from 'axios';
import { Form, Button, ListGroup, InputGroup } from 'react-bootstrap';
import { FaSearch, FaTimes } from 'react-icons/fa';

export default function SearchBar() {
  const [search, setSearch] = useState('');
  const [suggestions, setSuggestions] = useState([]);
  const [history, setHistory] = useState([]);
  const [showDropdown, setShowDropdown] = useState(false);
  const navigate = useNavigate();

  useEffect(() => {
    const saved = JSON.parse(localStorage.getItem('search_history')) || [];
    setHistory(saved);
  }, []);

  useEffect(() => {
    if (search.length >= 2) {
      axios.get(`${process.env.REACT_APP_API_URL}/products`, {
        params: { search }
      }).then(res => {
        setSuggestions(res.data.data.slice(0, 5));
        setShowDropdown(true);
      });
    } else {
      setSuggestions([]);
    }
  }, [search]);

  const saveToHistory = (term) => {
    let updated = [term, ...history.filter(item => item !== term)];
    if (updated.length > 5) updated = updated.slice(0, 5);
    setHistory(updated);
    localStorage.setItem('search_history', JSON.stringify(updated));
  };

  const handleSubmit = (e) => {
    e.preventDefault();
    if (search.trim()) {
      saveToHistory(search.trim());
      navigate(`/products?search=${encodeURIComponent(search.trim())}`);
      setShowDropdown(false);
    }
  };

  const handleSuggestionClick = (text) => {
    saveToHistory(text);
    navigate(`/products?search=${encodeURIComponent(text)}`);
    setShowDropdown(false);
  };

  const handleRemoveHistory = (term) => {
    const updated = history.filter(item => item !== term);
    setHistory(updated);
    localStorage.setItem('search_history', JSON.stringify(updated));
  };

  const handleClearAllHistory = () => {
    setHistory([]);
    localStorage.removeItem('search_history');
  };

  return (
    <div style={{ position: 'relative', width: '300px' }}>
      <Form onSubmit={handleSubmit}>
        <InputGroup>
          <Form.Control
            type="text"
            placeholder="Tìm sản phẩm..."
            value={search}
            onChange={(e) => setSearch(e.target.value)}
            onFocus={() => setShowDropdown(true)}
          />
          <Button type="submit" variant="outline-dark">
            <FaSearch />
          </Button>
        </InputGroup>
      </Form>

      {(showDropdown && (suggestions.length > 0 || history.length > 0)) && (
        <ListGroup style={{
          position: 'absolute',
          top: '100%',
          zIndex: 1000,
          width: '100%',
          maxHeight: 300,
          overflowY: 'auto'
        }}>
          {search === '' && history.map((item, i) => (
            <ListGroup.Item key={i} className="d-flex justify-content-between align-items-center">
              <span role="button" onClick={() => handleSuggestionClick(item)}>
                🔍 {item}
              </span>
              <FaTimes
                size={12}
                className="text-muted"
                role="button"
                onClick={() => handleRemoveHistory(item)}
              />
            </ListGroup.Item>
          ))}

          {search === '' && history.length > 0 && (
            <ListGroup.Item
              action
              className="text-center text-danger"
              onClick={handleClearAllHistory}
            >
              Xoá tất cả lịch sử
            </ListGroup.Item>
          )}

          {search && suggestions.map((item) => (
            <ListGroup.Item key={item.id} action onClick={() => handleSuggestionClick(item.name)}>
              <div className="d-flex align-items-center gap-2">
                <img src={item.images?.[0]?.url || 'placeholder.jpg'} width="40" height="40" alt="" />
                <span>{item.name}</span>
              </div>
            </ListGroup.Item>
          ))}
        </ListGroup>
      )}
    </div>
  );
}
