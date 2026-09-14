import { useState } from 'react';
import api, { extractErrorMessage } from '../api/client';
import { useResourceList } from '../api/useResourceList';
import Modal from '../components/Modal';
import StatusBadge from '../components/StatusBadge';

const emptyForm = {
  name: '',
  registration_number: '',
  home_port: '',
  captain_name: '',
  capacity_tons: '',
  status: 'docked',
};

export default function Fleet() {
  const { items, loading, error, reload } = useResourceList('/fleet');
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
      await api.post('/fleet', {
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
      <h2>Fleet</h2>
      <div className="toolbar">
        <button className="secondary" onClick={() => setShowForm(true)}>
          + Register vessel
        </button>
      </div>

      {loading && <div className="loading-state">Loading vessels...</div>}
      {error && <div className="error-box">{error}</div>}

      {!loading && !error && (
        <table>
          <thead>
            <tr>
              <th>Name</th>
              <th>Registration</th>
              <th>Home port</th>
              <th>Captain</th>
              <th>Capacity (t)</th>
              <th>Status</th>
              <th>Cleared to sail</th>
            </tr>
          </thead>
          <tbody>
            {items.length === 0 && (
              <tr>
                <td colSpan={7} className="empty-state">
                  No vessels yet.
                </td>
              </tr>
            )}
            {items.map((v) => (
              <tr key={v.id}>
                <td>{v.name}</td>
                <td>{v.registration_number}</td>
                <td>{v.home_port || '—'}</td>
                <td>{v.captain_name || '—'}</td>
                <td>{v.capacity_tons ?? '—'}</td>
                <td>
                  <StatusBadge value={v.status} />
                </td>
                <td>
                  {v.cleared_to_sail ? (
                    <span className="badge green">cleared</span>
                  ) : (
                    <span className="badge red">held</span>
                  )}
                </td>
              </tr>
            ))}
          </tbody>
        </table>
      )}

      {showForm && (
        <Modal title="Register vessel" onClose={() => setShowForm(false)}>
          <form onSubmit={handleCreate}>
            <label>Name</label>
            <input required value={form.name} onChange={update('name')} />

            <label>Registration number</label>
            <input required value={form.registration_number} onChange={update('registration_number')} />

            <label>Home port</label>
            <input value={form.home_port} onChange={update('home_port')} />

            <label>Captain name</label>
            <input value={form.captain_name} onChange={update('captain_name')} />

            <label>Capacity (tons)</label>
            <input type="number" min="0" value={form.capacity_tons} onChange={update('capacity_tons')} />

            <label>Status</label>
            <select value={form.status} onChange={update('status')}>
              <option value="docked">Docked</option>
              <option value="at_sea">At sea</option>
              <option value="maintenance">Maintenance</option>
            </select>

            {formError && <div className="error-box">{formError}</div>}

            <button className="primary" type="submit" disabled={saving}>
              {saving ? 'Saving...' : 'Register vessel'}
            </button>
          </form>
        </Modal>
      )}
    </div>
  );
}
