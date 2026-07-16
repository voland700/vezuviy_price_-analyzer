

import Alpine from 'alpinejs';

import { Fancybox } from '@fancyapps/ui';
import '@fancyapps/ui/dist/fancybox/fancybox.css';

// Enable declarative usage: clicks on [data-fancybox] open the lightbox
Fancybox.bind('[data-fancybox]');

window.Alpine = Alpine;

// Apply theme before paint to avoid flash (dark by default)
window.applyTheme = function () {
    const stored = localStorage.getItem('theme');
    if (stored === 'light') {
        document.documentElement.classList.remove('dark');
    } else {
        document.documentElement.classList.add('dark');
    }
};
window.applyTheme();

Alpine.data('themeToggle', () => ({
    dark: document.documentElement.classList.contains('dark'),
    toggle() {
        this.dark = !this.dark;
        if (this.dark) {
            document.documentElement.classList.add('dark');
            localStorage.setItem('theme', 'dark');
        } else {
            document.documentElement.classList.remove('dark');
            localStorage.setItem('theme', 'light');
        }
    },
}));

Alpine.data('clipboard', () => ({
    copied: false,
    async copy(text) {
        try {
            await navigator.clipboard.writeText(text);
            this.copied = true;
            setTimeout(() => (this.copied = false), 1500);
        } catch (e) {}
    },
}));

Alpine.start();
