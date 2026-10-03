<script setup>
import { Head } from '@inertiajs/vue3';
import PageHeader from '../../../components/admin/PageHeader.vue';
import UserForm from '../../../components/admin/UserForm.vue';
import { useTranslations } from '../../../composables/useTranslations';

defineProps({
    roles: { type: Array, required: true },
    permissions: { type: Array, required: true },
});

const { t } = useTranslations();
</script>

<template>
    <Head :title="t('Add user')" />

    <PageHeader :title="t('Add user')" :eyebrow="t('Users')" />

    <UserForm :roles="roles" :permissions="permissions" />
</template>
