/**
 * DashboardLayout.jsx
 * Main shell that wraps all authenticated dashboard pages.
 * Renders the fixed Sidebar on the left and a scrollable
 * main content area on the right with a top header bar.
 *
 * @prop {React.ReactNode} children   — Page content rendered inside the main area.
 * @prop {string}          pageTitle  — Title shown in the top header bar (default: "Portal Magang").
 * @prop {string}          activePath — Override for sidebar active nav item (default: auto-detect).
 */
import React from 'react';
import { useSimulation } from '../context/SimulationContext';
import Sidebar from '../components/Sidebar';
import PageTransition from '../components/PageTransition';

export default function DashboardLayout({
    children,
    pageTitle = 'Sistem Informasi Portal Magang',
    activePath,
}) {
    const { student, logout } = useSimulation();

    const studentName = student?.name ?? '—';
    const nim = student?.nim ?? '—';
    const initials = studentName
        .split(' ')
        .map((w) => w[0])
        .join('')
        .slice(0, 2)
        .toUpperCase();

    return (
        <div className="min-h-screen bg-[#F8F9FB]">
            {/* Sidebar */}
            <Sidebar nim={nim} onLogout={logout} activePath={activePath} />

            {/* Main area — offset by sidebar width */}
            <div className="ml-[260px] min-h-screen">
                {/* Top header bar */}
                <header className="flex items-center justify-between px-10 py-7">
                    <h2 className="text-[1.75rem] font-bold text-gray-900">
                        {pageTitle}
                    </h2>
                    <div className="flex h-10 w-10 items-center justify-center rounded-full bg-primary text-sm font-semibold text-white">
                        {initials}
                    </div>
                </header>

                {/* Page content with fade-in transition */}
                <main className="px-10 pb-10">
                    <PageTransition>{children}</PageTransition>
                </main>
            </div>
        </div>
    );
}
