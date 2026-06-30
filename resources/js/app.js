import './bootstrap';

import * as Sentry from '@sentry/browser';

const sentryDsn = document.querySelector('meta[name="sentry-dsn"]')?.getAttribute('content');

if (sentryDsn) {
    Sentry.init({
        dsn: sentryDsn,
        environment: document.querySelector('meta[name="sentry-environment"]')?.getAttribute('content') || 'production',
        release: document.querySelector('meta[name="sentry-release"]')?.getAttribute('content') || undefined,
        integrations: [Sentry.browserTracingIntegration()],
        tracesSampleRate: 0.2,
    });
}

import Alpine from 'alpinejs';

window.Alpine = Alpine;

Alpine.start();

const THEME_STORAGE_KEY = 'mondok-qu.theme';

function getStoredTheme() {
    try {
        return localStorage.getItem(THEME_STORAGE_KEY);
    } catch (e) {
        return null;
    }
}

function setStoredTheme(theme) {
    try {
        localStorage.setItem(THEME_STORAGE_KEY, theme);
    } catch (e) {}
}

function getThemePreference() {
    return getStoredTheme()
        || (window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light');
}

function applyTheme(theme) {
    document.documentElement.setAttribute('data-bs-theme', theme);
    setStoredTheme(theme);

    var toggle = document.getElementById('theme-toggle');
    if (toggle) {
        var icon = toggle.querySelector('i');
        if (icon) {
            icon.className = theme === 'dark' ? 'ti ti-sun' : 'ti ti-moon';
        }
    }
}

function toggleTheme() {
    var current = document.documentElement.getAttribute('data-bs-theme') || 'light';
    applyTheme(current === 'dark' ? 'light' : 'dark');
}

document.addEventListener('DOMContentLoaded', () => {
    applyTheme(getThemePreference());

    var toggleBtn = document.getElementById('theme-toggle');
    if (toggleBtn) {
        toggleBtn.addEventListener('click', toggleTheme);
    }

    window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', function (e) {
        if (!getStoredTheme()) {
            applyTheme(e.matches ? 'dark' : 'light');
        }
    });

    document.querySelectorAll('[data-error-fallback]').forEach((img) => {
        img.addEventListener('error', () => {
            img.classList.add('d-none');
            const fallback = img.nextElementSibling;
            if (fallback) {
                fallback.classList.remove('d-none');
            }
        });
    });


    const body = document.body;
    const toggle = document.getElementById('sidebar-toggle');
    const mobileToggle = document.getElementById('mobile-sidebar-toggle');
    const mobileClose = document.getElementById('mobile-sidebar-close');
    const mobileBackdrop = document.getElementById('mobile-sidebar-backdrop');
    const sidebarMenu = document.getElementById('sidebar-menu');
    const mobileSidebarLinks = document.querySelectorAll('.sidebar-link, .sidebar-sublink, .mobile-sidebar-user-card, .mobile-sidebar-action');
    const storageKey = 'mondok-qu.sidebar-collapsed';

    const applySidebarState = (collapsed) => {
        body.classList.toggle('sidebar-collapsed', collapsed);

        if (toggle) {
            const icon = toggle.querySelector('i');

            if (icon) {
                icon.className = collapsed
                    ? 'ti ti-layout-sidebar-left-expand'
                    : 'ti ti-layout-sidebar-left-collapse';
            }
        }
    };

    const setMobileSidebarOpen = (open) => {
        body.classList.toggle('mobile-sidebar-open', open);

        if (sidebarMenu) {
            sidebarMenu.classList.toggle('is-open', open);
        }

        if (mobileToggle) {
            mobileToggle.setAttribute('aria-expanded', String(open));
        }

        if (mobileBackdrop) {
            mobileBackdrop.hidden = !open;
        }
    };

    applySidebarState(localStorage.getItem(storageKey) === 'true');
    setMobileSidebarOpen(false);

    toggle?.addEventListener('click', () => {
        const collapsed = !body.classList.contains('sidebar-collapsed');

        applySidebarState(collapsed);
        localStorage.setItem(storageKey, String(collapsed));
    });

    mobileToggle?.addEventListener('click', () => {
        const isOpen = !body.classList.contains('mobile-sidebar-open');
        setMobileSidebarOpen(isOpen);
    });

    mobileClose?.addEventListener('click', () => {
        setMobileSidebarOpen(false);
    });

    mobileBackdrop?.addEventListener('click', () => {
        setMobileSidebarOpen(false);
    });

    mobileSidebarLinks.forEach((link) => {
        link.addEventListener('click', (e) => {
            if (window.innerWidth >= 992) return;
            if (link.closest('summary') || link.closest('.sidebar-submenu')) return;
            setMobileSidebarOpen(false);
        });
    });

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape') {
            setMobileSidebarOpen(false);
        }
    });

    document.querySelectorAll('[data-attendance-record-tools]').forEach((toolbar) => {
        const form = toolbar.closest('form');
        const searchInput = toolbar.querySelector('[data-attendance-search-input]');
        const roomFilter = toolbar.querySelector('[data-attendance-room-filter]');
        const visibleCount = toolbar.querySelector('[data-attendance-visible-count]');
        const resetFilter = toolbar.querySelector('[data-attendance-reset-filter]');
        const rows = Array.from(form?.querySelectorAll('[data-attendance-row]') ?? []);
        const emptyRow = form?.querySelector('[data-attendance-empty-row]');
        const normalize = (value) => (value ?? '').toString().trim().toLowerCase();

        const applyFilters = () => {
            const keyword = normalize(searchInput?.value);
            const room = normalize(roomFilter?.value);
            let visibleRows = 0;

            rows.forEach((row) => {
                const searchIndex = normalize(row.dataset.attendanceSearch);
                const roomIndex = normalize(row.dataset.attendanceRoom);
                const isVisible = (!keyword || searchIndex.includes(keyword)) && (!room || roomIndex === room);

                row.hidden = !isVisible;
                visibleRows += isVisible ? 1 : 0;
            });

            if (emptyRow) {
                emptyRow.hidden = visibleRows > 0 || rows.length === 0;
            }

            if (visibleCount) {
                visibleCount.textContent = `${visibleRows.toLocaleString('id-ID')} tampil`;
            }
        };

        searchInput?.addEventListener('input', applyFilters);
        roomFilter?.addEventListener('change', applyFilters);
        resetFilter?.addEventListener('click', () => {
            if (searchInput) {
                searchInput.value = '';
            }

            if (roomFilter) {
                roomFilter.value = '';
            }

            applyFilters();
            searchInput?.focus();
        });

        toolbar.querySelectorAll('[data-attendance-bulk-status]').forEach((button) => {
            button.addEventListener('click', () => {
                if (button.disabled) {
                    return;
                }

                const status = button.dataset.attendanceBulkStatus;

                rows
                    .filter((row) => !row.hidden)
                    .forEach((row) => {
                        const select = row.querySelector('[data-attendance-status-select]');

                        if (!select || select.disabled) {
                            return;
                        }

                        select.value = status;
                        select.dispatchEvent(new Event('change', { bubbles: true }));
                    });
            });
        });

        applyFilters();
    });
});
