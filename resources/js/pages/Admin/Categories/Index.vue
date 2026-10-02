<script setup>
import { Head } from '@inertiajs/vue3';
import { route } from 'ziggy-js';
import PageHeader from '../../../components/admin/PageHeader.vue';
import CategoryTree from '../../../components/category/CategoryTree.vue';
import AppButton from '../../../components/common/AppButton.vue';
import Icon from '../../../components/common/Icon.vue';
import { useTranslations } from '../../../composables/useTranslations';

defineProps({
    tree: { type: Array, required: true },
    highlightId: { type: Number, default: null },
});

const { t } = useTranslations();
</script>

<template>
    <Head :title="t('Categories')" />

    <PageHeader :title="t('Categories')" :eyebrow="t('Catalog')" :description="t('Organise products into a tree of categories of any depth.')">
        <template #actions>
            <AppButton v-if="$page.props.auth.user.can['categories.create']" :href="route('admin.categories.create')">
                <Icon name="plus" :size="16" />
                {{ t('Add category') }}
            </AppButton>
        </template>
    </PageHeader>

    <CategoryTree :tree="tree" :highlight-id="highlightId" />
</template>
