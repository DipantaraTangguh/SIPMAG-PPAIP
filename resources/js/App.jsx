/**
 * App.jsx — Root component for SIPMAG.
 * Uses SimulationProvider as the single source of truth.
 * Routes: /login (guest), /dashboard, /form1, /form1/status (protected).
 * SimulationPanel is always visible for demo control.
 */
import React from 'react';
import { BrowserRouter, Routes, Route, Navigate } from 'react-router-dom';

import { SimulationProvider } from './context/SimulationContext';
import ProtectedRoute from './routes/ProtectedRoute';
import GuestRoute from './components/GuestRoute';
import SimulationPanel from './components/SimulationPanel';

import LoginPage from './features/auth/LoginPage';
import DashboardPage from './features/dashboard/DashboardPage';
import Form1Page from './features/form1/Form1Page';
import Form1StatusPage from './features/form1/Form1StatusPage';
import PortalMagangPage from './features/portal/PortalMagangPage';
import VacancyDetailPage from './features/portal/VacancyDetailPage';
import Form2NewPage from './pages/Form2NewPage';
import BimbinganPage from './pages/BimbinganPage';
import SidangMagangPage from './pages/SidangMagangPage';

export default function App() {
    return (
        <SimulationProvider>
            <BrowserRouter>
                <Routes>
                    {/* Guest only */}
                    <Route path="/login" element={<GuestRoute><LoginPage /></GuestRoute>} />

                    {/* Protected routes */}
                    <Route path="/dashboard" element={<ProtectedRoute><DashboardPage /></ProtectedRoute>} />
                    <Route path="/form1" element={<ProtectedRoute><Form1Page /></ProtectedRoute>} />
                    <Route path="/form1/status" element={<ProtectedRoute><Form1StatusPage /></ProtectedRoute>} />
                    <Route path="/portal" element={<ProtectedRoute><PortalMagangPage /></ProtectedRoute>} />
                    <Route path="/portal/vacancy/:id" element={<ProtectedRoute><VacancyDetailPage /></ProtectedRoute>} />
                    <Route path="/portal/mandiri/form2/new" element={<ProtectedRoute><Form2NewPage /></ProtectedRoute>} />
                    <Route path="/bimbingan" element={<ProtectedRoute><BimbinganPage /></ProtectedRoute>} />
                    <Route path="/sidang" element={<ProtectedRoute><SidangMagangPage /></ProtectedRoute>} />

                    {/* Fallback */}
                    <Route path="*" element={<Navigate to="/dashboard" replace />} />
                </Routes>

                {/* Simulation panel — always visible */}
                <SimulationPanel />
            </BrowserRouter>
        </SimulationProvider>
    );
}
