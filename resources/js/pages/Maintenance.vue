<script setup>
import { Head } from '@inertiajs/vue3';
import { useTranslations } from '../composables/useTranslations';

const { t } = useTranslations();
</script>

<template>
    <Head :title="t('We will be back soon')" />

    <main class="flex min-h-svh flex-col items-center justify-center gap-6 bg-ink px-6 text-center text-white">
        <p class="font-display text-3xl">{{ $page.props.site.name }}</p>
        <h1 class="max-w-xl font-display text-5xl leading-tight sm:text-6xl">{{ t('We will be back soon') }}</h1>
        <p class="max-w-md text-base leading-relaxed text-sand-300">
            {{ t('We are refreshing our website. Please visit us again shortly.') }}
        </p>
        <div class="mt-4 flex flex-col items-center gap-2 text-sm text-sand-300">
            <a v-if="$page.props.site.contact.phone" :href="`tel:${$page.props.site.contact.phone}`" dir="ltr" class="hover:text-white">{{ $page.props.site.contact.phone }}</a>
            <a v-if="$page.props.site.contact.email" :href="`mailto:${$page.props.site.contact.email}`" class="hover:text-white">{{ $page.props.site.contact.email }}</a>
        </div>
    </main>
</template>
