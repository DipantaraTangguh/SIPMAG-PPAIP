/**
 * components/simulation/panels/AuthControls.jsx
 * Login/logout buttons for the simulation panel.
 */
import React from 'react';

export default function AuthControls({ sim, navigate }) {
    return (
        <div>
            <p className="mb-1.5 text-[10px] font-bold uppercase tracking-wider text-gray-500">
                Auth
            </p>
            {!sim.isLoggedIn ? (
                <button
                    type="button"
                    onClick={() => { sim.login('1234567890'); navigate('/dashboard'); }}
                    className="w-full rounded-lg bg-blue-600 px-3 py-2 text-xs font-bold text-white hover:bg-blue-700"
                >
                    🔓 Paksa Login
                </button>
            ) : (
                <button
                    type="button"
                    onClick={() => { sim.logout(); navigate('/login'); }}
                    className="w-full rounded-lg bg-gray-600 px-3 py-2 text-xs font-bold text-white hover:bg-gray-500"
                >
                    🔒 Logout
                </button>
            )}
        </div>
    );
}
