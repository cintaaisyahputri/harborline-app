import { useState } from 'react';
import api, { extractErrorMessage } from '../api/client';
import { useResourceList } from '../api/useResourceList';
import Modal from '../components/Modal';
import StatusBadge from '../components/StatusBadge';

function emptyLine() {
  return { inventory_item_id: '', quantity_kg: '' };
}

export default function Orders() {
  const { items, loading, error, reload } = useResourceList('/orders');
  const { items: warehouses } = useResourceList('/warehouses', { per_page: 100 });

  const [showForm, setShowForm] = useState(false);
  const [warehouseId, setWarehouseId] = useState('');
  const [notes, setNotes] = useState('');
  const [lines, setLines] = useState([emptyLine()]);
  const [formError, setFormError] = useState('');
  const [saving, setSaving] = useState(false);

  // Inventory choices depend on the selected warehouse, fetched on demand below.
  const [inventoryOptions, setInventoryOptions] = useState([]);

  async function handleWarehouseChange(e) {
    const id = e.target.value;
    setWarehouseId(id);
    setLines([emptyLine()]);
    if (!id) {
      setInventoryOptions([]);
      return;
    }
    try {
      const res = await api.get('/inventory', { params: { warehouse_id: id, per_page: 100 } });
      const data = Array.isArray(res.data) ? res.data : res.data.data;
      setInventoryOptions(data || []);
    } catch {
      setInventoryOptions([]);
    }
  }

  function updateLine(idx, field, value) {
    setLines((ls) => ls.map((l, i) => (i === idx ? { ...l, [field]: value } : l)));
  }

  function addLine() {
    setLines((ls) => [...ls, emptyLine()]);
  }

  function removeLine(idx) {
    setLines((ls) => ls.filter((_, i) => i !== idx));
  }

  function resetForm() {
    setWarehouseId('');
    setNotes('');
    setLines([emptyLine()]);
    setInventoryOptions([]);
    setFormError('');
  }

  async function handleCreate(e) {
    e.preventDefault();
    setFormError('');
    setSaving(true);
    try {
      await api.post('/orders', {
        warehouse_id: Number(warehouseId),
        notes: notes || null,
        items: lines
          .filter((l) => l.inventory_item_id && l.quantity_kg)
          .map((l) => ({
            inventory_item_id: Number(l.inventory_item_id),
            quantity_kg: Number(l.quantity_kg),
          })),
      });
      setShowForm(false);
      resetForm();
      reload();
    } catch (err) {
      setFormError(extractErrorMessage(err));
    } finally {
      setSaving(false);
    }
  }

  async function handlePrintReceipt(order) {
    try {
      const res = await api.get(`/orders/${order.id}/receipt`);
      if (res.data.print_url) {
        // Laravel's signed route() helper returns an absolute URL, and since
        // the SPA and API are now the same origin, opening it directly works.
        window.open(res.data.print_url, '_blank');
      }
    } catch (err) {
      alert(extractErrorMessage(err));
    }
  }

  async function handleStatusChange(order, status) {
    try {
      await api.patch(`/orders/${order.id}`, { status });
      reload();
    } catch (err) {
      alert(extractErrorMessage(err));
    }
  }

  return (
    <div>
      <h2>Orders</h2>
      <div className="toolbar">
        <button className="secondary" onClick={() => setShowForm(true)}>
          + New order
        </button>
      </div>

      {loading && <div className="loading-state">Loading orders...</div>}
      {error && <div className="error-box">{error}</div>}

      {!loading && !error && (
        <table>
          <thead>
            <tr>
              <th>Order #</th>
              <th>Buyer</th>
              <th>Warehouse</th>
              <th>Items</th>
              <th>Total</th>
              <th>Status</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            {items.length === 0 && (
              <tr>
                <td colSpan={7} className="empty-state">
                  No orders yet.
                </td>
              </tr>
            )}
            {items.map((o) => (
              <tr key={o.id}>
                <td>{o.order_number}</td>
                <td>{o.buyer?.name || '—'}</td>
                <td>{o.warehouse?.name || '—'}</td>
                <td>{o.items?.length ?? 0}</td>
                <td>{o.total_amount}</td>
                <td>
                  <StatusBadge value={o.status} />
                </td>
                <td>
                  <div className="toolbar" style={{ margin: 0 }}>
                    {o.status !== 'cancelled' && (
                      <button className="secondary" onClick={() => handlePrintReceipt(o)}>
                        Print receipt
                      </button>
                    )}
                    {o.status === 'confirmed' && (
                      <>
                        <button className="secondary" onClick={() => handleStatusChange(o, 'fulfilled')}>
                          Mark fulfilled
                        </button>
                        <button className="secondary" onClick={() => handleStatusChange(o, 'cancelled')}>
                          Cancel
                        </button>
                      </>
                    )}
                  </div>
                </td>
              </tr>
            ))}
          </tbody>
        </table>
      )}

      {showForm && (
        <Modal
          title="New order"
          onClose={() => {
            setShowForm(false);
            resetForm();
          }}
        >
          <form onSubmit={handleCreate}>
            <label>Warehouse</label>
            <select required value={warehouseId} onChange={handleWarehouseChange}>
              <option value="">Select a warehouse</option>
              {warehouses.map((w) => (
                <option key={w.id} value={w.id}>
                  {w.name}
                </option>
              ))}
            </select>

            <label style={{ marginTop: 16 }}>Items</label>
            {lines.map((line, idx) => (
              <div className="order-line" key={idx}>
                <select
                  required
                  value={line.inventory_item_id}
                  onChange={(e) => updateLine(idx, 'inventory_item_id', e.target.value)}
                  disabled={!warehouseId}
                >
                  <option value="">Select item</option>
                  {inventoryOptions.map((it) => (
                    <option key={it.id} value={it.id}>
                      {it.name} ({it.quantity_kg}kg available)
                    </option>
                  ))}
                </select>
                <input
                  type="number"
                  min="0.01"
                  step="0.01"
                  placeholder="kg"
                  required
                  value={line.quantity_kg}
                  onChange={(e) => updateLine(idx, 'quantity_kg', e.target.value)}
                />
                {lines.length > 1 && (
                  <button type="button" className="link" onClick={() => removeLine(idx)}>
                    ✕
                  </button>
                )}
              </div>
            ))}
            <button type="button" className="link" onClick={addLine}>
              + Add another item
            </button>

            <label>Notes (optional)</label>
            <input value={notes} onChange={(e) => setNotes(e.target.value)} />

            {formError && <div className="error-box">{formError}</div>}

            <button className="primary" type="submit" disabled={saving || !warehouseId}>
              {saving ? 'Placing order...' : 'Place order'}
            </button>
          </form>
        </Modal>
      )}
    </div>
  );
}
