import React, { useState, useEffect, useRef } from 'react';
import axios from '../api/axios';
import { useAuth } from '../context/AuthContext';
import { listenToNewMessages } from '../realtime/NewChat';
import { sendTypingStatus } from '../realtime/tyPing';

export default function ChatApp() {
  const [isOpen, setIsOpen] = useState(false);
  const [avatar, setAvatar] = useState('');
  const [message, setMessage] = useState('');
  const [messages, setMessages] = useState([]);
  const [userId, setUserId] = useState(null);
  const { token } = useAuth();

  const messagesEndRef = useRef(null);

  const adminAvatar = 'https://secure.gravatar.com/avatar/2ad86d4128742b555b487c8a62a33e9e?s=500&d=mm&r=g';
  const userAvatar = 'https://img.freepik.com/premium-vector/man-avatar-profile-picture-isolated-background-avatar-profile-picture-man_1293239-4841.jpg';

  // Lấy thông tin user
  useEffect(() => {
    const userData = localStorage.getItem('user');
    if (userData) {
      try {
        const user = JSON.parse(userData);
        setUserId(user.id);
        if (user.img_thumbnail) {
          setAvatar(`${process.env.REACT_APP_IMAGE_BASE_URL}/storage/${user.img_thumbnail}`);
        } else {
          setAvatar(userAvatar);
        }
      } catch (err) {
        console.error('❌ Lỗi parse user:', err);
        setAvatar(adminAvatar);
      }
    } else {
      setAvatar(adminAvatar);
    }
  }, []);

  // Lắng nghe realtime
  useEffect(() => {
    if (!userId) return;

    const unsubscribeMessage = listenToNewMessages(userId, (newMessage) => {
      setMessages(prev => [...prev, newMessage]);
    });

    return () => {
      if (unsubscribeMessage?.stopListening) {
        unsubscribeMessage.stopListening();
      }
    };
  }, [userId]);

  // Scroll xuống cuối mỗi khi có tin nhắn mới
  useEffect(() => {
    if (messagesEndRef.current) {
      messagesEndRef.current.scrollIntoView({ behavior: 'smooth' });
    }
  }, [messages]);

  // Tải tin nhắn từ API
  const loadMessages = async (uid) => {
    try {
      const res = await axios.get('/chat', {
        params: { user_id: uid },
        headers: { Authorization: `Bearer ${token}` },
      });
      setMessages(res.data.data || []);
    } catch (error) {
      console.error(' Lỗi load tin nhắn:', error.response?.data || error.message);
    }
  };
  

  // Gửi tin nhắn
  const handleSend = async () => {
    if (!message.trim()) return;
    const img_thumbnail = localStorage.getItem('user');
    const user = img_thumbnail ? JSON.parse(img_thumbnail): null;
    const avatar = user?.img_thumbnail 
    ? `/storage/${user.img_thumbnail}` : `https://i.pravatar.cc/150?u=${user?.id}`

    
    try {
      const res = await axios.post(
        '/chat/send',
        { message,avatar },
        {
          headers: {
            Authorization: `Bearer ${token}`,
          },
        }
      );

      const newMessage = res.data.data;
      
      setMessages(prev => [...prev, newMessage]);
      setMessage('');
    } catch (error) {
      console.error(' Lỗi gửi tin nhắn:', error.response?.data || error.message);
    }
  };

  return (
    <>
      {!isOpen && (
        <button
          className="btn btn-primary position-fixed bottom-0 end-0 m-4 rounded-circle shadow"
          style={{ width: 60, height: 60, zIndex: 1050 }}
          onClick={() => {
            setIsOpen(true);
            if (userId) loadMessages(userId);
          }}
        >
          💬
        </button>
      )}

      {isOpen && (
        <div
          className="position-fixed bottom-0 end-0 m-4 bg-white border rounded shadow"
          style={{ width: 420, maxWidth: '95vw', zIndex: 1040, height: 600, display: 'flex', flexDirection: 'column' }}
        >
          {/* Header */}
          <div className="border-bottom p-2 d-flex justify-content-between align-items-center">
            <strong>Trò chuyện</strong>
            <button className="btn btn-sm btn-danger" onClick={() => setIsOpen(false)}>
              ✖
            </button>
          </div>

          {/* Danh sách tin nhắn */}
          <div className="flex-grow-1 p-3 overflow-auto" style={{ background: '#f8f9fa' }}>
            {messages.length === 0 ? (
              <div className="text-center text-muted">Chưa có tin nhắn nào.</div>
            ) : (
              messages.map((msg, index) => (
                <div
                  key={msg.id || `msg-${index}-${Date.now()}`}
                  className={`d-flex mb-3 ${msg.sender === 'user' ? 'justify-content-end' : 'justify-content-start'}`}
                >
                  {msg.sender === 'admin' && (
                    <img
                      src={adminAvatar}
                      alt="Admin"
                      className="rounded-circle me-2"
                      style={{ width: 40, height: 40, objectFit: 'cover' }}
                    />
                  )}
                  <div className="bg-light p-2 rounded" style={{ maxWidth: '75%' }}>
                    {msg.message}
                  </div>
                  {msg.sender === 'user' && (
                    <img
                      src={avatar}
                      alt="User"
                      className="rounded-circle ms-2"
                      style={{ width: 40, height: 40, objectFit: 'cover' }}
                    />
                  )}
                </div>
              ))
            )}
            <div ref={messagesEndRef} />
          </div>

          {/* Input */}
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
              onChange={(e) => {
                setMessage(e.target.value);
                sendTypingStatus(userId, token);
              }}
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
