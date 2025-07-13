import React, { useState, useEffect } from 'react';
import axios from '../api/axios';
import { useAuth } from '../context/AuthContext';

export default function ChatApp() {
  const [isOpen, setIsOpen] = useState(false);
  const [avatar, setAvatar] = useState('');
  const [message, setMessage] = useState('');
  const { token } = useAuth();

  const adminAvatar = 'https://img.freepik.com/premium-vector/man-avatar-profile-picture-isolated-background-avatar-profile-picture-man_1293239-4841.jpg';

  useEffect(() => {
    const userData = localStorage.getItem('user');
    if (userData) {
      try {
        const user = JSON.parse(userData);
        if (user.img_thumbnail) {
          setAvatar(`${process.env.REACT_APP_IMAGE_BASE_URL}/storage/${user.img_thumbnail}`);
        } else {
          setAvatar(adminAvatar);
        }
      } catch (err) {
        console.error('Lỗi parse user:', err);
        setAvatar(adminAvatar);
      }
    } else {
      setAvatar(adminAvatar);
    }
  }, []);

  const handleSend = async () => {
    if (!message.trim()) return;

    try {
      await axios.post(
        '/chat/send',
        { message },
        {
          headers: {
            Authorization: `Bearer ${token}`,
          },
        }
      );
      setMessage('');
      // Có thể thêm push tin nhắn vào state nếu muốn hiển thị real-time
    } catch (error) {
      console.error('❌ Lỗi gửi tin nhắn:', error);
    }
  };

  return (
    <>
      {/* Nút mở chat */}
      {!isOpen && (
        <button
          className="btn btn-primary position-fixed bottom-0 end-0 m-4 rounded-circle shadow"
          style={{ width: 60, height: 60, zIndex: 1050 }}
          onClick={() => setIsOpen(true)}
        >
          💬
        </button>
      )}

      {/* Khung chat */}
      {isOpen && (
        <div
          className="position-fixed bottom-0 end-0 m-4 bg-white border rounded shadow"
          style={{
            width: 420,
            maxWidth: '95vw',
            zIndex: 1040,
            height: 600,
            display: 'flex',
            flexDirection: 'column',
          }}
        >
          {/* Header */}
          <div className="border-bottom p-2 d-flex justify-content-between align-items-center">
            <strong>Trò chuyện</strong>
            <button className="btn btn-sm btn-danger" onClick={() => setIsOpen(false)}>
              ✖
            </button>
          </div>

          {/* Nội dung chat (admin nhắn trước) */}
          <div className="flex-grow-1 p-3 overflow-auto" style={{ background: '#f8f9fa' }}>
            <div className="d-flex mb-3">
              <img
                src={adminAvatar}
                alt="Admin"
                className="rounded-circle me-2"
                style={{ width: 40, height: 40, objectFit: 'cover' }}
              />
              <div>
                <div className="bg-light p-2 rounded">Xin chào! Tôi có thể giúp gì cho bạn?</div>
                <div className="text-muted small mt-1">10:00</div>
              </div>
            </div>
          </div>

          {/* Footer nhập tin nhắn */}
          <div className="border-top p-2 d-flex align-items-center">
            <img
              src={avatar}
              alt="Avatar người dùng"
              className="rounded-circle me-2"
              style={{ width: 40, height: 40, objectFit: 'cover' }}
            />
            <input
              type="text"
              className="form-control me-2"
              placeholder="Nhập tin nhắn..."
              value={message}
              onChange={(e) => setMessage(e.target.value)}
              onKeyDown={(e) => e.key === 'Enter' && handleSend()}
            />
            <button className="btn btn-primary" onClick={handleSend}>
              Gửi
            </button>
          </div>
        </div>
      )}
    </>
  );
}
