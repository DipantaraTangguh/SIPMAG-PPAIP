/**
 * Sidebar.jsx
 * Responsive sidebar navigation for the Portal Magang dashboard.
 * Desktop uses a fixed 260px rail; mobile uses a bottom tab bar.
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
    { to: '/guidance', label: 'Bimbingan & Logbook', icon: ClipboardList },
    { to: '/defense', label: 'Sidang Magang', icon: GraduationCap },
    { to: '/form1', label: 'Profil', icon: User },
];


export default function Sidebar({ nim = '—', onLogout, activePath }) {
    const location = useLocation();

    const isActive = (path) => {
        const current = activePath || location.pathname;
        return current === path || current.startsWith(path + '/');
    };

    return (
        <aside className="fixed bottom-0 left-0 right-0 z-40 flex h-16 border-t border-primary-dark bg-primary shadow-[0_-8px_24px_rgba(0,0,0,0.12)] lg:bottom-auto lg:right-auto lg:top-0 lg:h-screen lg:w-[260px] lg:flex-col lg:border-t-0 lg:shadow-none">
            {/* ── Logo ──────────────────────────── */}
            <div className="hidden px-6 pb-6 pt-8 lg:block">
                <h1 className="text-2xl font-bold leading-tight text-white">
                    Portal Magang
                </h1>
                <span className="text-xs text-white/60">by PPAIP</span>
            </div>

            {/* ── Navigation ────────────────────── */}
            <nav className="flex min-w-0 flex-1 items-stretch justify-around gap-0 overflow-x-auto px-1 lg:flex-col lg:justify-start lg:gap-1 lg:px-3">
                {navItems.map(({ to, label, icon: Icon }) => {
                    const active = isActive(to);

                    return (
                        <NavLink
                            key={to}
                            to={to}
                            className={`
                                group flex min-w-[68px] flex-1 flex-col items-center justify-center gap-1 rounded-lg px-2 py-2
                                text-[10px] font-medium leading-tight transition-colors duration-150
                                lg:min-w-0 lg:flex-none lg:flex-row lg:justify-start lg:gap-3 lg:px-4 lg:py-3 lg:text-sm
                                ${active
                                    ? 'bg-white/10 font-semibold text-white lg:border-l-[3px] lg:border-white'
                                    : 'text-white/70 hover:bg-white/10 hover:text-white lg:border-l-[3px] lg:border-transparent'
                                }
                            `}
                        >
                            <Icon className="h-[18px] w-[18px] flex-shrink-0" />
                            <span className="line-clamp-2 text-center lg:text-left">{label}</span>
                        </NavLink>
                    );
                })}
            </nav>

            {/* ── Bottom section ─────────────────── */}
            <div className="mt-auto hidden border-t border-white/10 px-6 py-5 lg:block">
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
