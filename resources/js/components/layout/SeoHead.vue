<script setup>
import { Head, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

/**
 * Keeps the metadata rendered by resources/views/partials/seo.blade.php in sync while
 * navigating between pages. Each head-key matches a data-inertia attribute there.
 */
const page = usePage();
const seo = computed(() => page.props.seo);
</script>

<template>
    <Head v-if="seo">
        <title>{{ seo.title }}</title>
        <meta head-key="description" name="description" :content="seo.description ?? ''" />
        <meta head-key="keywords" name="keywords" :content="seo.keywords ?? ''" />
        <meta head-key="robots" name="robots" :content="seo.robots" />
        <link head-key="canonical" rel="canonical" :href="seo.canonical" />
        <link v-for="(url, locale) in seo.alternates" :key="locale" :head-key="`alternate-${locale}`" rel="alternate" :hreflang="locale" :href="url" />
        <link head-key="alternate-x-default" rel="alternate" hreflang="x-default" :href="seo.defaultUrl" />
        <meta head-key="og:type" property="og:type" :content="seo.type" />
        <meta head-key="og:site_name" property="og:site_name" :content="seo.siteName" />
        <meta head-key="og:title" property="og:title" :content="seo.title" />
        <meta head-key="og:description" property="og:description" :content="seo.description ?? ''" />
        <meta head-key="og:url" property="og:url" :content="seo.canonical" />
        <meta head-key="og:locale" property="og:locale" :content="seo.locale" />
        <meta v-if="seo.image" head-key="og:image" property="og:image" :content="seo.image" />
        <meta head-key="twitter:card" name="twitter:card" :content="seo.image ? 'summary_large_image' : 'summary'" />
    </Head>
</template>
