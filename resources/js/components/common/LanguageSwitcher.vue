<script setup>
import { usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

const page = usePage();

const otherLocales = computed(() =>
    page.props.locale.supported.filter((locale) => locale.code !== page.props.locale.current),
);
</script>

<template>
    <!-- Plain anchors: switching language reloads the document so lang/dir and fonts update. -->
    <a
        v-for="locale in otherLocales"
        :key="locale.code"
        :href="page.props.localeUrls[locale.code]"
        :hreflang="locale.code"
        :lang="locale.code"
        class="inline-flex items-center gap-1.5 text-sm transition-colors hover:text-brass-600"
    >
        <slot :locale="locale">{{ locale.native }}</slot>
    </a>
</template>
