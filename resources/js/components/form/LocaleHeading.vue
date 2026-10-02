<script setup>
import { usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import { useTranslations } from '../../composables/useTranslations';

/**
 * The language name above a group of translated fields. Optional languages say which
 * language visitors see while they are left empty.
 */
const props = defineProps({
    locale: { type: Object, required: true },
});

const { t } = useTranslations();
const page = usePage();

const fallbackName = computed(() => t(page.props.locale.supported.find(({ code }) => code === props.locale.fallback)?.name ?? ''));
</script>

<template>
    <div class="flex flex-col gap-0.5">
        <p class="text-xs font-semibold text-brass-600">
            {{ locale.native }}
            <span v-if="!locale.required" class="font-normal text-muted">· {{ t('Optional') }}</span>
        </p>
        <p v-if="!locale.required" class="text-xs text-muted">{{ t('Leave empty to show the :language text.', { language: fallbackName }) }}</p>
    </div>
</template>
