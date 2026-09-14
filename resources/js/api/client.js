import axios from 'axios';

// Same-origin now that the SPA is served by the same Laravel app -- no
// cross-origin/CORS setup needed. VITE_API_URL can still override this
// (e.g. pointing a locally-run frontend at a deployed API) if ever needed.
const baseURL = import.meta.env.VITE_API_URL || '/api';

const api = axios.create({ baseURL });

api.interceptors.request.use((config) => {
  const token = localStorage.getItem('hp_token');
  if (token) {
    config.headers.Authorization = `Bearer ${token}`;
  }
  return config;
});

// Centralised error unwrapping: Laravel validation errors come back as
// { message, errors: { field: [msg] } }. Flatten that into one readable string.
export function extractErrorMessage(error) {
  const data = error?.response?.data;
  if (!data) return error?.message || 'Something went wrong.';
  if (data.errors) {
    return Object.values(data.errors).flat().join(' ');
  }
  return data.message || 'Something went wrong.';
}

export default api;
