import React, { useState } from 'react';
import { useNavigate } from 'react-router-dom';
import { useSimulation } from '../../context/SimulationContext';
import { FullScreenSpinner } from '../../components/LoadingSpinner';

/* ── Icon components ─────────────────────────────── */
const PersonIcon = () => (
    <svg xmlns="http://www.w3.org/2000/svg" className="w-5 h-5 text-gray-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.8" strokeLinecap="round" strokeLinejoin="round">
        <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2" />
        <circle cx="12" cy="7" r="4" />
    </svg>
);

const LockIcon = () => (
    <svg xmlns="http://www.w3.org/2000/svg" className="w-5 h-5 text-gray-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.8" strokeLinecap="round" strokeLinejoin="round">
        <rect x="3" y="11" width="18" height="11" rx="2" ry="2" />
        <path d="M7 11V7a5 5 0 0 1 10 0v4" />
    </svg>
);

const EyeIcon = () => (
    <svg xmlns="http://www.w3.org/2000/svg" className="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.8" strokeLinecap="round" strokeLinejoin="round">
        <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z" />
        <circle cx="12" cy="12" r="3" />
    </svg>
);

const EyeOffIcon = () => (
    <svg xmlns="http://www.w3.org/2000/svg" className="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.8" strokeLinecap="round" strokeLinejoin="round">
        <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24" />
        <line x1="1" y1="1" x2="23" y2="23" />
    </svg>
);

const ArrowIcon = () => (
    <svg xmlns="http://www.w3.org/2000/svg" className="w-4 h-4 ml-2 transition-transform duration-300 group-hover:translate-x-1" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round">
        <line x1="5" y1="12" x2="19" y2="12" />
        <polyline points="12 5 19 12 12 19" />
    </svg>
);

const SpinnerIcon = () => (
    <svg className="w-5 h-5 animate-spin" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
        <circle className="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" strokeWidth="4" />
        <path className="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z" />
    </svg>
);

/* ── Main Component ──────────────────────────────── */
export default function LoginPage() {
    const { login } = useSimulation();
    const navigate = useNavigate();

    const [nim, setNim] = useState('');
    const [password, setPassword] = useState('');
    const [showPassword, setShowPassword] = useState(false);
    const [rememberMe, setRememberMe] = useState(false);
    const [isSubmitting, setIsSubmitting] = useState(false);
    const [showFullScreen, setShowFullScreen] = useState(false);
    const [error, setError] = useState('');

    const handleSubmit = async (e) => {
        e.preventDefault();
        setError('');

        if (!nim.trim() || !password.trim()) {
            setError('NIM dan Kata Sandi wajib diisi.');
            return;
        }

        setIsSubmitting(true);

        try {
            const result = await login(nim.trim(), password);
            if (result.success) {
                setShowFullScreen(true);
                setTimeout(() => navigate('/dashboard', { replace: true }), 400);
            } else {
                setError(result.error);
                setIsSubmitting(false);
            }
        } catch (err) {
            setError(err.message || 'Terjadi kesalahan. Coba lagi.');
            setIsSubmitting(false);
        }
    };

    if (showFullScreen) return <FullScreenSpinner />;

    return (
        <div className="login-page">
            {/* ── Left Panel: Hero ──────────────── */}
            <div
                className="login-hero"
                style={{ backgroundImage: "url('/assets/images/plaza-festival.png')", backgroundSize: 'cover', backgroundPosition: 'center' }}
            >
                <div className="login-hero__overlay" />
                <div className="login-hero__content">
                    {/* Logo */}
                    <div className="login-hero__logo">
                        <div className="login-hero__logo-ubakrie">
                            <img src="/assets/images/logo-ubakrie.png" alt="Logo Universitas Bakrie" />
                        </div>
                    </div>

                    <h1 className="login-hero__heading">
                        Gerbang Karir Profesional Mahasiswa Universitas Bakrie
                    </h1>
                    <p className="login-hero__sub">
                        Kelola administrasi magang, telusuri lowongan eksklusif, dan bangun portofolio profesional dalam satu ekosistem akademik yang terintegrasi.
                    </p>
                </div>
            </div>

            {/* ── Right Panel: Form ─────────────── */}
            <div className="login-form-panel">
                <div className="login-form-wrapper">
                    {/* Header */}
                    <div className="login-form__header">
                        <h2 className="login-form__title">Selamat Datang</h2>
                        <p className="login-form__subtitle">Masuk dengan akun BIG Anda.</p>
                    </div>

                    {/* Hint for demo login */}
                    <div className="mb-4 rounded-lg bg-blue-50 border border-blue-200 px-4 py-3 text-xs text-blue-700">
                        <strong>Demo:</strong> NIM <code className="rounded bg-blue-100 px-1 font-mono">1101214230</code> · Password <code className="rounded bg-blue-100 px-1 font-mono">password</code>
                    </div>

                    {/* Error Alert */}
                    {error && (
                        <div className="login-form__error" role="alert">
                            <svg className="w-4 h-4 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                <path fillRule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clipRule="evenodd" />
                            </svg>
                            <span>{error}</span>
                        </div>
                    )}

                    {/* Form */}
                    <form onSubmit={handleSubmit} className="login-form" id="login-form" autoComplete="off">
                        {/* NIM Field */}
                        <div className="login-field">
                            <label htmlFor="nim" className="login-field__label">
                                Nomor Induk Mahasiswa (NIM)
                            </label>
                            <div className="login-field__input-wrapper">
                                <span className="login-field__icon login-field__icon--left"><PersonIcon /></span>
                                <input
                                    id="nim"
                                    type="text"
                                    placeholder="NIM"
                                    value={nim}
                                    onChange={(e) => setNim(e.target.value)}
                                    className="login-field__input"
                                    autoComplete="username"
                                    disabled={isSubmitting}
                                />
                            </div>
                        </div>

                        {/* Password Field */}
                        <div className="login-field">
                            <div className="login-field__label-row">
                                <label htmlFor="password" className="login-field__label">
                                    Kata Sandi
                                </label>
                                <button type="button" className="login-field__forgot" tabIndex={-1}>
                                    Lupa Sandi?
                                </button>
                            </div>
                            <div className="login-field__input-wrapper">
                                <span className="login-field__icon login-field__icon--left"><LockIcon /></span>
                                <input
                                    id="password"
                                    type={showPassword ? 'text' : 'password'}
                                    placeholder="password"
                                    value={password}
                                    onChange={(e) => setPassword(e.target.value)}
                                    className="login-field__input login-field__input--password"
                                    autoComplete="current-password"
                                    disabled={isSubmitting}
                                />
                                <button
                                    type="button"
                                    className="login-field__icon login-field__icon--right login-field__toggle"
                                    onClick={() => setShowPassword(!showPassword)}
                                    tabIndex={-1}
                                    aria-label={showPassword ? 'Sembunyikan sandi' : 'Tampilkan sandi'}
                                >
                                    {showPassword ? <EyeOffIcon /> : <EyeIcon />}
                                </button>
                            </div>
                        </div>

                        {/* Remember Me */}
                        <label className="login-form__remember" htmlFor="remember">
                            <input
                                id="remember"
                                type="checkbox"
                                checked={rememberMe}
                                onChange={(e) => setRememberMe(e.target.checked)}
                                disabled={isSubmitting}
                            />
                            <span>Ingat saya</span>
                        </label>

                        {/* Submit */}
                        <button
                            type="submit"
                            className="login-form__submit group"
                            id="login-submit"
                            disabled={isSubmitting}
                        >
                            {isSubmitting ? (
                                <>
                                    <SpinnerIcon />
                                    <span className="ml-2">Memproses…</span>
                                </>
                            ) : (
                                <>
                                    Masuk Ke Portal
                                    <ArrowIcon />
                                </>
                            )}
                        </button>
                    </form>

                    {/* Footer */}
                    <div className="login-form__footer">
                        <p>
                            Belum memiliki akses?{' '}
                            <a href="mailto:akademik@bakrie.ac.id" className="login-form__footer-link">
                                Hubungi Biro Akademik
                            </a>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    );
}
