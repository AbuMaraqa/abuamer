<script setup>
import { Head } from '@inertiajs/vue3';
import { useTranslations } from '../../composables/useTranslations';

const { t } = useTranslations();
</script>

<template>
    <Head :title="t('Dashboard')" />

    <div class="flex flex-col gap-2">
        <p class="eyebrow">{{ t('Control panel') }}</p>
        <h1 class="font-display text-3xl text-ink">{{ t('Welcome, :name', { name: $page.props.auth.user.name }) }}</h1>
    </div>
</template>
