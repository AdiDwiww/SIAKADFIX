/**
 * layout.js – Renders responsive sidebar + topbar
 */
const Layout = {
  init(pageTitle = '') {
    Auth.requireLogin();
    const user = Auth.getUser();

    // Inject sidebar
    const sidebarEl = document.getElementById('sidebar');
    if (sidebarEl) sidebarEl.innerHTML = this._buildSidebar(user);

    // Inject topbar info
    const topUser = document.getElementById('topbar-user');
    if (topUser) topUser.textContent = user.username + ' (' + user.role + ')';

    // Set page title
    const pt = document.getElementById('page-title');
    if (pt && pageTitle) pt.textContent = pageTitle;

    // mobile overlay
    document.addEventListener('click', e => {
      const s = document.getElementById('sidebar');
      if (s && window.innerWidth <= 768 && !s.contains(e.target)
        && !e.target.closest('#btn-menu-toggle')) {
        s.classList.remove('mobile-open');
      }
    });
  },

  toggleSidebar() {
    const s = document.getElementById('sidebar');
    if (!s) return;
    if (window.innerWidth <= 768) s.classList.toggle('mobile-open');
    else s.classList.toggle('collapsed');

    const main = document.getElementById('main-content');
    if (main) main.classList.toggle('full');
  },

  _buildSidebar(user) {
    const page = location.pathname.split('/').pop();
    const allMenus = [
      { label: 'Dashboard', page: 'dashboard.html', icon: ICON.home, roles: ['ADMIN', 'DOSEN', 'MAHASISWA'] },
      { label: 'Mahasiswa', page: 'mahasiswa.html', icon: ICON.users, roles: ['ADMIN', 'DOSEN'] },
      { label: 'Dosen', page: 'dosen.html', icon: ICON.user, roles: ['ADMIN'] },
      { label: 'Mata Kuliah', page: 'kuliah.html', icon: ICON.book, roles: ['ADMIN', 'DOSEN', 'MAHASISWA'] },
      { label: 'Ganti Password', page: 'change-password.html', icon: ICON.key, roles: ['ADMIN', 'DOSEN', 'MAHASISWA'] },
    ];

    const items = allMenus
      .filter(m => m.roles.includes(user.role))
      .map(m => `
        <a href="${m.page}" class="nav-item ${page === m.page ? 'active' : ''}">
          <span class="nav-icon" style="font-size:18px">${m.icon}</span>
          <span>${m.label}</span>
        </a>`)
      .join('');

    const initials = user.username.charAt(0).toUpperCase();
    return `
      <div class="sidebar-header">
        <div class="sidebar-logo">
          <div class="logo-icon">${ICON.award}</div>
          <span>SIA<br>AKADEMIK</span>
        </div>
      </div>
      <nav class="sidebar-nav">
        <div class="nav-section-label">Menu Utama</div>
        ${items}
      </nav>
      <div class="sidebar-footer">
        <div class="sidebar-avatar">${initials}</div>
        <div class="sidebar-user-info">
          <div class="sidebar-username">${user.username}</div>
          <div class="sidebar-role">${user.role}</div>
        </div>
        <button class="btn-sidebar-logout" onclick="Auth.logout()" title="Logout">
          <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2"
               viewBox="0 0 24 24"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/>
          <polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
        </button>
      </div>`;
  },
};
