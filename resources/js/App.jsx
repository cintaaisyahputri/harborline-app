import { Navigate, Route, Routes } from 'react-router-dom';
import Login from './pages/Login';
import VerifyCode from './pages/VerifyCode';
import Register from './pages/Register';
import Fleet from './pages/Fleet';
import Warehouses from './pages/Warehouses';
import Inventory from './pages/Inventory';
import Orders from './pages/Orders';
import Compliance from './pages/Compliance';
import { ProtectedRoute } from './components/ProtectedRoute';

export default function App() {
  return (
    <Routes>
      <Route path="/login" element={<Login />} />
      <Route path="/verify" element={<VerifyCode />} />
      <Route path="/register" element={<Register />} />

      <Route element={<ProtectedRoute />}>
        <Route path="/dashboard" element={<Navigate to="/dashboard/orders" replace />} />
        <Route path="/dashboard/fleet" element={<Fleet />} />
        <Route path="/dashboard/warehouses" element={<Warehouses />} />
        <Route path="/dashboard/inventory" element={<Inventory />} />
        <Route path="/dashboard/orders" element={<Orders />} />
        <Route path="/dashboard/compliance" element={<Compliance />} />
      </Route>

      <Route path="*" element={<Navigate to="/dashboard" replace />} />
    </Routes>
  );
}
