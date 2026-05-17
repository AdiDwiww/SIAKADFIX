/**
 * notifications.js – Toast & confirm-dialog components
 */

/* ─── TOAST ──────────────────────────────────────────── */
const Toast = {
  _icons: { success: ICON.checkCircle, error: ICON.xCircle, warning: ICON.alertTriangle, info: ICON.info },

  show(message, type = 'success', title = null, duration = 3500) {
    let container = document.getElementById('toast-container');
    if (!container) {
      container = document.createElement('div');
      container.id = 'toast-container';
      document.body.appendChild(container);
    }

    const t = document.createElement('div');
    t.className = `toast ${type}`;
    t.innerHTML = `
      <span class="toast-icon">${this._icons[type] || ICON.info}</span>
      <div class="toast-body">
        ${title ? `<div class="toast-title">${title}</div>` : ''}
        <div class="toast-msg">${message}</div>
      </div>
      <button class="toast-close" onclick="this.parentElement.remove()">×</button>`;

    container.appendChild(t);

    setTimeout(() => {
      t.classList.add('removing');
      t.addEventListener('animationend', () => t.remove());
    }, duration);
  },

  success: (msg, title) => Toast.show(msg, 'success', title),
  error: (msg, title) => Toast.show(msg, 'error', title),
  warning: (msg, title) => Toast.show(msg, 'warning', title),
  info: (msg, title) => Toast.show(msg, 'info', title),
};

/* ─── CONFIRM DIALOG ────────────────────────────────── */
const Confirm = {
  show(message, onConfirm, title = 'Konfirmasi', dangerBtn = 'Ya, Hapus') {
    const existing = document.getElementById('confirm-dialog');
    if (existing) existing.remove();

    const overlay = document.createElement('div');
    overlay.id = 'confirm-dialog';
    overlay.className = 'modal-overlay';
    overlay.innerHTML = `
      <div class="modal modal-sm">
        <div class="modal-header">
          <h2><span style="display:inline-block;width:20px;vertical-align:middle;margin-right:6px">${ICON.alertTriangle}</span> ${title}</h2>
          <button class="btn-modal-close" onclick="Confirm.close()">×</button>
        </div>
        <div class="modal-body">
          <p style="color:var(--text-muted);line-height:1.6">${message}</p>
        </div>
        <div class="modal-footer">
          <button class="btn btn-secondary" onclick="Confirm.close()">Batal</button>
          <button class="btn btn-danger"    id="confirm-ok">${dangerBtn}</button>
        </div>
      </div>`;

    document.body.appendChild(overlay);
    overlay.onclick = e => { if (e.target === overlay) Confirm.close(); };

    document.getElementById('confirm-ok').onclick = () => {
      Confirm.close();
      onConfirm();
    };
  },

  close() {
    const d = document.getElementById('confirm-dialog');
    if (d) d.remove();
  },
};

/* ─── LOADING OVERLAY ───────────────────────────────── */
const Loading = {
  show() {
    if (document.getElementById('loading-overlay')) return;
    const el = document.createElement('div');
    el.id = 'loading-overlay';
    el.className = 'loading-overlay';
    el.innerHTML = '<div class="spinner"></div>';
    document.body.appendChild(el);
  },
  hide() {
    const el = document.getElementById('loading-overlay');
    if (el) el.remove();
  },
};
