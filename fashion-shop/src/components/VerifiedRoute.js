import { Navigate } from 'react-router-dom';
import { useAuth } from '../context/AuthContext';

export default function VerifiedRoute({ children }) {
  const { user } = useAuth();

  if (!user) return <Navigate to="/login" />;

  if (!user.email_verified_at) return <Navigate to="/resend-verification" />;

  return children;
}
