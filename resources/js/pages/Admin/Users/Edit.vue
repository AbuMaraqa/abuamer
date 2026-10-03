<script setup>
import { Head } from '@inertiajs/vue3';
import PageHeader from '../../../components/admin/PageHeader.vue';
import UserForm from '../../../components/admin/UserForm.vue';
import { useTranslations } from '../../../composables/useTranslations';

defineProps({
    user: { type: Object, required: true },
    roles: { type: Array, required: true },
    permissions: { type: Array, required: true },
});

const { t } = useTranslations();
</script>

<template>
    <Head :title="t('Edit user')" />

    <PageHeader :title="user.name" :eyebrow="t('Edit user')" />

    <UserForm :user="user" :roles="roles" :permissions="permissions" />
</template>
