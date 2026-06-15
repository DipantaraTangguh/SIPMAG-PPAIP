export const fetchWithCsrf = async (url, options = {}) => {
    options.credentials = 'include';
    options.headers = {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        ...options.headers,
    };

    const getCookie = (name) => {
        const value = `; ${document.cookie}`;
        const parts = value.split(`; ${name}=`);
        if (parts.length === 2) return decodeURIComponent(parts.pop().split(';').shift());
    };

    const csrfToken = getCookie('XSRF-TOKEN');
    if (csrfToken) {
        options.headers['X-XSRF-TOKEN'] = csrfToken;
    }

    const response = await fetch(url, options);

    if (!response.ok) {
        let errorMessage = response.statusText;
        try {
            const errorBody = await response.json();
            errorMessage = errorBody.message || errorMessage;
        } catch(e) {}
        throw new Error(errorMessage);
    }

    return response.json();
};
