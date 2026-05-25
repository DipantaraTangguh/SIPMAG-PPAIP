import React, { useState } from 'react';
import { useNavigate } from 'react-router-dom';
import { useSimulation } from '../../context/SimulationContext';
import { FullScreenSpinner } from '../../Components/Elements/LoadingSpinner';
import {
    User,
    Lock,
    Eye,
    EyeOff,
    ArrowRight,
    Loader2,
    XCircle,
} from 'lucide-react';


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
                            <XCircle className="w-4 h-4 flex-shrink-0" />
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
                                <span className="login-field__icon login-field__icon--left"><User className="w-5 h-5 text-gray-400" /></span>
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
                                <span className="login-field__icon login-field__icon--left"><Lock className="w-5 h-5 text-gray-400" /></span>
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
                                    {showPassword ? <EyeOff className="w-5 h-5" /> : <Eye className="w-5 h-5" />}
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
                                    <Loader2 className="w-5 h-5 animate-spin" />
                                    <span className="ml-2">Memproses…</span>
                                </>
                            ) : (
                                <>
                                    Masuk Ke Portal
                                    <ArrowRight className="w-4 h-4 ml-2 transition-transform duration-300 group-hover:translate-x-1" />
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
