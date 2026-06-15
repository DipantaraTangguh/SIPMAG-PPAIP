const API_BASE = '/api';
const UNSAFE_METHODS = new Set(['POST', 'PUT', 'PATCH', 'DELETE']);

function getCookie(name) {
    const prefix = `${encodeURIComponent(name)}=`;
    const cookie = document.cookie
        .split('; ')
        .find((part) => part.startsWith(prefix));

    return cookie ? decodeURIComponent(cookie.slice(prefix.length)) : null;
}

async function ensureCsrfCookie() {
    if (getCookie('XSRF-TOKEN')) return;

    const response = await fetch('/sanctum/csrf-cookie', {
        credentials: 'same-origin',
        headers: { Accept: 'application/json' },
    });

    if (!response.ok) {
        throw new Error('Tidak dapat menyiapkan sesi aman. Silakan muat ulang halaman.');
    }
}

async function request(endpoint, options = {}) {
    const method = (options.method || 'GET').toUpperCase();

    if (UNSAFE_METHODS.has(method)) {
        await ensureCsrfCookie();
    }

    const headers = {
        Accept: 'application/json',
        ...options.headers,
    };

    // Jangan paksa Content-Type, browser yang set boundary upload file.
    if (!(options.body instanceof FormData)) {
        headers['Content-Type'] = 'application/json';
    }

    const csrfToken = getCookie('XSRF-TOKEN');
    if (csrfToken && UNSAFE_METHODS.has(method)) {
        headers['X-XSRF-TOKEN'] = csrfToken;
    }

    const response = await fetch(`${API_BASE}${endpoint}`, {
        ...options,
        method,
        credentials: 'same-origin',
        headers,
    });

    if (response.status === 401) {
        if (window.location.pathname !== '/login') {
            window.location.href = '/login';
        }
        throw new Error('Sesi berakhir. Silakan login kembali.');
    }

    if (response.status === 419) {
        throw new Error('Sesi keamanan berakhir. Silakan muat ulang halaman.');
    }

    const data = await response.json().catch(() => null);

    if (!response.ok) {
        let message = response.statusText;
        if (data?.message) {
            message = data.message;
        } else if (data?.errors) {
            message = Object.values(data.errors).flat().join(', ');
        }
        throw new Error(message);
    }

    return data;
}

export const api = {
    get: (url) => request(url, { method: 'GET' }),
    post: (url, body) => request(url, { method: 'POST', body: JSON.stringify(body) }),
    put: (url, body) => request(url, { method: 'PUT', body: JSON.stringify(body) }),
    delete: (url) => request(url, { method: 'DELETE' }),
    upload: (url, formData) => request(url, { method: 'POST', body: formData }),
    download: async (url, filename) => {
        const response = await fetch(`${API_BASE}${url}`, {
            method: 'GET',
            credentials: 'same-origin',
            headers: { Accept: 'application/json' },
        });

        if (response.status === 401) {
            window.location.href = '/login';
            throw new Error('Sesi berakhir. Silakan login kembali.');
        }
        if (!response.ok) {
            const data = await response.json().catch(() => null);
            throw new Error(data?.message || response.statusText);
        }

        const blob = await response.blob();
        const objectUrl = URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = objectUrl;
        a.download = filename || 'download.pdf';
        document.body.appendChild(a);
        a.click();
        a.remove();
        URL.revokeObjectURL(objectUrl);
    },
    login: (login, password) => request('/login', {
        method: 'POST',
        body: JSON.stringify({ login, password }),
    }),
    logout: async () => {
        try {
            await request('/logout', { method: 'POST' });
        } catch {
            // Session may already be expired.
        }
    },
};
