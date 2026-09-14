import { NavLink, Navigate, Outlet } from 'react-router-dom';
import { useAuth } from '../context/AuthContext';

export function ProtectedRoute() {
  const { token, loading } = useAuth();

  if (loading) return <div className="loading-state">Loading...</div>;
  if (!token) return <Navigate to="/login" replace />;

  return (
    <div className="app-shell">
      <Sidebar />
      <main className="main-content">
        <Outlet />
      </main>
    </div>
  );
}

function Sidebar() {
  const { user, logout } = useAuth();

  const links = [
    { to: '/dashboard/fleet', label: 'Fleet' },
    { to: '/dashboard/warehouses', label: 'Warehouses' },
    { to: '/dashboard/inventory', label: 'Inventory' },
    { to: '/dashboard/orders', label: 'Orders' },
    { to: '/dashboard/compliance', label: 'Compliance' },
  ];

  return (
    <aside className="sidebar">
      <div className="brand">Harborline Provisions</div>
      <nav>
        {links.map((l) => (
          <NavLink key={l.to} to={l.to} className={({ isActive }) => (isActive ? 'active' : '')}>
            {l.label}
          </NavLink>
        ))}
      </nav>
      <div className="user-box">
        <div>{user?.name}</div>
        <div style={{ opacity: 0.7 }}>{user?.role}</div>
        <button className="secondary" onClick={logout}>
          Log out
        </button>
      </div>
    </aside>
  );
}
