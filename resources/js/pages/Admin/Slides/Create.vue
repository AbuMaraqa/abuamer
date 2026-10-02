<script setup>
import { Head } from '@inertiajs/vue3';
import PageHeader from '../../../components/admin/PageHeader.vue';
import SlideForm from '../../../components/admin/SlideForm.vue';
import { useTranslations } from '../../../composables/useTranslations';

defineProps({
    categories: { type: Array, required: true },
});

const { t } = useTranslations();
</script>

<template>
    <Head :title="t('Add slide')" />

    <PageHeader :title="t('Add slide')" :eyebrow="t('Slider')" />

    <SlideForm :categories="categories" />
</template>
