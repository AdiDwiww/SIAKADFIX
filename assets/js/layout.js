/**
 * layout.js – Renders responsive dark-themed sidebar + topbar
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
    const displayName = user.profile?.nama || user.username;
    if (topUser) topUser.textContent = displayName + ' (' + user.role + ')';

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
      { label: 'Mata Kuliah', page: 'kuliah.html', icon: ICON.book, roles: ['ADMIN'] },
      
      // Menu Khusus Mahasiswa
      { label: 'KRS', page: 'krs.html', icon: ICON.clipboard, roles: ['MAHASISWA'] },
      { label: 'KHS / Transkrip', page: 'khs.html', icon: ICON.award, roles: ['MAHASISWA'] },
      { label: 'Jadwal Kuliah', page: 'jadwal.html', icon: ICON.calendar, roles: ['MAHASISWA'] },
      
      // Menu Khusus Dosen
      { label: 'Jadwal Mengajar', page: 'jadwal_dosen.html', icon: ICON.calendar, roles: ['DOSEN'] },
      { label: 'Input Nilai', page: 'input_nilai.html', icon: ICON.edit, roles: ['DOSEN'] },

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

    const displayName = user.profile?.nama || user.username;
    const initials = displayName.charAt(0).toUpperCase();
    return `
      <div class="sidebar-header">
        <div class="sidebar-logo">
          <span>SI<br>AKADEMIK</span>
        </div>
      </div>
      <div class="sidebar-search">
        <input type="text" class="sidebar-search-input" placeholder="Search menu…" oninput="Layout._filterNav(this.value)" style="background-image:url('data:image/svg+xml,%3Csvg width=%2214%22 height=%2214%22 viewBox=%220 0 24 24%22 fill=%22none%22 stroke=%22%2364748b%22 stroke-width=%222%22 xmlns=%22http://www.w3.org/2000/svg%22%3E%3Ccircle cx=%2211%22 cy=%2211%22 r=%227%22/%3E%3Cline x1=%2216.65%22 y1=%2216.65%22 x2=%2221%22 y2=%2221%22/%3E%3C/svg%3E');background-repeat:no-repeat;background-position:10px center;padding-left:34px">
      </div>
      <nav class="sidebar-nav" id="sidebar-nav">
        <div class="nav-section-label">Navigation</div>
        ${items}
      </nav>
      <div class="sidebar-footer">
        <div class="sidebar-avatar">${initials}</div>
        <div class="sidebar-user-info">
          <div class="sidebar-username" style="font-size: 13px;">${displayName}</div>
          <div class="sidebar-role">${user.role}</div>
        </div>
        <button class="btn-sidebar-logout" onclick="Auth.logout()" title="Logout">
          <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2"
               viewBox="0 0 24 24"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/>
          <polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
        </button>
      </div>`;
  },

  /** Filter sidebar nav items by search text */
  _filterNav(query) {
    const nav = document.getElementById('sidebar-nav');
    if (!nav) return;
    const items = nav.querySelectorAll('.nav-item');
    const q = query.toLowerCase().trim();
    items.forEach(item => {
      const label = item.textContent.toLowerCase();
      item.style.display = !q || label.includes(q) ? '' : 'none';
    });
  },
};
