import { useCallback, useEffect, useState } from 'react';
import api from '../api/client';

// Laravel's ResourceCollection wraps paginated results as { data, links, meta }.
// Plain collections just return an array. Normalise both to an array.
function unwrap(payload) {
  if (Array.isArray(payload)) return payload;
  if (payload && Array.isArray(payload.data)) return payload.data;
  return [];
}

export function useResourceList(endpoint, params = {}) {
  const [items, setItems] = useState([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState('');

  const paramsKey = JSON.stringify(params);

  const reload = useCallback(async () => {
    setLoading(true);
    setError('');
    try {
      const res = await api.get(endpoint, { params: JSON.parse(paramsKey) });
      setItems(unwrap(res.data));
    } catch (err) {
      setError(err?.response?.data?.message || 'Failed to load data.');
    } finally {
      setLoading(false);
    }
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [endpoint, paramsKey]);

  useEffect(() => {
    reload();
  }, [reload]);

  return { items, loading, error, reload };
}
