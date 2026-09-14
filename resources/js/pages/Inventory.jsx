import { useState } from 'react';
import api, { extractErrorMessage } from '../api/client';
import { useResourceList } from '../api/useResourceList';
import Modal from '../components/Modal';

const emptyForm = {
  warehouse_id: '',
  name: '',
  sku: '',
  category: 'frozen',
  species: '',
  quantity_kg: '',
  reorder_threshold_kg: '',
  unit_price: '',
  expires_at: '',
};

export default function Inventory() {
  const { items, loading, error, reload } = useResourceList('/inventory');
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
      await api.post('/inventory', {
        ...form,
        warehouse_id: Number(form.warehouse_id),
        quantity_kg: Number(form.quantity_kg),
        reorder_threshold_kg: form.reorder_threshold_kg ? Number(form.reorder_threshold_kg) : null,
        unit_price: Number(form.unit_price),
        expires_at: form.expires_at || null,
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
      <h2>Inventory</h2>
      <div className="toolbar">
        <button className="secondary" onClick={() => setShowForm(true)}>
          + Add item
        </button>
      </div>

      {loading && <div className="loading-state">Loading inventory...</div>}
      {error && <div className="error-box">{error}</div>}

      {!loading && !error && (
        <table>
          <thead>
            <tr>
              <th>Name</th>
              <th>SKU</th>
              <th>Warehouse</th>
              <th>Category</th>
              <th>Qty (kg)</th>
              <th>Unit price</th>
              <th>Expires</th>
              <th>Stock</th>
            </tr>
          </thead>
          <tbody>
            {items.length === 0 && (
              <tr>
                <td colSpan={8} className="empty-state">
                  No inventory items yet.
                </td>
              </tr>
            )}
            {items.map((i) => (
              <tr key={i.id}>
                <td>{i.name}</td>
                <td>{i.sku}</td>
                <td>{i.warehouse_name || '—'}</td>
                <td>{i.category}</td>
                <td>{i.quantity_kg}</td>
                <td>{i.unit_price}</td>
                <td>{i.expires_at || '—'}</td>
                <td>
                  {i.low_stock ? (
                    <span className="badge yellow">low stock</span>
                  ) : (
                    <span className="badge green">ok</span>
                  )}
                </td>
              </tr>
            ))}
          </tbody>
        </table>
      )}

      {showForm && (
        <Modal title="Add inventory item" onClose={() => setShowForm(false)}>
          <form onSubmit={handleCreate}>
            <label>Warehouse</label>
            <select required value={form.warehouse_id} onChange={update('warehouse_id')}>
              <option value="">Select a warehouse</option>
              {warehouses.map((w) => (
                <option key={w.id} value={w.id}>
                  {w.name}
                </option>
              ))}
            </select>

            <label>Name</label>
            <input required value={form.name} onChange={update('name')} />

            <label>SKU</label>
            <input required value={form.sku} onChange={update('sku')} />

            <label>Category</label>
            <select value={form.category} onChange={update('category')}>
              <option value="frozen">Frozen</option>
              <option value="chilled">Chilled</option>
              <option value="live">Live</option>
              <option value="dry">Dry</option>
            </select>

            <label>Species (optional)</label>
            <input value={form.species} onChange={update('species')} />

            <label>Quantity (kg)</label>
            <input type="number" min="0" step="0.01" required value={form.quantity_kg} onChange={update('quantity_kg')} />

            <label>Reorder threshold (kg)</label>
            <input type="number" min="0" step="0.01" value={form.reorder_threshold_kg} onChange={update('reorder_threshold_kg')} />

            <label>Unit price</label>
            <input type="number" min="0" step="0.01" required value={form.unit_price} onChange={update('unit_price')} />

            <label>Expires at</label>
            <input type="date" value={form.expires_at} onChange={update('expires_at')} />

            {formError && <div className="error-box">{formError}</div>}

            <button className="primary" type="submit" disabled={saving}>
              {saving ? 'Saving...' : 'Add item'}
            </button>
          </form>
        </Modal>
      )}
    </div>
  );
}
