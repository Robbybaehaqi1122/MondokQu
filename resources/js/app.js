import './bootstrap';

import Alpine from 'alpinejs';

window.Alpine = Alpine;

Alpine.start();

document.addEventListener('DOMContentLoaded', () => {
    const body = document.body;
    const toggle = document.getElementById('sidebar-toggle');
    const mobileToggle = document.getElementById('mobile-sidebar-toggle');
    const sidebarMenu = document.getElementById('sidebar-menu');
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

    applySidebarState(localStorage.getItem(storageKey) === 'true');

    toggle?.addEventListener('click', () => {
        const collapsed = !body.classList.contains('sidebar-collapsed');

        applySidebarState(collapsed);
        localStorage.setItem(storageKey, String(collapsed));
    });

    mobileToggle?.addEventListener('click', () => {
        const isOpen = sidebarMenu?.classList.toggle('is-open');

        if (sidebarMenu) {
            mobileToggle.setAttribute('aria-expanded', String(Boolean(isOpen)));
        }
    });
});
