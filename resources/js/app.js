import { createInertiaApp, usePage } from '@inertiajs/vue3';
import { ZiggyVue } from 'ziggy-js';
import { translationsPlugin } from './composables/useTranslations';
import AdminLayout from './layouts/AdminLayout.vue';
import AuthLayout from './layouts/AuthLayout.vue';
import MainLayout from './layouts/MainLayout.vue';

createInertiaApp({
    title: (title) => {
        const appName = usePage().props.appName;

        return title && title !== appName ? `${title} | ${appName}` : appName;
    },
    layout: (name) => {
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
