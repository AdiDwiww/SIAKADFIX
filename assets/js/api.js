/**
 * api.js – Fetch wrapper with auto base-URL detection and Bearer auth
 */

const _getBase = () => {
  const { protocol, host, pathname } = window.location;
  const idx = pathname.lastIndexOf('/pages/');
  const root = idx !== -1 ? pathname.substring(0, idx) : '';
  return `${protocol}//${host}${root}`;
};

const _BASE_ROOT = _getBase();
window.BASE_ROOT = _BASE_ROOT;
const API_BASE = _BASE_ROOT + '/api';
const UPLOADS_URL = _BASE_ROOT + '/uploads/';
window.UPLOADS_URL = UPLOADS_URL;

const API = {
  async request(method, endpoint, body = null, isFormData = false) {
    const headers = {};
    const token = localStorage.getItem('sia_token');
    if (token) headers['Authorization'] = `Bearer ${token}`;
    if (!isFormData && body) headers['Content-Type'] = 'application/json';

    const opts = { method, headers };
    if (body) opts.body = isFormData ? body : JSON.stringify(body);

    try {
      const res = await fetch(API_BASE + endpoint, opts);
      const data = await res.json();
      return { ok: res.ok, status: res.status, ...data };
    } catch {
      return { ok: false, message: 'Koneksi gagal. Pastikan server XAMPP sudah berjalan.', data: null };
    }
  },

  get: (ep) => API.request('GET', ep),
  post: (ep, body, fd) => API.request('POST', ep, body, !!fd),
  put: (ep, body) => API.request('PUT', ep, body),
  delete: (ep, body) => API.request('DELETE', ep, body),

  /** Helper: returns full URL to a stored photo, or the default avatar */
  photoUrl(path) {
    if (!path) return window.BASE_ROOT + '/assets/img/avatar.png';
    return window.UPLOADS_URL + path;
  },
};
