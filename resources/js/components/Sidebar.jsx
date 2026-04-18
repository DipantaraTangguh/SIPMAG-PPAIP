/**
 * Sidebar.jsx
 * Fixed left sidebar navigation for the SIPMAG dashboard.
 * 260px wide, full viewport height, bg-primary background.
 *
 * Access to Sidang Magang is gated inside SidangPage itself.
 *
 * @prop {string}   nim        — Student NIM to display at the bottom.
 * @prop {function} onLogout   — Callback fired when "Keluar" is clicked.
 * @prop {string}   activePath — Optional override to force a specific nav item active.
 */
import React from 'react';
import { NavLink, useLocation } from 'react-router-dom';
import {
    Home,
    Briefcase,
    ClipboardList,
    GraduationCap,
    User,
    LogOut,
} from 'lucide-react';

const navItems = [
    { to: '/dashboard', label: 'Beranda', icon: Home },
    { to: '/portal', label: 'Portal Magang', icon: Briefcase },
    { to: '/bimbingan', label: 'Bimbingan & Logbook', icon: ClipboardList },
    { to: '/sidang', label: 'Sidang Magang', icon: GraduationCap },
    { to: '/form1', label: 'Profil', icon: User },
];


export default function Sidebar({ nim = '—', onLogout, activePath }) {
    const location = useLocation();

    const isActive = (path) => {
        const current = activePath || location.pathname;
        return current === path || current.startsWith(path + '/');
    };

    return (
        <aside className="fixed left-0 top-0 z-40 flex h-screen w-[260px] flex-col bg-primary">
            {/* ── Logo ──────────────────────────── */}
            <div className="px-6 pb-6 pt-8">
                <h1 className="text-2xl font-bold leading-tight text-white">
                    SIPMAG
                </h1>
                <span className="text-xs text-white/60">by PPAIP</span>
            </div>

            {/* ── Navigation ────────────────────── */}
            <nav className="flex flex-1 flex-col gap-1 px-3">
                {navItems.map(({ to, label, icon: Icon }) => {
                    const active = isActive(to);

                    return (
                        <NavLink
                            key={to}
                            to={to}
                            className={`
                                group flex items-center gap-3 rounded-lg px-4 py-3
                                text-sm font-medium transition-colors duration-150
                                ${active
                                    ? 'border-l-[3px] border-white bg-white/10 font-semibold text-white'
                                    : 'border-l-[3px] border-transparent text-white/70 hover:bg-white/10 hover:text-white'
                                }
                            `}
                        >
                            <Icon className="h-[18px] w-[18px] flex-shrink-0" />
                            <span>{label}</span>
                        </NavLink>
                    );
                })}
            </nav>

            {/* ── Bottom section ─────────────────── */}
            <div className="mt-auto border-t border-white/10 px-6 py-5">
                <div className="mb-3 flex items-center gap-2 text-xs text-white/60">
                    <Briefcase className="h-4 w-4" />
                    <span>NIM: {nim}</span>
                </div>
                <button
                    type="button"
                    onClick={onLogout}
                    className="flex items-center gap-2 text-sm text-white/60 transition-colors hover:text-white"
                >
                    <LogOut className="h-4 w-4" />
                    <span>Keluar</span>
                </button>
            </div>
        </aside>
    );
}
