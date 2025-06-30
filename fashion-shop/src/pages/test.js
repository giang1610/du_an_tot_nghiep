import React, { useEffect } from 'react';
import echo from '../echo';

const Test = () => {
  useEffect(() => {
  console.log('Khởi tạo kết nối WebSocket...');

  window.Echo = echo;

  echo.connector.pusher.connection.bind('connected', () => {
    console.log('✅ Đã kết nối WebSocket!');
  });

 echo.channel('chat')
  .listen('.MessageSent', (e) => {
    console.log('📥 Nhận tin nhắn:', e.message);
  });

}, []);

  return (
    <div>
      <h1>Test WebSocket</h1>
    </div>
  );
};

export default Test;
