import { useState, useCallback } from 'react';
import { Form, Button, Alert, InputGroup, Spinner } from 'react-bootstrap';
import { useNavigate, Link } from 'react-router-dom';
import { Eye, EyeSlash } from 'react-bootstrap-icons';
import '../../src/css/regis.css'; // Ensure this path is correct

import { toast, ToastContainer } from 'react-toastify';
import 'react-toastify/dist/ReactToastify.css';

// 1. Import SweetAlert2
import Swal from 'sweetalert2';
import withReactContent from 'sweetalert2-react-content';

// 2. Create an instance
const MySwal = withReactContent(Swal);

export default function Register() {
  const [form, setForm] = useState({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
  });
  const [error, setError] = useState('');
  const [loading, setLoading] = useState(false);
  const [showPassword, setShowPassword] = useState(false);
  const [showConfirmPassword, setShowConfirmPassword] = useState(false);
  
  // 3. The Modal state is no longer needed
  // const [showSuccessModal, setShowSuccessModal] = useState(false);

  const navigate = useNavigate();

  const handleChange = useCallback((e) => {
    const { name, value } = e.target;
    setForm((prev) => ({ ...prev, [name]: value }));
    setError('');
  }, []);

  const handleSubmit = async (e) => {
    e.preventDefault();
    setError('');
    setLoading(true);

    if (form.password !== form.password_confirmation) {
      setError('Mật khẩu và xác nhận mật khẩu không khớp');
      setLoading(false);
      return;
    }

    try {
      const res = await fetch(`${process.env.REACT_APP_API_URL}/register`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(form),
      });

      const data = await res.json();

      if (res.ok) {
        MySwal.fire({
          title: 'Đăng Ký Thành Công!',
          text: 'Vui lòng kiểm tra email để xác thực. Đang chuyển hướng...',
          icon: 'success',
          timer: 2000, 
          timerProgressBar: true,
          showConfirmButton: false,
          didClose: () => {
            navigate('/login');
          }
        });
        setForm({ name: '', email: '', password: '', password_confirmation: '' });
      } else {
        let message = 'Đăng ký thất bại';
        if (data?.errors) {
          const firstError = Object.values(data.errors)[0][0];
          message = firstError;
        } else if (data?.message) {
          message = data.message;
        }
        setError(message);
      }
    } catch {
      toast.warn('Không thể kết nối đến máy chủ');
    } finally {
      setLoading(false);
    }
  };

  return (
    // 5. The Modal component is removed from the JSX
    <div className="register-container-wrapper">
      <div className="register-card">
        <h3 className="form-title">Đăng Ký Tài Khoản</h3>

        {error && (
          <Alert variant="danger" onClose={() => setError('')} dismissible>
            {error}
          </Alert>
        )}
          <div className="img_card">
          <div className="img_card_content">
            <img src='https://cellphones.com.vn/sforum/wp-content/uploads/2023/12/hinh-nen-xanh-duong-15.jpg'></img>
         </div>
        <div>
          <Form onSubmit={handleSubmit} noValidate>
          <Form.Group className="mb-3 " controlId="registerName" >
            <Form.Label>Họ tên</Form.Label>
            <Form.Control className="border border-black" type="text" name="name"  value={form.name} onChange={handleChange} required placeholder="user name" disabled={loading} minLength={3}/>
          </Form.Group>

          <Form.Group className="mb-3" controlId="registerEmail">
            <Form.Label>Địa chỉ Email</Form.Label>
            <Form.Control className="border border-black" type="email" name="email" value={form.email} onChange={handleChange} required placeholder="example@gmail.com" disabled={loading}/>
          </Form.Group>

          <Form.Group className="mb-3" controlId="registerPassword">
            <Form.Label>Mật khẩu</Form.Label>
            <InputGroup>
              <Form.Control className="border border-black" type={showPassword ? 'text' : 'password'} name="password" value={form.password} onChange={handleChange} required placeholder="Tối thiểu 8 ký tự" disabled={loading} minLength={6}/>
              <Button className="border border-black" variant="outline-secondary" onClick={() => setShowPassword((v) => !v)} tabIndex={-1} disabled={loading}>
                {showPassword ? <EyeSlash /> : <Eye />}
              </Button>
            </InputGroup>
          </Form.Group>

          <Form.Group className="mb-4" controlId="registerPasswordConfirm">
            <Form.Label>Xác nhận mật khẩu</Form.Label>
            <InputGroup>
              <Form.Control className="border border-black" type={showConfirmPassword ? 'text' : 'password'} name="password_confirmation" value={form.password_confirmation} onChange={handleChange} required placeholder="Nhập lại mật khẩu của bạn" disabled={loading} minLength={6}/>
              <Button className="border border-black" variant="outline-secondary" onClick={() => setShowConfirmPassword((v) => !v)} tabIndex={-1} disabled={loading}>
                {showConfirmPassword ? <EyeSlash /> : <Eye />}
              </Button>
            </InputGroup>
          </Form.Group>

          <Button type="submit" className="w-100 btn-submit-custom" disabled={loading}>
            {loading ? (
              <>
                <Spinner as="span" animation="border" size="sm" role="status" aria-hidden="true" />
                {' '}Đang xử lý...
              </>
            ) : (
              'Đăng Ký'
            )}
          </Button>
        </Form>
        </div>
          </div>
       
         <ToastContainer position="top-right" className="p-5" autoClose={2000} hideProgressBar={false} />

        <div className="login-prompt">
          Đã có tài khoản? <Link to="/login">Đăng nhập ngay</Link>
        </div>
      </div>
    </div>
  );
}