const API_BASE = '/api';
const TOKEN_KEY = 'portal_magang_token';

export function getToken() {
    return localStorage.getItem(TOKEN_KEY);
}

export function setToken(token) {
    localStorage.setItem(TOKEN_KEY, token);
}

export function clearToken() {
    localStorage.removeItem(TOKEN_KEY);
}
async function request(endpoint, options = {}) {
    const token = getToken();
    const headers = {
        'Accept': 'application/json',
        ...options.headers,
    };

    // Jangan paksa Content-Type, browser yang set boundary upload file.
    if (!(options.body instanceof FormData)) {
        headers['Content-Type'] = 'application/json';
    }

    if (token) {
        headers['Authorization'] = `Bearer ${token}`;
    }

    const response = await fetch(`${API_BASE}${endpoint}`, {
        ...options,
        headers,
    });

    // Token mati, langsung bersihin session user.
    if (response.status === 401) {
        clearToken();
        window.location.href = '/login';
        throw new Error('Sesi berakhir. Silakan login kembali.');
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
    get:    (url)        => request(url, { method: 'GET' }),
    post:   (url, body)  => request(url, { method: 'POST', body: JSON.stringify(body) }),
    put:    (url, body)  => request(url, { method: 'PUT', body: JSON.stringify(body) }),
    delete: (url)        => request(url, { method: 'DELETE' }),
    upload: (url, formData) => request(url, { method: 'POST', body: formData }),
    download: async (url, filename) => {
        const token = getToken();
        const response = await fetch(`${API_BASE}${url}`, {
            method: 'GET',
            headers: token ? { 'Authorization': `Bearer ${token}` } : {},
        });

        if (response.status === 401) {
            clearToken();
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
    login: async (login, password) => {
        const data = await request('/login', {
            method: 'POST',
            body: JSON.stringify({ login, password }),
        });
        if (data.token) {
            setToken(data.token);
        }
        return data;
    },
    logout: async () => {
        try {
            await request('/logout', { method: 'POST' });
        } catch { /* aman di-skip */ }
        clearToken();
    },
};
