import { useState } from 'react';
import api, { extractErrorMessage } from '../api/client';
import { useResourceList } from '../api/useResourceList';
import Modal from '../components/Modal';
import StatusBadge from '../components/StatusBadge';

const emptyForm = {
  target: 'vessel',
  vessel_id: '',
  warehouse_id: '',
  certificate_type: 'health_inspection',
  certificate_number: '',
  issuing_authority: '',
  issued_at: '',
  expires_at: '',
};

export default function Compliance() {
  const { items, loading, error, reload } = useResourceList('/compliance');
  const { items: vessels } = useResourceList('/fleet', { per_page: 100 });
  const { items: warehouses } = useResourceList('/warehouses', { per_page: 100 });
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
      await api.post('/compliance', {
        vessel_id: form.target === 'vessel' ? Number(form.vessel_id) : null,
        warehouse_id: form.target === 'warehouse' ? Number(form.warehouse_id) : null,
        certificate_type: form.certificate_type,
        certificate_number: form.certificate_number,
        issuing_authority: form.issuing_authority,
        issued_at: form.issued_at,
        expires_at: form.expires_at,
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
      <h2>Compliance certificates</h2>
      <div className="toolbar">
        <button className="secondary" onClick={() => setShowForm(true)}>
          + Add certificate
        </button>
      </div>

      {loading && <div className="loading-state">Loading certificates...</div>}
      {error && <div className="error-box">{error}</div>}

      {!loading && !error && (
        <table>
          <thead>
            <tr>
              <th>Type</th>
              <th>Number</th>
              <th>Issuer</th>
              <th>For</th>
              <th>Expires</th>
              <th>Status</th>
            </tr>
          </thead>
          <tbody>
            {items.length === 0 && (
              <tr>
                <td colSpan={6} className="empty-state">
                  No certificates yet.
                </td>
              </tr>
            )}
            {items.map((c) => (
              <tr key={c.id}>
                <td>{String(c.certificate_type).replace(/_/g, ' ')}</td>
                <td>{c.certificate_number}</td>
                <td>{c.issuing_authority}</td>
                <td>{c.vessel?.name || c.warehouse?.name || '—'}</td>
                <td>{c.expires_at}</td>
                <td>
                  <StatusBadge value={c.status} />
                </td>
              </tr>
            ))}
          </tbody>
        </table>
      )}

      {showForm && (
        <Modal title="Add compliance certificate" onClose={() => setShowForm(false)}>
          <form onSubmit={handleCreate}>
            <label>Applies to</label>
            <select value={form.target} onChange={update('target')}>
              <option value="vessel">A vessel</option>
              <option value="warehouse">A warehouse</option>
            </select>

            {form.target === 'vessel' ? (
              <>
                <label>Vessel</label>
                <select required value={form.vessel_id} onChange={update('vessel_id')}>
                  <option value="">Select a vessel</option>
                  {vessels.map((v) => (
                    <option key={v.id} value={v.id}>
                      {v.name}
                    </option>
                  ))}
                </select>
              </>
            ) : (
              <>
                <label>Warehouse</label>
                <select required value={form.warehouse_id} onChange={update('warehouse_id')}>
                  <option value="">Select a warehouse</option>
                  {warehouses.map((w) => (
                    <option key={w.id} value={w.id}>
                      {w.name}
                    </option>
                  ))}
                </select>
              </>
            )}

            <label>Certificate type</label>
            <select value={form.certificate_type} onChange={update('certificate_type')}>
              <option value="health_inspection">Health inspection</option>
              <option value="catch_origin">Catch origin</option>
              <option value="export_license">Export license</option>
              <option value="safety_survey">Safety survey</option>
            </select>

            <label>Certificate number</label>
            <input required value={form.certificate_number} onChange={update('certificate_number')} />

            <label>Issuing authority</label>
            <input required value={form.issuing_authority} onChange={update('issuing_authority')} />

            <label>Issued at</label>
            <input type="date" required value={form.issued_at} onChange={update('issued_at')} />

            <label>Expires at</label>
            <input type="date" required value={form.expires_at} onChange={update('expires_at')} />

            {formError && <div className="error-box">{formError}</div>}

            <button className="primary" type="submit" disabled={saving}>
              {saving ? 'Saving...' : 'Add certificate'}
            </button>
          </form>
        </Modal>
      )}
    </div>
  );
}
