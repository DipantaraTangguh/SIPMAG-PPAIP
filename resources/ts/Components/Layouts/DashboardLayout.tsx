import React from "react";
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

    const studentName = student?.name ?? "-";
    const nim = student?.nim ?? "-";
    const initials = studentName
        .split(" ")
        .map((w) => w[0])
        .join("")
        .slice(0, 2)
        .toUpperCase();

    return (
        <div className="min-h-screen bg-[#F8F9FB]">
            <Sidebar nim={nim} onLogout={logout} activePath={activePath} />
            <div className="min-h-screen lg:ml-65">
                <header className="flex items-center justify-between gap-4 px-4 py-5 sm:px-6 lg:px-10 lg:py-7">
                    <h2 className="min-w-0 text-xl font-bold leading-tight text-gray-900 sm:text-2xl lg:text-[1.75rem]">
                        {pageTitle}
                    </h2>
                    <div className="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-primary text-sm font-semibold text-white">
                        {initials}
                    </div>
                </header>
                <main className="px-4 pb-24 sm:px-6 lg:px-10 lg:pb-10">
                    <div className="animate-fadeIn">{children}</div>
                </main>
            </div>
        </div>
    );
}
