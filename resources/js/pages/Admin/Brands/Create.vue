<script setup>
import { Head } from '@inertiajs/vue3';
import BrandForm from '../../../components/admin/BrandForm.vue';
import PageHeader from '../../../components/admin/PageHeader.vue';
import { useTranslations } from '../../../composables/useTranslations';

const { t } = useTranslations();
</script>

<template>
    <Head :title="t('Add brand')" />

    <PageHeader :title="t('Add brand')" :eyebrow="t('Brands')" />

    <BrandForm />
</template>
