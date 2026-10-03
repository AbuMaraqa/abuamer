<script setup>
import { Head } from '@inertiajs/vue3';
import BrandForm from '../../../components/admin/BrandForm.vue';
import PageHeader from '../../../components/admin/PageHeader.vue';
import { useTranslations } from '../../../composables/useTranslations';

defineProps({
    brand: { type: Object, required: true },
});

const { t } = useTranslations();
</script>

<template>
    <Head :title="t('Edit brand')" />

    <PageHeader :title="brand.name" :eyebrow="t('Edit brand')" />

    <BrandForm :brand="brand" />
</template>
