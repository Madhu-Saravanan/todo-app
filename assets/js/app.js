/* ============================================================
   AntiGravity Todo App — Main JavaScript
   ============================================================ */

'use strict';

// ---------- DOM Ready ----------
document.addEventListener('DOMContentLoaded', () => {
  initTheme();
  initSidebar();
  initToasts();
  initStatusToggles();
  initDeleteButtons();
  initSearchDebounce();
});

// ============================================================
// THEME  (dark / light)
// ============================================================
function initTheme() {
  const html      = document.documentElement;
  const btn       = document.getElementById('themeToggle');
  const icon      = document.getElementById('themeIcon');
  const saved     = localStorage.getItem('ag_theme') || 'dark';

  applyTheme(saved);

  btn?.addEventListener('click', () => {
    const next = html.getAttribute('data-bs-theme') === 'dark' ? 'light' : 'dark';
    applyTheme(next);
    localStorage.setItem('ag_theme', next);
  });
}

function applyTheme(theme) {
  const html = document.documentElement;
  const icon = document.getElementById('themeIcon');
  html.setAttribute('data-bs-theme', theme);
  if (icon) {
    icon.className = theme === 'dark' ? 'bi bi-sun-fill' : 'bi bi-moon-stars-fill';
  }
}

// ============================================================
// SIDEBAR TOGGLE
// ============================================================
function initSidebar() {
  const btn      = document.getElementById('sidebarToggle');
  const sidebar  = document.getElementById('sidebar');
  const backdrop = document.getElementById('sidebarBackdrop');
  const body     = document.body;
  const isMobile = () => window.innerWidth <= 768;

  // Restore state on desktop
  if (!isMobile() && localStorage.getItem('ag_sidebar') === 'collapsed') {
    body.classList.add('sidebar-collapsed');
  }

  function openMobile() {
    sidebar?.classList.add('mobile-open');
    if (backdrop) { backdrop.style.display = 'block'; requestAnimationFrame(() => backdrop.classList.add('show')); }
  }
  function closeMobile() {
    sidebar?.classList.remove('mobile-open');
    if (backdrop) {
      backdrop.classList.remove('show');
      setTimeout(() => { backdrop.style.display = 'none'; }, 220);
    }
  }

  btn?.addEventListener('click', () => {
    if (isMobile()) {
      sidebar?.classList.contains('mobile-open') ? closeMobile() : openMobile();
    } else {
      body.classList.toggle('sidebar-collapsed');
      localStorage.setItem(
        'ag_sidebar',
        body.classList.contains('sidebar-collapsed') ? 'collapsed' : 'open'
      );
    }
  });

  // Close mobile sidebar on backdrop click
  backdrop?.addEventListener('click', closeMobile);

  // Also close on any sidebar link click (navigation)
  sidebar?.querySelectorAll('.sidebar-link').forEach(link => {
    link.addEventListener('click', () => { if (isMobile()) closeMobile(); });
  });
}

// ============================================================
// TOAST NOTIFICATIONS (programmatic)
// ============================================================
function initToasts() {
  // Auto-dismiss the server-side flash toast
  const flashToast = document.getElementById('flashToast');
  if (flashToast) {
    const bsToast = new bootstrap.Toast(flashToast, { delay: 5000 });
    bsToast.show();
  }
}

/**
 * Show a programmatic toast notification.
 * @param {string} message
 * @param {'success'|'danger'|'warning'|'info'} type
 */
function showToast(message, type = 'success') {
  const container = document.getElementById('toastContainer')
    || (() => {
        const el = document.createElement('div');
        el.id = 'toastContainer';
        el.className = 'toast-container position-fixed bottom-0 end-0 p-3';
        el.style.zIndex = '9999';
        document.body.appendChild(el);
        return el;
      })();

  const id = 'toast_' + Date.now();
  container.insertAdjacentHTML('beforeend', `
    <div id="${id}" class="toast align-items-center text-bg-${type} border-0"
         role="alert" aria-live="assertive" aria-atomic="true">
      <div class="d-flex">
        <div class="toast-body fw-semibold">${escapeHtml(message)}</div>
        <button type="button" class="btn-close btn-close-white me-2 m-auto"
                data-bs-dismiss="toast" aria-label="Close"></button>
      </div>
    </div>
  `);

  const el = document.getElementById(id);
  const t  = new bootstrap.Toast(el, { delay: 4500, autohide: true });
  t.show();
  el.addEventListener('hidden.bs.toast', () => el.remove());
}

// ============================================================
// AJAX STATUS TOGGLE
// ============================================================
function initStatusToggles() {
  document.querySelectorAll('[data-status-toggle]').forEach(el => {
    el.addEventListener('change', function () {
      const todoId = this.dataset.id;
      // Checkbox toggles between completed/pending; select uses its own value
      const status = (this.type === 'checkbox')
        ? (this.checked ? 'completed' : 'pending')
        : this.value;
      const csrf   = getMetaCsrf();

      fetch(APP_URL + '/ajax/todo_status.php', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/x-www-form-urlencoded',
          'X-CSRF-Token': csrf
        },
        body: new URLSearchParams({ id: todoId, status, csrf_token: csrf })
      })
      .then(r => r.json())
      .then(data => {
        showToast(data.message, data.success ? 'success' : 'danger');
        if (data.success) {
          // Update card appearance
          const card = document.querySelector(`.todo-card[data-id="${todoId}"]`);
          if (card) {
            card.className = card.className.replace(/status-\S+/, `status-${status}`);
            // Sync any other status toggles on the same card
            card.querySelectorAll('[data-status-toggle]').forEach(t => {
              if (t !== this) {
                if (t.type === 'checkbox') t.checked = (status === 'completed');
                else t.value = status;
              }
            });
          }
          // Reload if on a filtered page so counts stay accurate
          if (window.location.search.includes('status=')) {
            setTimeout(() => location.reload(), 800);
          }
        }
      })
      .catch(() => showToast('Network error. Please try again.', 'danger'));
    });
  });
}

// ============================================================
// AJAX SOFT DELETE
// ============================================================
function initDeleteButtons() {
  document.querySelectorAll('[data-delete-id]').forEach(btn => {
    btn.addEventListener('click', function (e) {
      e.preventDefault();
      if (!confirm('Are you sure you want to delete this todo? (Soft delete)')) return;

      const todoId   = this.dataset.deleteId;
      const redirect = this.dataset.redirect || null;
      const csrf     = getMetaCsrf();
      const card     = document.querySelector(`.todo-card[data-id="${todoId}"]`);

      fetch(APP_URL + '/ajax/todo_delete.php', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/x-www-form-urlencoded',
          'X-CSRF-Token': csrf
        },
        body: new URLSearchParams({ id: todoId, csrf_token: csrf })
      })
      .then(r => r.json())
      .then(data => {
        showToast(data.message, data.success ? 'success' : 'danger');
        if (data.success) {
          if (redirect) {
            setTimeout(() => { window.location.href = redirect; }, 600);
          } else if (card) {
            card.style.transition = 'opacity .4s, transform .4s';
            card.style.opacity    = '0';
            card.style.transform  = 'translateX(30px)';
            setTimeout(() => card.remove(), 420);
          }
        }
      })
      .catch(() => showToast('Network error.', 'danger'));
    });
  });
}

// ============================================================
// SEARCH DEBOUNCE (auto-submit filter form)
// ============================================================
function initSearchDebounce() {
  const searchInput = document.getElementById('searchInput');
  if (!searchInput) return;

  let timer;
  searchInput.addEventListener('input', () => {
    clearTimeout(timer);
    timer = setTimeout(() => {
      searchInput.closest('form')?.submit();
    }, 500);
  });
}

// ============================================================
// HELPERS
// ============================================================
function getMetaCsrf() {
  return document.querySelector('meta[name="csrf-token"]')?.content || '';
}

function escapeHtml(str) {
  const div = document.createElement('div');
  div.textContent = str;
  return div.innerHTML;
}

// APP_URL injected by PHP via a <script> tag in the layout
