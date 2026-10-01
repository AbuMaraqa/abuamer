<script setup>
import { Head } from '@inertiajs/vue3';
</script>

<template>
    <Head :title="$page.props.appName" />

    <section class="mx-auto max-w-7xl px-4 py-24 sm:px-6 lg:px-8">
        <p class="eyebrow">{{ $t('Tiles & Ceramics') }}</p>
        <h1 class="mt-4 font-display text-5xl text-ink">{{ $page.props.appName }}</h1>
    </section>
</template>
