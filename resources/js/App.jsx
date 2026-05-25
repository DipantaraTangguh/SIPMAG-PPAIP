/**
 * App.jsx — Root component for Portal Magang.
 * Uses SimulationProvider as the single source of truth.
 * Routes: /login (guest), /dashboard, /form1, /form1/status (protected).
 * SimulationPanel is always visible for demo control.
 */
import React from 'react';
import { BrowserRouter, Routes, Route, Navigate } from 'react-router-dom';

import { SimulationProvider } from './context/SimulationContext';
import ProtectedRoute from './Components/Layouts/ProtectedRoute';
import GuestRoute from './Components/Layouts/GuestRoute';
import SimulationPanel from './Components/Layouts/SimulationPanel';

import LoginPage from './Pages/Auth/LoginPage';
import DashboardPage from './Pages/Dashboard/DashboardPage';
import Form1Page from './Pages/Form1/Form1Page';
import Form1StatusPage from './Pages/Form1/Form1StatusPage';
import InternshipPortalPage from './Pages/Portal/InternshipPortalPage';
import VacancyDetailPage from './Pages/Portal/VacancyDetailPage';
import Form2NewPage from './Pages/Form2/Form2NewPage';
import GuidancePage from './Pages/Guidance/GuidancePage';
import InternshipDefensePage from './Pages/Defense/InternshipDefensePage';

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
                    <Route path="/portal" element={<ProtectedRoute><InternshipPortalPage /></ProtectedRoute>} />
                    <Route path="/portal/vacancy/:id" element={<ProtectedRoute><VacancyDetailPage /></ProtectedRoute>} />
                    <Route path="/portal/independent/form2/new" element={<ProtectedRoute><Form2NewPage /></ProtectedRoute>} />
                    <Route path="/guidance" element={<ProtectedRoute><GuidancePage /></ProtectedRoute>} />
                    <Route path="/defense" element={<ProtectedRoute><InternshipDefensePage /></ProtectedRoute>} />

                    {/* Fallback */}
                    <Route path="*" element={<Navigate to="/dashboard" replace />} />
                </Routes>

                {/* Simulation panel — always visible */}
                <SimulationPanel />
            </BrowserRouter>
        </SimulationProvider>
    );
}
