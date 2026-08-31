import React, { useEffect, useRef, useState } from "react";
import { LogOut } from "lucide-react";
import { useAuth } from "../../context/AppContext";
import Sidebar from "./Sidebar";

interface DashboardLayoutProps {
    children: React.ReactNode;
    pageTitle?: string;
    activePath?: string;
}

export default function DashboardLayout({
    children,
    pageTitle = "Sistem Informasi Portal Magang",
    activePath,
}: DashboardLayoutProps) {
    const { student, logout } = useAuth();
    const [menuOpen, setMenuOpen] = useState(false);
    const menuRef = useRef<HTMLDivElement>(null);

    // Menu tanpa jalan tutup selain tombolnya sendiri gampang terjebak di
    // layar sentuh, jadi klik di luar dan Escape ikut menutupnya. Pendengarnya
    // hanya dipasang selagi menu terbuka.
    useEffect(() => {
        if (!menuOpen) {
            return;
        }

        const closeOnOutside = (event: PointerEvent) => {
            if (!menuRef.current?.contains(event.target as Node)) {
                setMenuOpen(false);
            }
        };
        const closeOnEscape = (event: KeyboardEvent) => {
            if (event.key === "Escape") {
                setMenuOpen(false);
            }
        };

        document.addEventListener("pointerdown", closeOnOutside);
        document.addEventListener("keydown", closeOnEscape);

        return () => {
            document.removeEventListener("pointerdown", closeOnOutside);
            document.removeEventListener("keydown", closeOnEscape);
        };
    }, [menuOpen]);

    const studentName = student?.name ?? "-";
    const nim = student?.nim ?? "-";
    const initials = studentName
        .split(" ")
        .map((w) => w[0])
        .join("")
        .slice(0, 2)
        .toUpperCase();

    const avatar = (
        <span className="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-primary text-sm font-semibold text-white">
            {initials}
        </span>
    );

    return (
        <div className="min-h-screen bg-[#F8F9FB]">
            <Sidebar nim={nim} onLogout={logout} activePath={activePath} />
            <div className="min-h-screen lg:ml-65">
                <header className="flex items-center justify-between gap-4 px-4 py-5 sm:px-6 lg:px-10 lg:py-7">
                    <h2 className="min-w-0 text-xl font-bold leading-tight text-gray-900 sm:text-2xl lg:text-[1.75rem]">
                        {pageTitle}
                    </h2>

                    {/* Di layar lebar NIM dan tombol keluar sudah ada di kaki
                        sidebar. Kaki itu hilang pada mobile karena sidebar-nya
                        berubah jadi bilah bawah, jadi keduanya dipindahkan ke
                        menu di balik foto profil. Avatar-nya sendiri tetap
                        tampil polos di desktop supaya tidak ada dua jalan
                        keluar yang berbeda di satu layar. */}
                    <div ref={menuRef} className="relative shrink-0">
                        <button
                            type="button"
                            onClick={() => setMenuOpen((open) => !open)}
                            aria-haspopup="menu"
                            aria-expanded={menuOpen}
                            aria-label="Menu akun"
                            className="flex h-11 w-11 items-center justify-center rounded-full transition-colors hover:bg-gray-200 lg:hidden"
                        >
                            {avatar}
                        </button>
                        <span className="hidden lg:block">{avatar}</span>

                        {menuOpen && (
                            <div
                                role="menu"
                                className="absolute right-0 top-full z-50 mt-2 w-52 overflow-hidden rounded-xl border border-gray-200 bg-white shadow-lg lg:hidden"
                            >
                                <p className="truncate border-b border-gray-100 px-4 py-3 text-xs text-gray-500">
                                    NIM {nim}
                                </p>
                                <button
                                    type="button"
                                    role="menuitem"
                                    onClick={logout}
                                    className="flex w-full items-center gap-2 px-4 py-3 text-left text-sm text-gray-700 transition-colors hover:bg-gray-50"
                                >
                                    <LogOut className="h-4 w-4" />
                                    <span>Keluar</span>
                                </button>
                            </div>
                        )}
                    </div>
                </header>
                <main className="px-4 pb-24 sm:px-6 lg:px-10 lg:pb-10">
                    <div className="animate-fadeIn">{children}</div>
                </main>
            </div>
        </div>
    );
}
