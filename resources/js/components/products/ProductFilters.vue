<script setup>
import { router } from '@inertiajs/vue3';
import { onBeforeUnmount, ref, watch } from 'vue';
import Icon from '../common/Icon.vue';

/**
 * Server-side search and brand filter: each change reloads only the product list,
 * keeping the rest of the page.
 */
const props = defineProps({
    filters: { type: Object, required: true },
    url: { type: String, required: true },
    // Brands with products in this listing; the brand filter is shown when there are any.
    brands: { type: Array, default: () => [] },
});

const term = ref(props.filters.q ?? '');
const brand = ref(props.filters.brand ?? '');
const isSearching = ref(false);
let timer = null;

function reload() {
    router.get(
        props.url,
        { ...props.filters, q: term.value || undefined, brand: brand.value || undefined, page: undefined },
        {
            only: ['products', 'filters'],
            preserveState: true,
            preserveScroll: true,
            replace: true,
            onStart: () => (isSearching.value = true),
            onFinish: () => (isSearching.value = false),
        },
    );
}

watch(term, () => {
    clearTimeout(timer);
    timer = setTimeout(reload, 350);
});

watch(brand, () => {
    clearTimeout(timer);
    reload();
});

onBeforeUnmount(() => clearTimeout(timer));
</script>

<template>
    <div class="flex w-full flex-col gap-2.5 sm:w-auto sm:flex-row sm:items-center">
        <div v-if="brands.length > 0" class="relative sm:w-48">
            <Icon name="tag" :size="16" class="pointer-events-none absolute start-4 top-1/2 -translate-y-1/2 text-muted" />
            <select
                v-model="brand"
                :aria-label="$t('Filter by brand')"
                class="w-full appearance-none rounded-full border border-line bg-white py-3 ps-10 pe-10 text-base text-ink sm:text-sm focus:border-brass-500 focus:ring-2 focus:ring-brass-500/20 focus:outline-none"
                :class="{ 'border-brass-500': brand }"
            >
                <option value="">{{ $t('All brands') }}</option>
                <option v-for="option in brands" :key="option.id" :value="option.slug">{{ option.name }}</option>
            </select>
            <Icon name="chevron-down" :size="16" class="pointer-events-none absolute end-4 top-1/2 -translate-y-1/2 text-muted" />
        </div>

        <div class="relative w-full sm:w-72 lg:w-80">
            <svg v-if="isSearching" class="pointer-events-none absolute start-4 top-1/2 size-[18px] -translate-y-1/2 animate-spin text-brass-600" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                <circle cx="12" cy="12" r="9" stroke="currentColor" stroke-opacity="0.25" stroke-width="2.5" />
                <path d="M21 12a9 9 0 0 0-9-9" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" />
            </svg>
            <Icon v-else name="search" :size="18" class="pointer-events-none absolute start-4 top-1/2 -translate-y-1/2 text-muted" />
            <input
                v-model="term"
                type="search"
                :placeholder="$t('Search by name or code…')"
                :aria-label="$t('Search products')"
                :aria-busy="isSearching"
                class="w-full rounded-full border border-line bg-white py-3 ps-11 pe-4 text-base placeholder:text-muted/80 sm:text-sm focus:border-brass-500 focus:ring-2 focus:ring-brass-500/20 focus:outline-none"
            />
        </div>
    </div>
</template>
