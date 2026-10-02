<script setup>
import { Head, usePage } from '@inertiajs/vue3';
import PageHeader from '../../../components/admin/PageHeader.vue';
import CategoryForm from '../../../components/category/CategoryForm.vue';
import { useTranslations } from '../../../composables/useTranslations';

defineProps({
    category: { type: Object, required: true },
    categories: { type: Array, required: true },
});

const { t } = useTranslations();
const locale = usePage().props.locale.current;
</script>

<template>
    <Head :title="t('Edit category')" />

    <PageHeader :title="category[locale]?.name || t('Edit category')" :eyebrow="t('Edit category')" />

    <CategoryForm :category="category" :categories="categories" />
</template>
