<script setup>
import { usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import { useTranslations } from '../../composables/useTranslations';

const { t } = useTranslations();
const page = usePage();

const otherLocales = computed(() =>
    page.props.locale.supported.filter((locale) => locale.code !== page.props.locale.current),
);
</script>

<template>
    <!-- Plain anchors: switching language reloads the document so lang/dir and fonts update. -->
    <nav class="inline-flex items-center gap-3" :aria-label="t('Language')">
        <template v-for="(locale, index) in otherLocales" :key="locale.code">
            <span v-if="index > 0" class="h-3.5 w-px bg-current opacity-25" aria-hidden="true" />
            <a
                :href="page.props.localeUrls[locale.code]"
                :hreflang="locale.code"
                :lang="locale.code"
                class="inline-flex items-center gap-1.5 transition-colors hover:text-brass-600"
            >
                <slot :locale="locale">{{ locale.native }}</slot>
            </a>
        </template>
    </nav>
</template>
