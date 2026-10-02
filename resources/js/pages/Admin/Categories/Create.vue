<script setup>
import { Head } from '@inertiajs/vue3';
import { computed } from 'vue';
import PageHeader from '../../../components/admin/PageHeader.vue';
import CategoryForm from '../../../components/category/CategoryForm.vue';
import { findNode } from '../../../composables/useCategoryTree';
import { useTranslations } from '../../../composables/useTranslations';

const props = defineProps({
    categories: { type: Array, required: true },
    parentId: { type: Number, default: null },
});

const { t } = useTranslations();

const parent = computed(() => (props.parentId === null ? null : findNode(props.categories, props.parentId)));
</script>

<template>
    <Head :title="t('Add category')" />

    <PageHeader
        :title="parent ? t('Add a subcategory') : t('Add category')"
        :eyebrow="t('Categories')"
        :description="parent ? t('The new category will be created inside “:name”.', { name: parent.name }) : null"
    />

    <CategoryForm :categories="categories" :parent-id="parentId" />
</template>
