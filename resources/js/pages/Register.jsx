import { useState } from 'react';
import { Link, useNavigate } from 'react-router-dom';
import api, { extractErrorMessage } from '../api/client';
import { useAuth } from '../context/AuthContext';
import AuthHeader from '../components/AuthHeader';

export default function Register() {
  const navigate = useNavigate();
  const { completeLogin } = useAuth();
  const [form, setForm] = useState({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
    role: 'buyer',
  });
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
      const res = await api.post('/register', form);
      // Registration issues a token straight away -- no email code needed here.
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
        <h1>Create an account</h1>
        <p className="subtitle">Join Harborline Provisions</p>

        <form onSubmit={handleSubmit}>
          <label>Full name</label>
          <input required value={form.name} onChange={update('name')} />

          <label>Email</label>
          <input type="email" required value={form.email} onChange={update('email')} />

          <label>Role</label>
          <select value={form.role} onChange={update('role')}>
            <option value="buyer">Buyer</option>
            <option value="warehouse">Warehouse staff</option>
            <option value="fleet_manager">Fleet manager</option>
          </select>

          <label>Password</label>
          <input type="password" required minLength={8} value={form.password} onChange={update('password')} />

          <label>Confirm password</label>
          <input
            type="password"
            required
            minLength={8}
            value={form.password_confirmation}
            onChange={update('password_confirmation')}
          />

          {error && <div className="error-box">{error}</div>}

          <button className="primary" type="submit" disabled={busy}>
            {busy ? 'Creating...' : 'Create account'}
          </button>
        </form>

        <p className="switch-link">
          Already have an account? <Link to="/login">Sign in</Link>
        </p>
      </div>
    </div>
  );
}
