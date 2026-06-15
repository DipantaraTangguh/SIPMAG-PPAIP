import React from 'react';

interface ErrorBoundaryProps {
    children: React.ReactNode;
    fallback?: React.ReactNode;
}

interface ErrorBoundaryState {
    hasError: boolean;
    error: Error | null;
    errorInfo: React.ErrorInfo | null;
}

/**
 * ErrorBoundary — REL-01
 *
 * Catches render / lifecycle errors in the subtree below this component
 * and shows a friendly fallback UI instead of a white screen.
 *
 * Usage:
 *   <ErrorBoundary>
 *     <SomeComponent />
 *   </ErrorBoundary>
 *
 * Optional:
 *   <ErrorBoundary fallback={<p>Custom message</p>}>
 *     <SomeComponent />
 *   </ErrorBoundary>
 */
export default class ErrorBoundary extends React.Component<
    ErrorBoundaryProps,
    ErrorBoundaryState
> {
    constructor(props: ErrorBoundaryProps) {
        super(props);
        this.state = { hasError: false, error: null, errorInfo: null };
        this.handleReset = this.handleReset.bind(this);
    }

    static getDerivedStateFromError(error: Error): Partial<ErrorBoundaryState> {
        return { hasError: true, error };
    }

    componentDidCatch(error: Error, errorInfo: React.ErrorInfo) {
        // Log to the browser console during development.
        // Replace with a real logging service (e.g. Sentry) in production.
        if (import.meta.env.DEV) {
            console.error('[ErrorBoundary] Caught an error:', error, errorInfo);
        }
        this.setState({ errorInfo });
    }

    handleReset() {
        this.setState({ hasError: false, error: null, errorInfo: null });
    }

    render() {
        const { hasError, error, errorInfo } = this.state;
        const { children, fallback } = this.props;

        if (!hasError) {
            return children;
        }

        // Allow callers to supply a fully custom fallback.
        if (fallback) {
            return fallback;
        }

        return (
            <div style={styles.overlay}>
                <div style={styles.card}>
                    {/* Icon */}
                    <div style={styles.iconWrap}>
                        <svg style={styles.icon} viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2">
                            <circle cx="12" cy="12" r="10" />
                            <line x1="12" y1="8" x2="12" y2="12" />
                            <line x1="12" y1="16" x2="12.01" y2="16" />
                        </svg>
                    </div>

                    <h1 style={styles.heading}>Terjadi Kesalahan</h1>
                    <p style={styles.subheading}>
                        Halaman ini mengalami error tak terduga. Coba muat ulang atau kembali ke Dashboard.
                    </p>

                    {/* Dev-only technical details — never shown in production */}
                    {import.meta.env.DEV && error && (
                        <details style={styles.details}>
                            <summary style={styles.summary}>Detail teknis (dev only)</summary>
                            <pre style={styles.pre}>
                                {error.toString()}
                                {errorInfo?.componentStack}
                            </pre>
                        </details>
                    )}

                    <div style={styles.actions}>
                        <button onClick={this.handleReset} style={styles.btnPrimary}>
                            ↺ Coba Lagi
                        </button>
                        <a href="/dashboard" style={styles.btnSecondary}>
                            ← Ke Dashboard
                        </a>
                    </div>
                </div>
            </div>
        );
    }
}

// ---------------------------------------------------------------------------
// Inline styles — no external CSS dependency so the boundary works even when
// the stylesheet itself fails to load.
// ---------------------------------------------------------------------------
const styles: Record<string, React.CSSProperties> = {
    overlay: {
        minHeight: '100vh',
        display: 'flex',
        alignItems: 'center',
        justifyContent: 'center',
        background: 'linear-gradient(135deg, #0f172a 0%, #1e293b 100%)',
        fontFamily: "'Inter', system-ui, sans-serif",
        padding: '1.5rem',
    },
    card: {
        background: 'rgba(255,255,255,0.05)',
        border: '1px solid rgba(255,255,255,0.12)',
        borderRadius: '1rem',
        padding: '2.5rem',
        maxWidth: '480px',
        width: '100%',
        textAlign: 'center',
        backdropFilter: 'blur(12px)',
        boxShadow: '0 25px 50px rgba(0,0,0,0.5)',
    },
    iconWrap: {
        display: 'flex',
        justifyContent: 'center',
        marginBottom: '1.25rem',
    },
    icon: {
        width: '3rem',
        height: '3rem',
        color: '#f87171',
    },
    heading: {
        margin: '0 0 0.5rem',
        fontSize: '1.5rem',
        fontWeight: 700,
        color: '#f1f5f9',
    },
    subheading: {
        margin: '0 0 1.5rem',
        fontSize: '0.9375rem',
        color: '#94a3b8',
        lineHeight: 1.6,
    },
    details: {
        marginBottom: '1.5rem',
        textAlign: 'left',
    },
    summary: {
        cursor: 'pointer',
        color: '#64748b',
        fontSize: '0.8125rem',
        marginBottom: '0.5rem',
    },
    pre: {
        background: 'rgba(0,0,0,0.4)',
        borderRadius: '0.5rem',
        padding: '0.75rem',
        fontSize: '0.75rem',
        color: '#fca5a5',
        overflowX: 'auto',
        whiteSpace: 'pre-wrap',
        wordBreak: 'break-word',
    },
    actions: {
        display: 'flex',
        gap: '0.75rem',
        justifyContent: 'center',
        flexWrap: 'wrap',
    },
    btnPrimary: {
        padding: '0.625rem 1.25rem',
        borderRadius: '0.5rem',
        border: 'none',
        background: '#3b82f6',
        color: '#fff',
        fontWeight: 600,
        fontSize: '0.9375rem',
        cursor: 'pointer',
        transition: 'background 0.2s',
    },
    btnSecondary: {
        padding: '0.625rem 1.25rem',
        borderRadius: '0.5rem',
        border: '1px solid rgba(255,255,255,0.2)',
        background: 'transparent',
        color: '#cbd5e1',
        fontWeight: 600,
        fontSize: '0.9375rem',
        cursor: 'pointer',
        textDecoration: 'none',
        display: 'inline-block',
    },
};
