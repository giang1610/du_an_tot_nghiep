import React, { useState } from 'react';
import { useNavigate, useSearchParams } from 'react-router-dom';
// Step 1: Import the icons from react-icons
import { FaEye, FaEyeSlash } from 'react-icons/fa';

const styles = {
  container: {
    display: 'flex',
    justifyContent: 'center',
    alignItems: 'center',
    minHeight: '80vh',
    backgroundColor: '#f4f7f6',
    fontFamily: "-apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif",
  },
  form: {
    background: '#ffffff',
    padding: '40px',
    borderRadius: '12px',
    boxShadow: '0 8px 30px rgba(0, 0, 0, 0.1)',
    width: '100%',
    maxWidth: '420px',
    boxSizing: 'border-box',
  },
  h2: {
    textAlign: 'center',
    color: '#333',
    marginBottom: '30px',
    fontWeight: 600,
  },
  formGroup: {
    marginBottom: '20px',
    position: 'relative',
  },
  label: {
    display: 'block',
    marginBottom: '8px',
    color: '#555',
    fontWeight: 500,
    fontSize: '14px',
  },
  formControl: {
    width: '100%',
    padding: '12px 45px 12px 15px',
    border: '1px solid #ccc',
    borderRadius: '8px',
    fontSize: '16px',
    boxSizing: 'border-box',
  },
  // Step 2: Adjusted styles for better icon alignment
  toggleBtn: {
    position: 'absolute',
    right: '15px',
    top: '40px',
    background: 'none',
    border: 'none',
    color: '#888',
    cursor: 'pointer',
    fontSize: '16px',
    padding: 0,
  },
  submitBtn: {
    width: '100%',
    padding: '14px',
    backgroundColor: '#007bff',
    color: 'white',
    border: 'none',
    borderRadius: '8px',
    fontSize: '16px',
    fontWeight: 600,
    cursor: 'pointer',
    marginTop: '10px',
    transition: 'background-color 0.2s, transform 0.1s',
  },
  submitBtnHover: {
    backgroundColor: '#0056b3',
  },
  submitBtnDisabled: {
    backgroundColor: '#a0c9ff',
    cursor: 'not-allowed',
  },
  message: {
    padding: '12px',
    marginBottom: '20px',
    borderRadius: '8px',
    textAlign: 'center',
    fontSize: '15px',
    lineHeight: 1.4,
  },
  messageSuccess: {
    backgroundColor: '#d4edda',
    color: '#155724',
    border: '1px solid #c3e6cb',
  },
  messageError: {
    backgroundColor: '#f8d7da',
    color: '#721c24',
    border: '1px solid #f5c6cb',
  },
};

function ResetPassword() {
  const [params] = useSearchParams();
  const token = params.get('token');
  const email = params.get('email');

  const [password, setPassword] = useState('');
  const [passwordConfirm, setPasswordConfirm] = useState('');
  const [showPassword, setShowPassword] = useState(false);
  const [showPasswordConfirm, setShowPasswordConfirm] = useState(false);
  const [message, setMessage] = useState({ text: '', type: '' });
  const [loading, setLoading] = useState(false);
  const [isHovered, setIsHovered] = useState(false);

  const navigate = useNavigate();

  const handleSubmit = async (e) => {
    e.preventDefault();
    setLoading(true);
    setMessage({ text: '', type: '' });

    if (password !== passwordConfirm) {
      setMessage({ text: '❌ Mật khẩu xác nhận không khớp.', type: 'error' });
      setLoading(false);
      return;
    }

    try {
      const res = await fetch(`${process.env.REACT_APP_API_URL}/reset-password`, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
        },
        body: JSON.stringify({
          token: token,
          email: email,
          password: password,
          password_confirmation: passwordConfirm,
        }),
      });

      const data = await res.json();

      if (res.ok) {
        setMessage({ text: '✅ Mật khẩu đã được đặt lại thành công! Đang chuyển hướng...', type: 'success' });
        localStorage.clear();
        setTimeout(() => {
          navigate('/login');
        }, 2000);
      } else {
        const errorMessage = data.errors?.password?.[0] || data.message || 'Không thể đặt lại mật khẩu.';
        setMessage({ text: `❌ Lỗi: ${errorMessage}`, type: 'error' });
      }
    } catch (error) {
      setMessage({ text: '❌ Kết nối thất bại. Vui lòng thử lại.', type: 'error' });
    } finally {
      setLoading(false);
    }
  };

  const messageStyle = {
    ...styles.message,
    ...(message.type === 'success' && styles.messageSuccess),
    ...(message.type === 'error' && styles.messageError),
  };

  const buttonStyle = {
    ...styles.submitBtn,
    ...(isHovered && !loading ? styles.submitBtnHover : {}),
    ...(loading ? styles.submitBtnDisabled : {}),
  };

  return (
    <div style={styles.container}>
      <form style={styles.form} onSubmit={handleSubmit}>
        <h2 style={styles.h2}>Đặt lại mật khẩu</h2>

        <div style={styles.formGroup}>
          <label htmlFor="password" style={styles.label}>Mật khẩu mới</label>
          <input
            id="password"
            type={showPassword ? 'text' : 'password'}
            placeholder="Nhập mật khẩu mới của bạn"
            value={password}
            onChange={(e) => setPassword(e.target.value)}
            required
            style={styles.formControl}
          />
          {/* Step 3: Replace text with icons */}
          <button
            type="button"
            style={styles.toggleBtn}
            onClick={() => setShowPassword(!showPassword)}
          >
            {showPassword ? <FaEyeSlash /> : <FaEye />}
          </button>
        </div>

        <div style={styles.formGroup}>
          <label htmlFor="password-confirm" style={styles.label}>Xác nhận mật khẩu</label>
          <input
            id="password-confirm"
            type={showPasswordConfirm ? 'text' : 'password'}
            placeholder="Nhập lại mật khẩu"
            value={passwordConfirm}
            onChange={(e) => setPasswordConfirm(e.target.value)}
            required
            style={styles.formControl}
          />
          {/* Step 3: Replace text with icons */}
          <button
            type="button"
            style={styles.toggleBtn}
            onClick={() => setShowPasswordConfirm(!showPasswordConfirm)}
          >
            {showPasswordConfirm ? <FaEyeSlash /> : <FaEye />}
          </button>
        </div>

        {message.text && <p style={messageStyle}>{message.text}</p>}

        <button
          type="submit"
          style={buttonStyle}
          disabled={loading}
          onMouseEnter={() => setIsHovered(true)}
          onMouseLeave={() => setIsHovered(false)}
        >
          {loading ? 'Đang xử lý...' : 'Xác nhận'}
        </button>
      </form>
    </div>
  );
}

export default ResetPassword;