import React from 'react';
import { BrowserRouter, Routes, Route, Navigate } from 'react-router-dom';

import { SimulationProvider } from './context/SimulationContext';
import ErrorBoundary from './Components/Layouts/ErrorBoundary';
import ProtectedRoute from './Components/Layouts/ProtectedRoute';
import GuestRoute from './Components/Layouts/GuestRoute';

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
        // Root boundary — last-resort catch for SimulationProvider / BrowserRouter errors.
        <ErrorBoundary>
            <SimulationProvider>
                <BrowserRouter>
                    <Routes>
                        {/* Auth route — isolated so a login-page crash can't block recovery */}
                        <Route
                            path="/login"
                            element={
                                <ErrorBoundary>
                                    <GuestRoute><LoginPage /></GuestRoute>
                                </ErrorBoundary>
                            }
                        />

                        {/* Protected routes — each wrapped individually so a crash on one page
                            does not replace every other page with an error card. Users can
                            navigate away (e.g. to /dashboard) to recover without a full reload. */}
                        <Route
                            path="/dashboard"
                            element={
                                <ErrorBoundary>
                                    <ProtectedRoute><DashboardPage /></ProtectedRoute>
                                </ErrorBoundary>
                            }
                        />
                        <Route
                            path="/form1"
                            element={
                                <ErrorBoundary>
                                    <ProtectedRoute><Form1Page /></ProtectedRoute>
                                </ErrorBoundary>
                            }
                        />
                        <Route
                            path="/form1/status"
                            element={
                                <ErrorBoundary>
                                    <ProtectedRoute><Form1StatusPage /></ProtectedRoute>
                                </ErrorBoundary>
                            }
                        />
                        <Route
                            path="/portal"
                            element={
                                <ErrorBoundary>
                                    <ProtectedRoute><InternshipPortalPage /></ProtectedRoute>
                                </ErrorBoundary>
                            }
                        />
                        <Route
                            path="/portal/vacancy/:id"
                            element={
                                <ErrorBoundary>
                                    <ProtectedRoute><VacancyDetailPage /></ProtectedRoute>
                                </ErrorBoundary>
                            }
                        />
                        <Route
                            path="/portal/independent/form2/new"
                            element={
                                <ErrorBoundary>
                                    <ProtectedRoute><Form2NewPage /></ProtectedRoute>
                                </ErrorBoundary>
                            }
                        />
                        <Route
                            path="/guidance"
                            element={
                                <ErrorBoundary>
                                    <ProtectedRoute><GuidancePage /></ProtectedRoute>
                                </ErrorBoundary>
                            }
                        />
                        <Route
                            path="/defense"
                            element={
                                <ErrorBoundary>
                                    <ProtectedRoute><InternshipDefensePage /></ProtectedRoute>
                                </ErrorBoundary>
                            }
                        />

                        <Route path="*" element={<Navigate to="/dashboard" replace />} />
                    </Routes>
                </BrowserRouter>
            </SimulationProvider>
        </ErrorBoundary>
    );
}
