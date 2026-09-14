import { useState } from 'react';
import { useLocation, useNavigate, Navigate } from 'react-router-dom';
import api, { extractErrorMessage } from '../api/client';
import { useAuth } from '../context/AuthContext';
import AuthHeader from '../components/AuthHeader';

export default function VerifyCode() {
  const location = useLocation();
  const navigate = useNavigate();
  const { completeLogin } = useAuth();
  const email = location.state?.email;
  const [code, setCode] = useState('');
  const [error, setError] = useState('');
  const [busy, setBusy] = useState(false);

  // Someone navigated here directly without going through /login first.
  if (!email) {
    return <Navigate to="/login" replace />;
  }

  async function handleSubmit(e) {
    e.preventDefault();
    setError('');
    setBusy(true);
    try {
      const res = await api.post('/login/verify', { email, code });
      completeLogin(res.data.token, res.data.user);
      navigate('/dashboard', { replace: true });
    } catch (err) {
      setError(extractErrorMessage(err));
    } finally {
      setBusy(false);
    }
  }

  return (
    <div className="auth-shell">
      <div className="auth-card">
        <AuthHeader />
        <h1>Check your email</h1>
        <p className="subtitle">
          Enter the 6-digit code sent to <strong>{email}</strong>
        </p>

        <form onSubmit={handleSubmit}>
          <label>Verification code</label>
          <input
            type="text"
            inputMode="numeric"
            maxLength={6}
            required
            autoFocus
            value={code}
            onChange={(e) => setCode(e.target.value.replace(/\D/g, ''))}
            placeholder="123456"
          />

          {error && <div className="error-box">{error}</div>}

          <button className="primary" type="submit" disabled={busy || code.length !== 6}>
            {busy ? 'Verifying...' : 'Verify & sign in'}
          </button>
        </form>

        <p className="switch-link">
          <button className="link" onClick={() => navigate('/login')}>
            Use a different account
          </button>
        </p>
      </div>
    </div>
  );
}
