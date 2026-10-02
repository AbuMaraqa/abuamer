<script setup>
import { Head, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import { useTranslations } from '../composables/useTranslations';

/**
 * Shown in production for 403, 404, 419, 429, 500 and 503 responses.
 */
const props = defineProps({
    status: { type: Number, required: true },
    homeUrl: { type: String, default: '/' },
});

const { t } = useTranslations();
const page = usePage();

const messages = {
    403: ['Access denied', 'You do not have permission to view this page.'],
    404: ['Page not found', 'The page you are looking for may have been moved or no longer exists.'],
    419: ['Page expired', 'Your session has expired. Please refresh the page and try again.'],
    429: ['Too many requests', 'Please wait a moment before trying again.'],
    500: ['Something went wrong', 'An unexpected error occurred. Please try again later.'],
    503: ['We will be back soon', 'We are refreshing our website. Please visit us again shortly.'],
};

const content = computed(() => messages[props.status] ?? messages[500]);
</script>

<template>
    <Head :title="t(content[0])" />

    <main class="flex min-h-svh flex-col items-center justify-center gap-6 bg-sand-50 px-6 text-center">
        <p class="font-display text-8xl text-brass-500 tabular-nums lining-nums" dir="ltr">{{ status }}</p>
        <h1 class="font-display text-4xl text-ink sm:text-5xl">{{ t(content[0]) }}</h1>
        <p class="max-w-md text-base leading-relaxed text-muted">{{ t(content[1]) }}</p>
        <a :href="homeUrl" class="mt-2 rounded-lg bg-ink px-6 py-3 text-sm font-medium text-white transition-colors hover:bg-ink-soft">
            {{ t('Back to the home page') }}
        </a>
        <p v-if="page.props.site?.name" class="mt-8 font-display text-xl text-ink-soft">{{ page.props.site.name }}</p>
    </main>
</template>
