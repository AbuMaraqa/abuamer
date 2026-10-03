<script setup>
import { Head } from '@inertiajs/vue3';
import PageHeader from '../../../components/admin/PageHeader.vue';
import RoleForm from '../../../components/admin/RoleForm.vue';
import { useTranslations } from '../../../composables/useTranslations';

defineProps({
    role: { type: Object, required: true },
    permissions: { type: Array, required: true },
});

const { t } = useTranslations();
</script>

<template>
    <Head :title="t('Edit role')" />

    <PageHeader :title="role.label" :eyebrow="t('Edit role')" />

    <RoleForm :role="role" :permissions="permissions" />
</template>
