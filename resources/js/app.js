import '../css/app.css';
import './bootstrap';

import { createInertiaApp, usePage } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { createApp, h } from 'vue';
import { ZiggyVue } from '../../vendor/tightenco/ziggy';

const appName = import.meta.env.VITE_APP_NAME || 'Laravel';

// Console Signature / Digital Fingerprint
console.log(
    '%c LUXSTAY %c © 2026 Developed by Suwarna %c',
    'background: #0A1128; color: #B8860B; padding: 5px 10px; border-radius: 5px 0 0 5px; font-weight: bold;',
    'background: #B8860B; color: #0A1128; padding: 5px 10px; border-radius: 0 5px 5px 0; font-weight: bold;',
    'background: transparent'
);

createInertiaApp({
    title: (title) => `${title} - ${appName}`,
    resolve: (name) =>
        resolvePageComponent(
            `./Pages/${name}.vue`,
            import.meta.glob('./Pages/**/*.vue'),
        ),
    setup({ el, App, props, plugin }) {
        const vueApp = createApp({ render: () => h(App, props) })
            .use(plugin)
            .use(ZiggyVue);

        // Global translation helper
        vueApp.config.globalProperties.$t = (key, replace = {}) => {
            const page = props.initialPage ? props.initialPage : {};
            const translations = (page.props && page.props.translations) || {};
            let translation = translations[key] || key;

            if (Object.keys(replace).length > 0) {
                Object.keys(replace).forEach(replaceKey => {
                    translation = translation.replace(':' + replaceKey, replace[replaceKey]);
                });
            }

            return translation;
        };

        // Global price formatter (Fixed to Rupiah/IDR)
        const formatIDR = (value) => {
            if (!value && value !== 0) return `Rp 0`;
            return new Intl.NumberFormat('id-ID', {
                style: 'currency',
                currency: 'IDR',
                minimumFractionDigits: 0,
                maximumFractionDigits: 0
            }).format(value);
        };

        vueApp.config.globalProperties.$formatPrice = formatIDR;
        vueApp.config.globalProperties.$formatMoney = formatIDR;

        return vueApp.mount(el);
    },
    progress: {
        color: '#4B5563',
    },
});
