import Alpine from 'alpinejs';

window.Alpine = Alpine;

Alpine.start();

document.addEventListener('DOMContentLoaded', () => {
    // Mobile drawer navigation
    const drawer = document.querySelector('[data-drawer]');
    const drawerOpen = document.querySelector('[data-drawer-open]');
    const drawerClose = document.querySelector('[data-drawer-close]');
    const drawerBackdrop = document.querySelector('[data-drawer-backdrop]');
    const focusable = drawer ? [...drawer.querySelectorAll('a, button')] : [];

    const openDrawer = () => {
        if (!drawer) return;
        drawer.classList.remove('hidden');
        drawer.classList.add('flex');
        document.body.style.overflow = 'hidden';
        focusable[0]?.focus();
    };

    const closeDrawer = () => {
        if (!drawer) return;
        drawer.classList.add('hidden');
        drawer.classList.remove('flex');
        document.body.style.overflow = '';
        drawerOpen?.focus();
    };

    drawerOpen?.addEventListener('click', openDrawer);
    drawerClose?.addEventListener('click', closeDrawer);
    drawerBackdrop?.addEventListener('click', closeDrawer);

    // Confirmation modals: backdrop or cancel closes
    document.querySelectorAll('[data-modal]').forEach((modal) => {
        modal.addEventListener('click', (e) => {
            if (e.target.closest('[data-modal-backdrop]') || e.target.closest('[data-modal-cancel]')) {
                modal.classList.add('hidden');
            }
        });
    });

    // Escape closes drawer and any open modal
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') {
            if (drawer && !drawer.classList.contains('hidden')) closeDrawer();
            document.querySelectorAll('[data-modal]:not(.hidden)').forEach((modal) => modal.classList.add('hidden'));
        }
    });

    // Auto-dismiss toasts
    document.querySelectorAll('[data-toast]').forEach((toast) => {
        toast.classList.remove('hidden');
        setTimeout(() => {
            toast.classList.add('hidden');
        }, 4000);
    });
});