<script setup>
import { Head } from '@inertiajs/vue3';
import PageHeader from '../../../components/admin/PageHeader.vue';
import RoleForm from '../../../components/admin/RoleForm.vue';
import { useTranslations } from '../../../composables/useTranslations';

defineProps({
    permissions: { type: Array, required: true },
});

const { t } = useTranslations();
</script>

<template>
    <Head :title="t('Add role')" />

    <PageHeader :title="t('Add role')" :eyebrow="t('Roles & permissions')" />

    <RoleForm :permissions="permissions" />
</template>
