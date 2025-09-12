// Build-free shared sidebar (no React/JSX required)
(function () {
  const PAGES = {
    dashboard: 'librarian-dashboard.html',
    books: 'librarian-books.html',
    members: 'members.html',
    borrow: 'borrow-return.html',
    overdue: 'overdue.html',
    lowstock: 'low-stock.html',
    history: 'history.html',
    emails: 'emails.html',
  };

  function link(href, icon, text, active) {
    const cls = 'sb-link' + (active ? ' active' : '');
    return `
      <a class="${cls}" href="${href}">
        <i class="bi ${icon}"></i>
        <span class="sb-text">${text}</span>
      </a>
    `;
  }

  function markup(current) {
    return `
      <nav id="sidebar" class="sb-sidebar d-flex flex-column">
        <a class="sb-brand" href="${PAGES.dashboard}">
          <img src="assets/img/LibraTrack-logo.png" alt="LibraTrack" class="sb-logo">
          <span class="sb-text fw-semibold">LibraTrack</span>
        </a>

        <div class="sb-menu flex-grow-1">
          <div class="sb-label">Main</div>

          ${link(PAGES.dashboard,'bi-speedometer2','Dashboard', current==='dashboard')}
          ${link(PAGES.books,    'bi-journal-text','Books',     current==='books')}
          ${link(PAGES.members,  'bi-people','Members',         current==='members')}
          ${link(PAGES.borrow,   'bi-arrow-left-right','Borrow/Return', current==='borrow')}

          <div class="sb-label mt-3">Monitoring</div>
          ${link(PAGES.overdue,  'bi-exclamation-triangle','Overdue',   current==='overdue')}
          ${link(PAGES.lowstock, 'bi-box-seam','Low Stock',              current==='lowstock')}
          ${link(PAGES.history,  'bi-clock-history','History',           current==='history')}
          ${link(PAGES.emails,   'bi-envelope-paper-heart','Emails',     current==='emails')}

          <div class="sb-label mt-3">Quick Actions</div>
          <button class="btn btn-gold w-100 mb-2">
            <i class="bi bi-plus-circle me-2"></i><span class="sb-text">Add Book</span>
          </button>
          <button class="btn btn-outline-light w-100">
            <i class="bi bi-person-plus me-2"></i><span class="sb-text">Register Member</span>
          </button>
        </div>

        <div class="mt-auto small text-secondary px-3 pb-3 sb-text">
          © <span id="year"></span> LibraTrack
        </div>
      </nav>
    `;
  }

  // Public: mount
  window.renderSidebar = function renderSidebar(current = '') {
    const root = document.getElementById('sidebar-root');
    if (!root) return;
    root.innerHTML = markup(current);

    // optional: derive active link from URL when `current` not provided
    if (!current) {
      const here = location.pathname.split('/').pop().toLowerCase();
      root.querySelectorAll('.sb-link').forEach(a => {
        const target = (a.getAttribute('href') || '').split('/').pop().toLowerCase();
        if (target === here) a.classList.add('active');
      });
    }
  };

  // Public: toggle (desktop expands, mobile slides)
  window.toggleSidebar = function toggleSidebar() {
    const isDesktop = window.innerWidth >= 992;
    const cls = isDesktop ? 'sb-expanded' : 'sb-toggled';
    document.body.classList.toggle(cls);
    if (isDesktop) document.body.classList.remove('sb-toggled');
  };

  // Close mobile menu if you resize up
  window.addEventListener('resize', () => {
    if (window.innerWidth >= 992) document.body.classList.remove('sb-toggled');
  });
})();
