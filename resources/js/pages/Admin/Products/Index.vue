<script setup>
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { computed, onBeforeUnmount, ref, watch } from 'vue';
import { route } from 'ziggy-js';
import PageHeader from '../../../components/admin/PageHeader.vue';
import CategoryTreeSelect from '../../../components/category/CategoryTreeSelect.vue';
import AppButton from '../../../components/common/AppButton.vue';
import Icon from '../../../components/common/Icon.vue';
import ImagePlaceholder from '../../../components/common/ImagePlaceholder.vue';
import ModalDialog from '../../../components/common/ModalDialog.vue';
import PaginationLinks from '../../../components/common/PaginationLinks.vue';
import FormField from '../../../components/form/FormField.vue';
import SelectInput from '../../../components/form/SelectInput.vue';
import ProductDeleteDialog from '../../../components/products/ProductDeleteDialog.vue';
import { findPath } from '../../../composables/useCategoryTree';
import { useTranslations } from '../../../composables/useTranslations';

const props = defineProps({
    products: { type: Object, required: true },
    categories: { type: Array, required: true },
    filters: { type: Object, required: true },
});

const { t } = useTranslations();
const can = usePage().props.auth.user.can;

/* Filters (server-side) ---------------------------------------------------- */

const filters = ref({ ...props.filters });
let searchTimer = null;

function applyFilters() {
    router.get(
        route('admin.products.index'),
        {
            q: filters.value.q || undefined,
            category: filters.value.category ?? undefined,
            descendants: filters.value.category && !filters.value.descendants ? 0 : undefined,
            status: filters.value.status ?? undefined,
            featured: filters.value.featured ?? undefined,
        },
        { preserveState: true, preserveScroll: true, replace: true, only: ['products', 'filters'] },
    );
}

watch(() => filters.value.q, () => {
    clearTimeout(searchTimer);
    searchTimer = setTimeout(applyFilters, 350);
});

watch(() => [filters.value.category, filters.value.descendants, filters.value.status, filters.value.featured], applyFilters);

onBeforeUnmount(() => clearTimeout(searchTimer));

const hasFilters = computed(() => !!(filters.value.q || filters.value.category || filters.value.status || filters.value.featured));

function clearFilters() {
    filters.value = { q: '', category: null, descendants: true, status: null, featured: null };
}

const statusOptions = [
    { value: null, label: t('Any status') },
    { value: 'active', label: t('Visible') },
    { value: 'inactive', label: t('Hidden') },
];

const featuredOptions = [
    { value: null, label: t('Featured or not') },
    { value: 'yes', label: t('Featured only') },
    { value: 'no', label: t('Not featured') },
];

function categoryPath(categoryId) {
    return findPath(props.categories, categoryId).map((node) => node.name).join(' / ');
}

/* Selection and bulk move -------------------------------------------------- */

const selectedIds = ref([]);
const allSelected = computed(() => props.products.data.length > 0 && props.products.data.every((product) => selectedIds.value.includes(product.id)));

watch(() => props.products.data, () => {
    selectedIds.value = [];
});

function toggleAll() {
    selectedIds.value = allSelected.value ? [] : props.products.data.map((product) => product.id);
}

const isMoveOpen = ref(false);
const moveCategoryId = ref(null);
const isMoving = ref(false);

function moveSelected() {
    isMoving.value = true;

    router.patch(
        route('admin.products.category'),
        { ids: selectedIds.value, category_id: moveCategoryId.value },
        {
            preserveScroll: true,
            onSuccess: () => {
                isMoveOpen.value = false;
                selectedIds.value = [];
            },
            onFinish: () => {
                isMoving.value = false;
            },
        },
    );
}

const deleting = ref(null);
</script>

<template>
    <Head :title="t('Products')" />

    <PageHeader :title="t('Products')" :eyebrow="t('Catalog')" :description="t(':count products', { count: products.meta.total })">
        <template #actions>
            <AppButton v-if="can['products.create']" :href="route('admin.products.create', { category_id: filters.category ?? undefined })">
                <Icon name="plus" :size="16" />
                {{ t('Add product') }}
            </AppButton>
        </template>
    </PageHeader>

    <section class="mb-5 grid gap-3 rounded-2xl border border-line bg-white p-4 md:grid-cols-2 xl:grid-cols-[1.4fr_1.4fr_1fr_1fr]">
        <div class="relative">
            <Icon name="search" :size="18" class="pointer-events-none absolute start-3.5 top-1/2 -translate-y-1/2 text-muted" />
            <input
                v-model="filters.q"
                type="search"
                :placeholder="t('Search by name or code…')"
                :aria-label="t('Search products')"
                class="w-full rounded-lg border border-line py-2.5 ps-11 pe-3 text-sm placeholder:text-muted/80 focus:border-brass-500 focus:ring-2 focus:ring-brass-500/20 focus:outline-none"
            />
        </div>
        <div class="flex flex-col gap-2">
            <CategoryTreeSelect v-model="filters.category" :nodes="categories" allow-root :root-label="t('All categories')" />
            <label v-if="filters.category" class="flex items-center gap-2 text-xs text-ink-soft">
                <input v-model="filters.descendants" type="checkbox" class="size-4 accent-brass-500" />
                {{ t('Include subcategories') }}
            </label>
        </div>
        <SelectInput v-model="filters.status" :options="statusOptions" :aria-label="t('Status')" />
        <SelectInput v-model="filters.featured" :options="featuredOptions" :aria-label="t('Featured')" />
        <button v-if="hasFilters" type="button" class="justify-self-start text-sm text-brass-700 hover:underline" @click="clearFilters">{{ t('Clear filters') }}</button>
    </section>

    <div v-if="selectedIds.length > 0" class="mb-4 flex flex-wrap items-center gap-3 rounded-xl bg-ink px-4 py-3 text-sm text-white">
        <span>{{ t(':count selected', { count: selectedIds.length }) }}</span>
        <button v-if="can['products.update']" type="button" class="inline-flex items-center gap-1.5 rounded-lg bg-white/10 px-3 py-1.5 hover:bg-white/20" @click="isMoveOpen = true">
            <Icon name="move" :size="16" />
            {{ t('Move to category…') }}
        </button>
        <button type="button" class="ms-auto text-white/70 hover:text-white" @click="selectedIds = []">{{ t('Clear selection') }}</button>
    </div>

    <div class="overflow-hidden rounded-2xl border border-line bg-white">
        <table v-if="products.data.length > 0" class="w-full text-sm">
            <thead class="hidden border-b border-line bg-sand-50 text-xs text-muted md:table-header-group">
                <tr>
                    <th class="w-12 px-4 py-3 text-start">
                        <input type="checkbox" class="size-4 accent-brass-500" :checked="allSelected" :aria-label="t('Select all')" @change="toggleAll" />
                    </th>
                    <th class="px-4 py-3 text-start font-medium">{{ t('Product') }}</th>
                    <th class="px-4 py-3 text-start font-medium">{{ t('Category') }}</th>
                    <th class="px-4 py-3 text-start font-medium">{{ t('Status') }}</th>
                    <th class="w-24 px-4 py-3" />
                </tr>
            </thead>
            <tbody class="divide-y divide-line">
                <tr v-for="product in products.data" :key="product.id" class="flex flex-wrap items-center gap-x-3 gap-y-2 p-4 md:table-row md:p-0" :class="{ 'bg-brass-300/10': selectedIds.includes(product.id) }">
                    <td class="md:px-4 md:py-3">
                        <input v-model="selectedIds" type="checkbox" :value="product.id" class="size-4 accent-brass-500" :aria-label="t('Select :name', { name: product.name })" />
                    </td>
                    <td class="min-w-0 grow md:px-4 md:py-3">
                        <div class="flex items-center gap-3">
                            <div class="size-12 shrink-0 overflow-hidden rounded-lg bg-sand-100">
                                <img v-if="product.image" :src="product.image.thumb" alt="" class="h-full w-full object-cover" loading="lazy" />
                                <ImagePlaceholder v-else />
                            </div>
                            <div class="min-w-0">
                                <Link :href="route('admin.products.edit', product.id)" class="block truncate font-medium text-ink hover:text-brass-700">
                                    {{ product.name }}
                                </Link>
                                <p v-if="product.sku" class="truncate font-mono text-xs text-muted" dir="ltr">{{ product.sku }}</p>
                            </div>
                        </div>
                    </td>
                    <td class="w-full text-xs text-muted md:w-auto md:px-4 md:py-3">{{ categoryPath(product.category_id ?? product.category?.id) }}</td>
                    <td class="md:px-4 md:py-3">
                        <div class="flex flex-wrap items-center gap-1.5">
                            <span class="rounded-full px-2.5 py-0.5 text-xs" :class="product.status ? 'bg-success/10 text-success' : 'bg-sand-200 text-muted'">
                                {{ product.status ? t('Visible') : t('Hidden') }}
                            </span>
                            <span v-if="product.featured" class="inline-flex items-center gap-1 rounded-full bg-brass-300/25 px-2.5 py-0.5 text-xs text-brass-700">
                                <Icon name="star" :size="12" />
                                {{ t('Featured') }}
                            </span>
                        </div>
                    </td>
                    <td class="ms-auto md:px-4 md:py-3">
                        <div class="flex items-center justify-end gap-1">
                            <Link v-if="can['products.update']" :href="route('admin.products.edit', product.id)" class="flex size-9 items-center justify-center rounded-lg text-muted hover:bg-sand-100 hover:text-ink" :aria-label="t('Edit :name', { name: product.name })">
                                <Icon name="edit" :size="16" />
                            </Link>
                            <button v-if="can['products.delete']" type="button" class="flex size-9 items-center justify-center rounded-lg text-muted hover:bg-danger/5 hover:text-danger" :aria-label="t('Delete :name', { name: product.name })" @click="deleting = product">
                                <Icon name="trash" :size="16" />
                            </button>
                        </div>
                    </td>
                </tr>
            </tbody>
        </table>

        <div v-else class="flex flex-col items-center gap-3 px-4 py-16 text-center">
            <Icon name="box" :size="36" class="text-sand-300" />
            <p class="text-sm text-muted">{{ hasFilters ? t('No products match these filters.') : t('No products yet.') }}</p>
        </div>
    </div>

    <PaginationLinks class="mt-8" :meta="products.meta" :links="products.links" />

    <ModalDialog :show="isMoveOpen" :title="t('Move :count products', { count: selectedIds.length })" @close="isMoveOpen = false">
        <FormField :label="t('Destination category')" for="move-category">
            <CategoryTreeSelect id="move-category" v-model="moveCategoryId" :nodes="categories" />
        </FormField>

        <template #footer>
            <AppButton variant="secondary" @click="isMoveOpen = false">{{ t('Cancel') }}</AppButton>
            <AppButton :disabled="moveCategoryId === null" :loading="isMoving" @click="moveSelected">{{ t('Move') }}</AppButton>
        </template>
    </ModalDialog>

    <ProductDeleteDialog :product="deleting" @close="deleting = null" />
</template>
