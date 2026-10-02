<script setup>
import { Head, usePage } from '@inertiajs/vue3';
import { ref } from 'vue';
import PageHeader from '../../../components/admin/PageHeader.vue';
import ProductDeleteDialog from '../../../components/products/ProductDeleteDialog.vue';
import ProductForm from '../../../components/products/ProductForm.vue';
import { useTranslations } from '../../../composables/useTranslations';

defineProps({
    product: { type: Object, required: true },
    categories: { type: Array, required: true },
});

const { t } = useTranslations();
const locale = usePage().props.locale.current;
const deleting = ref(null);
</script>

<template>
    <Head :title="t('Edit product')" />

    <PageHeader :title="product[locale]?.name || t('Edit product')" :eyebrow="t('Edit product')" />

    <ProductForm :product="product" :categories="categories" @delete="deleting = { id: product.id, name: product[locale]?.name }" />

    <ProductDeleteDialog :product="deleting" @close="deleting = null" />
</template>
