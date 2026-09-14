import { createContext, useContext, useEffect, useState } from 'react';
import api from '../api/client';

const AuthContext = createContext(null);

export function AuthProvider({ children }) {
  const [user, setUser] = useState(null);
  const [token, setToken] = useState(() => localStorage.getItem('hp_token'));
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    if (!token) {
      setLoading(false);
      return;
    }
    api
      .get('/me')
      .then((res) => setUser(res.data))
      .catch(() => {
        localStorage.removeItem('hp_token');
        setToken(null);
      })
      .finally(() => setLoading(false));
  }, [token]);

  function completeLogin(newToken, newUser) {
    localStorage.setItem('hp_token', newToken);
    setToken(newToken);
    setUser(newUser);
  }

  async function logout() {
    try {
      await api.post('/logout');
    } catch {
      // ignore -- we're clearing local state regardless
    }
    localStorage.removeItem('hp_token');
    setToken(null);
    setUser(null);
  }

  return (
    <AuthContext.Provider value={{ user, token, loading, completeLogin, logout }}>
      {children}
    </AuthContext.Provider>
  );
}

export function useAuth() {
  const ctx = useContext(AuthContext);
  if (!ctx) throw new Error('useAuth must be used within AuthProvider');
  return ctx;
}
