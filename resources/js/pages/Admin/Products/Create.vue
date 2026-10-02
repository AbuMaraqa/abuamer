<script setup>
import { Head } from '@inertiajs/vue3';
import PageHeader from '../../../components/admin/PageHeader.vue';
import ProductForm from '../../../components/products/ProductForm.vue';
import { useTranslations } from '../../../composables/useTranslations';

defineProps({
    categories: { type: Array, required: true },
    categoryId: { type: Number, default: null },
});

const { t } = useTranslations();
</script>

<template>
    <Head :title="t('Add product')" />

    <PageHeader :title="t('Add product')" :eyebrow="t('Products')" />

    <ProductForm :categories="categories" :category-id="categoryId" />
</template>
