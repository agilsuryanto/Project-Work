// ===== AL-SYUKROSMART OPS - Main JS =====

document.addEventListener('DOMContentLoaded', function () {

    // ===== SIDEBAR TOGGLE (Mobile) =====
    const menuToggle = document.getElementById('menuToggle');
    const sidebar = document.querySelector('.sidebar');
    const overlay = document.getElementById('sidebarOverlay');

    if (menuToggle) {
        menuToggle.addEventListener('click', () => {
            sidebar?.classList.toggle('open');
            overlay?.classList.toggle('show');
        });
    }

    if (overlay) {
        overlay.addEventListener('click', () => {
            sidebar?.classList.remove('open');
            overlay.classList.remove('show');
        });
    }

    // ===== ACTIVE NAV ITEM =====
    const currentPage = window.location.search;
    document.querySelectorAll('.nav-item').forEach(item => {
        const href = item.getAttribute('href');
        if (href && currentPage.includes(href.split('?')[1]?.split('=')[1] || '')) {
            item.classList.add('active');
        }
    });

    // ===== MODAL HANDLING =====
    document.querySelectorAll('[data-modal]').forEach(btn => {
        btn.addEventListener('click', () => {
            const modal = document.getElementById(btn.dataset.modal);
            if (modal) modal.classList.add('open');
        });
    });

    document.querySelectorAll('.modal-close, [data-close-modal]').forEach(btn => {
        btn.addEventListener('click', () => {
            btn.closest('.modal-overlay')?.classList.remove('open');
        });
    });

    document.querySelectorAll('.modal-overlay').forEach(overlay => {
        overlay.addEventListener('click', (e) => {
            if (e.target === overlay) overlay.classList.remove('open');
        });
    });

    // ===== NOTIFICATION DROPDOWN =====
    const notifBtn = document.getElementById('notifBtn');
    const notifDropdown = document.getElementById('notifDropdown');

    if (notifBtn && notifDropdown) {
        notifBtn.addEventListener('click', (e) => {
            e.stopPropagation();
            notifDropdown.classList.toggle('show');
        });

        document.addEventListener('click', () => {
            notifDropdown.classList.remove('show');
        });
    }

    // ===== TAB NAVIGATION =====
    document.querySelectorAll('.tab-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            const group = btn.closest('.tab-group') || document.body;
            const target = btn.dataset.tab;

            group.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
            btn.classList.add('active');

            document.querySelectorAll('.tab-pane').forEach(pane => {
                pane.classList.toggle('active', pane.id === target);
            });
        });
    });

    // ===== PASSWORD TOGGLE =====
    document.querySelectorAll('.eye-toggle').forEach(btn => {
        btn.addEventListener('click', () => {
            const input = btn.previousElementSibling;
            if (input.type === 'password') {
                input.type = 'text';
                btn.textContent = '🙈';
            } else {
                input.type = 'password';
                btn.textContent = '👁️';
            }
        });
    });

    // ===== DEMO ACCOUNT FILL =====
    document.querySelectorAll('.demo-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            const username = btn.dataset.user;
            const password = btn.dataset.pass;
            const uField = document.getElementById('username');
            const pField = document.getElementById('password');
            if (uField) uField.value = username;
            if (pField) pField.value = password;
            uField?.focus();
        });
    });

    // ===== TABLE SEARCH =====
    const searchInput = document.getElementById('tableSearch');
    if (searchInput) {
        searchInput.addEventListener('input', () => {
            const query = searchInput.value.toLowerCase();
            document.querySelectorAll('tbody tr').forEach(row => {
                const text = row.textContent.toLowerCase();
                row.style.display = text.includes(query) ? '' : 'none';
            });
        });
    }

    // ===== SORTABLE TABLE HEADERS =====
    document.querySelectorAll('th[data-sort]').forEach(th => {
        th.style.cursor = 'pointer';
        th.addEventListener('click', () => {
            const table = th.closest('table');
            const tbody = table.querySelector('tbody');
            const rows = Array.from(tbody.querySelectorAll('tr'));
            const idx = Array.from(th.parentElement.children).indexOf(th);
            const asc = th.dataset.sortDir !== 'asc';
            th.dataset.sortDir = asc ? 'asc' : 'desc';

            rows.sort((a, b) => {
                const A = a.cells[idx]?.textContent.trim() || '';
                const B = b.cells[idx]?.textContent.trim() || '';
                return asc ? A.localeCompare(B) : B.localeCompare(A);
            });

            rows.forEach(r => tbody.appendChild(r));
        });
    });

    // ===== CONFIRM DELETE =====
    document.querySelectorAll('[data-confirm]').forEach(btn => {
        btn.addEventListener('click', (e) => {
            if (!confirm(btn.dataset.confirm || 'Apakah Anda yakin?')) {
                e.preventDefault();
            }
        });
    });

    // ===== FORM VALIDATION =====
    document.querySelectorAll('form[data-validate]').forEach(form => {
        form.addEventListener('submit', (e) => {
            let valid = true;
            form.querySelectorAll('[required]').forEach(field => {
                const errSpan = field.parentElement.querySelector('.field-error');
                if (!field.value.trim()) {
                    valid = false;
                    field.style.borderColor = '#e74c3c';
                    if (errSpan) errSpan.textContent = 'Field ini wajib diisi';
                    else {
                        const span = document.createElement('span');
                        span.className = 'field-error';
                        span.style.color = '#e74c3c';
                        span.style.fontSize = '12px';
                        span.textContent = 'Field ini wajib diisi';
                        field.parentElement.appendChild(span);
                    }
                } else {
                    field.style.borderColor = '';
                    if (errSpan) errSpan.textContent = '';
                }
            });
            if (!valid) e.preventDefault();
        });
    });

    // ===== ANIMATE NUMBERS =====
    document.querySelectorAll('.stat-number').forEach(el => {
        const target = parseInt(el.dataset.target) || 0;
        let current = 0;
        const increment = Math.ceil(target / 40);
        const timer = setInterval(() => {
            current += increment;
            if (current >= target) {
                current = target;
                clearInterval(timer);
            }
            el.textContent = current.toLocaleString('id-ID');
        }, 30);
    });

    // ===== AUTO-CLOSE ALERTS =====
    setTimeout(() => {
        document.querySelectorAll('.alert').forEach(alert => {
            alert.style.transition = 'opacity 0.5s, max-height 0.5s, padding 0.5s, margin 0.5s';
            alert.style.opacity = '0';
            alert.style.maxHeight = '0';
            alert.style.padding = '0';
            alert.style.marginBottom = '0';
        });
    }, 5000);

    // ===== DATE DISPLAY =====
    const dateEl = document.getElementById('currentDate');
    if (dateEl) {
        const now = new Date();
        const days = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
        const months = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
            'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
        dateEl.textContent = `${days[now.getDay()]}, ${now.getDate()} ${months[now.getMonth()]} ${now.getFullYear()}`;
    }

    // ===== MINI BAR CHART =====
    document.querySelectorAll('.mini-chart').forEach(canvas => {
        const data = JSON.parse(canvas.dataset.values || '[]');
        if (!data.length) return;
        const ctx = canvas.getContext('2d');
        const max = Math.max(...data);
        const w = canvas.width / data.length;
        const color = canvas.dataset.color || '#2980b9';

        data.forEach((val, i) => {
            const h = (val / max) * canvas.height * 0.85;
            ctx.fillStyle = color + (i === data.length - 1 ? 'ff' : '80');
            ctx.beginPath();
            ctx.roundRect(i * w + 2, canvas.height - h, w - 4, h, [3, 3, 0, 0]);
            ctx.fill();
        });
    });
});

// ===== GLOBAL FUNCTIONS =====
function openModal(id) {
    document.getElementById(id)?.classList.add('open');
}

function closeModal(id) {
    document.getElementById(id)?.classList.remove('open');
}

function showAlert(message, type = 'info') {
    const div = document.createElement('div');
    div.className = `alert alert-${type}`;
    div.innerHTML = `<span>${type === 'success' ? '✅' : type === 'error' ? '❌' : 'ℹ️'}</span> ${message}`;
    const content = document.querySelector('.page-content') || document.body;
    content.insertBefore(div, content.firstChild);
    setTimeout(() => {
        div.style.opacity = '0';
        div.style.maxHeight = '0';
        div.style.padding = '0';
    }, 4000);
}

function filterTable(query, tableId = null) {
    const rows = document.querySelectorAll(
        tableId ? `#${tableId} tbody tr` : 'tbody tr'
    );
    const q = query.toLowerCase();
    rows.forEach(row => {
        row.style.display = row.textContent.toLowerCase().includes(q) ? '' : 'none';
    });
}
