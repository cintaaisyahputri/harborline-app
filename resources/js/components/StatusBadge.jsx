const COLORS = {
  // orders
  pending: 'yellow',
  confirmed: 'green',
  fulfilled: 'green',
  cancelled: 'red',
  // vessels
  at_sea: 'yellow',
  docked: 'green',
  maintenance: 'gray',
  // compliance
  valid: 'green',
  expiring_soon: 'yellow',
  expired: 'red',
};

export default function StatusBadge({ value }) {
  const color = COLORS[value] || 'gray';
  return <span className={`badge ${color}`}>{String(value).replace(/_/g, ' ')}</span>;
}
