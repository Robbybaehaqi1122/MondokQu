import './bootstrap';

import Alpine from 'alpinejs';

window.Alpine = Alpine;

Alpine.start();

document.addEventListener('DOMContentLoaded', () => {
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
        link.addEventListener('click', () => {
            if (window.innerWidth < 992) {
                setMobileSidebarOpen(false);
            }
        });
    });

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape') {
            setMobileSidebarOpen(false);
        }
    });
});
