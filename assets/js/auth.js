/**
 * auth.js – Session helpers
 */
const Auth = {
    getToken: () => localStorage.getItem('sia_token'),
    getUser: () => { try { return JSON.parse(localStorage.getItem('sia_user') || 'null'); } catch { return null; } },
    isLoggedIn: () => !!Auth.getToken(),

    setSession(token, user, profile = null) {
        localStorage.setItem('sia_token', token);
        const session = profile ? { ...user, profile } : user;
        localStorage.setItem('sia_user', JSON.stringify(session));
    },

    getProfile() {
        const user = Auth.getUser();
        return user?.profile ?? null;
    },

    clearSession() {
        localStorage.removeItem('sia_token');
        localStorage.removeItem('sia_user');
    },

    /** Redirect to login if not authenticated */
    requireLogin() {
        if (!Auth.isLoggedIn()) {
            window.location.href = this._loginPath();
        }
    },

    /** Redirect to dashboard if already logged in */
    requireGuest() {
        if (Auth.isLoggedIn()) {
            window.location.href = this._dashPath();
        }
    },

    /** Disallow access if not one of the allowed roles */
    requireRole(allowedRoles) {
        const user = Auth.getUser();
        if (!user || !allowedRoles.includes(user.role)) {
            window.location.href = this._dashPath();
        }
    },

    logout() {
        Auth.clearSession();
        window.location.href = Auth._loginPath();
    },

    _loginPath() {
        const p = window.location.pathname;
        return p.includes('/pages/') ? 'login.html' : 'pages/login.html';
    },
    _dashPath() {
        const p = window.location.pathname;
        return p.includes('/pages/') ? 'dashboard.html' : 'pages/dashboard.html';
    },
};
