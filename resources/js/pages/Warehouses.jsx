import { useState } from 'react';
import api, { extractErrorMessage } from '../api/client';
import { useResourceList } from '../api/useResourceList';
import Modal from '../components/Modal';

const emptyForm = { name: '', location: '', type: 'cold_storage', capacity_tons: '' };

export default function Warehouses() {
  const { items, loading, error, reload } = useResourceList('/warehouses');
  const [showForm, setShowForm] = useState(false);
  const [form, setForm] = useState(emptyForm);
  const [formError, setFormError] = useState('');
  const [saving, setSaving] = useState(false);

  function update(field) {
    return (e) => setForm((f) => ({ ...f, [field]: e.target.value }));
  }

  async function handleCreate(e) {
    e.preventDefault();
    setFormError('');
    setSaving(true);
    try {
      await api.post('/warehouses', {
        ...form,
        capacity_tons: form.capacity_tons ? Number(form.capacity_tons) : null,
      });
      setShowForm(false);
      setForm(emptyForm);
      reload();
    } catch (err) {
      setFormError(extractErrorMessage(err));
    } finally {
      setSaving(false);
    }
  }

  return (
    <div>
      <h2>Warehouses</h2>
      <div className="toolbar">
        <button className="secondary" onClick={() => setShowForm(true)}>
          + Add warehouse
        </button>
      </div>

      {loading && <div className="loading-state">Loading warehouses...</div>}
      {error && <div className="error-box">{error}</div>}

      {!loading && !error && (
        <table>
          <thead>
            <tr>
              <th>Name</th>
              <th>Location</th>
              <th>Type</th>
              <th>Capacity (t)</th>
              <th>Manager</th>
              <th>Current stock (kg)</th>
            </tr>
          </thead>
          <tbody>
            {items.length === 0 && (
              <tr>
                <td colSpan={6} className="empty-state">
                  No warehouses yet.
                </td>
              </tr>
            )}
            {items.map((w) => (
              <tr key={w.id}>
                <td>{w.name}</td>
                <td>{w.location}</td>
                <td>{String(w.type).replace(/_/g, ' ')}</td>
                <td>{w.capacity_tons ?? '—'}</td>
                <td>{w.manager?.name || '—'}</td>
                <td>{w.current_stock_kg ?? 0}</td>
              </tr>
            ))}
          </tbody>
        </table>
      )}

      {showForm && (
        <Modal title="Add warehouse" onClose={() => setShowForm(false)}>
          <form onSubmit={handleCreate}>
            <label>Name</label>
            <input required value={form.name} onChange={update('name')} />

            <label>Location</label>
            <input required value={form.location} onChange={update('location')} />

            <label>Type</label>
            <select value={form.type} onChange={update('type')}>
              <option value="cold_storage">Cold storage</option>
              <option value="dry_storage">Dry storage</option>
              <option value="processing">Processing</option>
            </select>

            <label>Capacity (tons)</label>
            <input type="number" min="0" value={form.capacity_tons} onChange={update('capacity_tons')} />

            {formError && <div className="error-box">{formError}</div>}

            <button className="primary" type="submit" disabled={saving}>
              {saving ? 'Saving...' : 'Add warehouse'}
            </button>
          </form>
        </Modal>
      )}
    </div>
  );
}
