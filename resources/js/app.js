import { createInertiaApp, usePage } from '@inertiajs/vue3';
import { ZiggyVue } from 'ziggy-js';
import { translationsPlugin } from './composables/useTranslations';
import AdminLayout from './layouts/AdminLayout.vue';
import AuthLayout from './layouts/AuthLayout.vue';
import MainLayout from './layouts/MainLayout.vue';

createInertiaApp({
    title: (title) => {
        const siteName = usePage().props.site?.name ?? usePage().props.appName;

        return title && title !== siteName ? `${title} | ${siteName}` : siteName;
    },
    layout: (name) => {
        if (name === 'Maintenance' || name === 'Error') {
            return null;
        }

        if (name.startsWith('Admin/')) {
            return AdminLayout;
        }

        if (name.startsWith('Auth/')) {
            return AuthLayout;
        }

        return MainLayout;
    },
    withApp(app) {
        app.use(ZiggyVue).use(translationsPlugin);
    },
    progress: {
        color: '#a07d52',
    },
});
