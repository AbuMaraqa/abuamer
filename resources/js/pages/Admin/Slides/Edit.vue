<script setup>
import { Head, usePage } from '@inertiajs/vue3';
import PageHeader from '../../../components/admin/PageHeader.vue';
import SlideForm from '../../../components/admin/SlideForm.vue';
import { useTranslations } from '../../../composables/useTranslations';

defineProps({
    slide: { type: Object, required: true },
    categories: { type: Array, required: true },
});

const { t } = useTranslations();
const locale = usePage().props.locale.current;
</script>

<template>
    <Head :title="t('Edit slide')" />

    <PageHeader :title="slide[locale]?.title || t('Edit slide')" :eyebrow="t('Slider')" />

    <SlideForm :slide="slide" :categories="categories" />
</template>
