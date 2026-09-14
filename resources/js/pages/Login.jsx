import { useState } from 'react';
import { Link, useNavigate } from 'react-router-dom';
import api, { extractErrorMessage } from '../api/client';
import AuthHeader from '../components/AuthHeader';

export default function Login() {
  const navigate = useNavigate();
  const [form, setForm] = useState({ email: '', password: '' });
  const [error, setError] = useState('');
  const [busy, setBusy] = useState(false);

  function update(field) {
    return (e) => setForm((f) => ({ ...f, [field]: e.target.value }));
  }

  async function handleSubmit(e) {
    e.preventDefault();
    setError('');
    setBusy(true);
    try {
      const res = await api.post('/login', form);
      // Step 1 succeeded: a code was emailed. Move to the verify screen.
      navigate('/verify', { state: { email: res.data.email || form.email } });
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
        <h1>Sign in</h1>
        <p className="subtitle">Enter your email and password to continue</p>

        <form onSubmit={handleSubmit}>
          <label>Email</label>
          <input type="email" required value={form.email} onChange={update('email')} />

          <label>Password</label>
          <input type="password" required value={form.password} onChange={update('password')} />

          {error && <div className="error-box">{error}</div>}

          <button className="primary" type="submit" disabled={busy}>
            {busy ? 'Checking...' : 'Continue'}
          </button>
        </form>

        <div className="hint-box">
          We'll email a 6-digit code to finish signing in. In local dev
          (MAIL_MAILER=log) it's written to <code>storage/logs/laravel.log</code>
          instead of a real email.
        </div>

        <p className="switch-link">
          No account? <Link to="/register">Register</Link>
        </p>
      </div>
    </div>
  );
}
