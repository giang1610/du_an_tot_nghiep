import axios from '../api/axios';


let lastSent = 0;

export async function sendTypingStatus(userId, token) {
  const now = Date.now();

  // Gửi 1 lần mỗi 5s tối đa
  if (now - lastSent < 5000) return;

  lastSent = now;

  try {
    await axios.post(
      '/chat/typing',
      { user_id: userId },
      {
        headers: {
          Authorization: `Bearer ${token}`,
        },
      }
    );
  } catch (error) {
    console.error('❌ Không thể gửi typing:', error.response?.data || error.message);
  }
}
