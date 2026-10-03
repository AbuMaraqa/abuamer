<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import { VueDraggable } from 'vue-draggable-plus';
import { route } from 'ziggy-js';
import PageHeader from '../../../components/admin/PageHeader.vue';
import BrandLogo from '../../../components/brands/BrandLogo.vue';
import AppButton from '../../../components/common/AppButton.vue';
import Icon from '../../../components/common/Icon.vue';
import ModalDialog from '../../../components/common/ModalDialog.vue';
import { countryName } from '../../../composables/useCountryName';
import { useTranslations } from '../../../composables/useTranslations';

const props = defineProps({
    brands: { type: Array, required: true },
});

const { t } = useTranslations();

const items = ref([...props.brands]);
watch(() => props.brands, (brands) => (items.value = [...brands]));

const sync = { preserveScroll: true, preserveState: true, only: ['brands'] };

function saveOrder() {
    router.patch(route('admin.brands.reorder'), { ids: items.value.map((brand) => brand.id) }, sync);
}

function toggleStatus(brand) {
    router.patch(route('admin.brands.status', brand.id), { status: !brand.status }, sync);
}

const deleting = ref(null);
const isDeleting = ref(false);

function destroy() {
    isDeleting.value = true;

    router.delete(route('admin.brands.destroy', deleting.value.id), {
        preserveScroll: true,
        onSuccess: () => (deleting.value = null),
        onFinish: () => (isDeleting.value = false),
    });
}
</script>

<template>
    <Head :title="t('Brands')" />

    <PageHeader :title="t('Brands')" :eyebrow="t('Catalog')" :description="t('The manufacturers of your products. Drag to change the order in which they appear on the website.')">
        <template #actions>
            <AppButton :href="route('admin.brands.create')">
                <Icon name="plus" :size="16" />
                {{ t('Add brand') }}
            </AppButton>
        </template>
    </PageHeader>

    <VueDraggable v-if="items.length > 0" v-model="items" handle=".brand-handle" :animation="180" tag="ol" class="flex flex-col gap-3" @end="saveOrder">
        <li v-for="brand in items" :key="brand.id" class="flex flex-wrap items-center gap-3 rounded-2xl border border-line bg-white p-3 sm:flex-nowrap sm:gap-4">
            <button type="button" class="brand-handle -ms-1 flex size-9 shrink-0 cursor-grab items-center justify-center text-sand-400 hover:text-ink" :aria-label="t('Drag to reorder')">
                <Icon name="grip-vertical" :size="18" />
            </button>

            <div class="flex h-14 w-24 shrink-0 items-center justify-center overflow-hidden rounded-lg border border-line bg-sand-50 px-2 text-sm sm:w-32" :class="{ 'opacity-40 grayscale': !brand.status }">
                <BrandLogo :brand="brand" image-class="max-h-10" />
            </div>

            <div class="min-w-0 flex-1">
                <Link :href="route('admin.brands.edit', brand.id)" class="block truncate font-medium text-ink hover:text-brass-700" dir="auto">{{ brand.name }}</Link>
                <p class="mt-1 truncate text-xs text-muted">
                    <template v-if="brand.country">{{ countryName(brand.country) }} · </template>
                    {{ t(':count products', { count: brand.products_count }) }}
                </p>
            </div>

            <div class="flex w-full shrink-0 items-center justify-between gap-2 border-t border-line pt-3 sm:w-auto sm:justify-end sm:border-0 sm:pt-0">
                <button
                    type="button"
                    class="shrink-0 rounded-full px-3 py-1.5 text-xs sm:py-1"
                    :class="brand.status ? 'bg-success/10 text-success' : 'bg-sand-200 text-muted'"
                    :title="brand.status ? t('Hide from website') : t('Show on website')"
                    @click="toggleStatus(brand)"
                >
                    {{ brand.status ? t('Visible') : t('Hidden') }}
                </button>

                <div class="flex items-center">
                    <a
                        v-if="brand.status"
                        :href="brand.url"
                        target="_blank"
                        rel="noopener"
                        class="flex size-10 items-center justify-center rounded-lg text-muted hover:bg-sand-100 hover:text-ink sm:size-9"
                        :aria-label="t('View on website')"
                    >
                        <Icon name="globe" :size="16" />
                    </a>
                    <Link :href="route('admin.products.index', { brand: brand.id })" class="flex size-10 items-center justify-center rounded-lg text-muted hover:bg-sand-100 hover:text-ink sm:size-9" :aria-label="t('Products of :name', { name: brand.name })">
                        <Icon name="box" :size="16" />
                    </Link>
                    <Link :href="route('admin.brands.edit', brand.id)" class="flex size-10 items-center justify-center rounded-lg text-muted hover:bg-sand-100 hover:text-ink sm:size-9" :aria-label="t('Edit :name', { name: brand.name })">
                        <Icon name="edit" :size="16" />
                    </Link>
                    <button type="button" class="flex size-10 items-center justify-center rounded-lg text-muted hover:bg-danger/5 hover:text-danger sm:size-9" :aria-label="t('Delete :name', { name: brand.name })" @click="deleting = brand">
                        <Icon name="trash" :size="16" />
                    </button>
                </div>
            </div>
        </li>
    </VueDraggable>

    <div v-else class="flex flex-col items-center gap-3 rounded-2xl border border-dashed border-line px-6 py-16 text-center">
        <Icon name="tag" :size="36" class="text-sand-300" />
        <p class="text-sm text-ink-soft">{{ t('No brands yet.') }}</p>
        <p class="max-w-md text-xs text-muted">{{ t('Add the manufacturers you carry, then choose a brand for each product. Brands get their own page and a filter in the catalog.') }}</p>
    </div>

    <ModalDialog :show="deleting !== null" :title="t('Delete “:name”', { name: deleting?.name ?? '' })" max-width="sm" @close="deleting = null">
        <p class="text-sm text-ink-soft">
            {{ deleting?.products_count > 0 ? t('Its :count products stay in the catalog without a brand.', { count: deleting.products_count }) : t('This action cannot be undone.') }}
        </p>
        <template #footer>
            <AppButton variant="secondary" @click="deleting = null">{{ t('Cancel') }}</AppButton>
            <AppButton variant="danger" :loading="isDeleting" @click="destroy">{{ t('Delete') }}</AppButton>
        </template>
    </ModalDialog>
</template>
