<script setup>
import { router } from '@inertiajs/vue3';
import AppBreadcrumbs from '../../components/breadcrumbs/AppBreadcrumbs.vue';
import BrandLogo from '../../components/brands/BrandLogo.vue';
import Icon from '../../components/common/Icon.vue';
import PaginationLinks from '../../components/common/PaginationLinks.vue';
import ProductFilters from '../../components/products/ProductFilters.vue';
import ProductGrid from '../../components/products/ProductGrid.vue';
import { countryName } from '../../composables/useCountryName';
import { useTranslations } from '../../composables/useTranslations';

const props = defineProps({
    brand: { type: Object, required: true },
    collections: { type: Array, required: true },
    products: { type: Object, required: true },
    filters: { type: Object, required: true },
    breadcrumbs: { type: Array, required: true },
});

const { t } = useTranslations();

function filterByCollection(categoryId) {
    router.get(
        props.brand.url,
        { ...props.filters, category: categoryId ?? undefined, q: props.filters.q || undefined, page: undefined },
        { only: ['products', 'filters'], preserveState: true, preserveScroll: true, replace: true },
    );
}

/**
 * The website's address without the protocol, as people read it ("www.example.com").
 */
function displayUrl(url) {
    return url.replace(/^https?:\/\//, '').replace(/\/$/, '');
}
</script>

<template>
    <section class="border-b border-line bg-white">
        <div class="mx-auto max-w-7xl px-4 pt-6 pb-10 sm:px-6 sm:pt-10 sm:pb-14 lg:px-8 lg:pt-14">
            <AppBreadcrumbs :items="breadcrumbs" />

            <div class="mt-8 grid items-center gap-8 sm:mt-10 md:grid-cols-[16rem_1fr] md:gap-12 lg:grid-cols-[20rem_1fr]">
                <div class="flex aspect-[3/2] items-center justify-center rounded-2xl border border-line bg-sand-50 px-8 text-4xl">
                    <BrandLogo :brand="brand" image-class="max-h-24" />
                </div>

                <div class="flex flex-col gap-4">
                    <p class="eyebrow">{{ t('Brand') }}</p>
                    <h1 class="font-display text-4xl leading-tight text-ink sm:text-5xl" dir="auto">{{ brand.name }}</h1>
                    <div class="flex flex-wrap items-center gap-x-5 gap-y-2 text-sm text-muted">
                        <span v-if="brand.country" class="inline-flex items-center gap-1.5">
                            <Icon name="map-pin" :size="16" class="text-brass-600" />
                            {{ t('Made in :country', { country: countryName(brand.country) }) }}
                        </span>
                        <a v-if="brand.website" :href="brand.website" target="_blank" rel="noopener" class="inline-flex items-center gap-1.5 transition-colors hover:text-ink">
                            <Icon name="external-link" :size="16" class="text-brass-600" />
                            <span dir="ltr">{{ displayUrl(brand.website) }}</span>
                        </a>
                    </div>
                    <p v-if="brand.description" class="max-w-3xl text-base leading-relaxed whitespace-pre-line text-ink-soft">{{ brand.description }}</p>
                </div>
            </div>
        </div>
    </section>

    <section class="mx-auto max-w-7xl px-4 py-12 sm:px-6 sm:py-16 lg:px-8">
        <div class="mb-8 flex flex-col gap-5 sm:mb-10 lg:flex-row lg:items-end lg:justify-between">
            <div>
                <h2 class="font-display text-2xl text-ink sm:text-3xl">{{ t(':brand products', { brand: brand.name }) }}</h2>
                <p class="mt-2 text-sm text-muted" aria-live="polite">{{ t(':count products', { count: products.meta.total }) }}</p>
            </div>
            <ProductFilters :filters="filters" :url="brand.url" />
        </div>

        <div v-if="collections.length > 1" class="-mx-4 mb-8 flex gap-2 overflow-x-auto px-4 pb-1 [scrollbar-width:none] sm:mx-0 sm:flex-wrap sm:px-0 [&::-webkit-scrollbar]:hidden" role="group" :aria-label="t('Collections')">
            <button
                type="button"
                class="shrink-0 rounded-full border px-4 py-2 text-sm transition-colors"
                :class="filters.category === null ? 'border-ink bg-ink text-white' : 'border-line text-ink-soft hover:border-ink hover:text-ink'"
                :aria-pressed="filters.category === null"
                @click="filterByCollection(null)"
            >
                {{ t('All') }}
            </button>
            <button
                v-for="collection in collections"
                :key="collection.id"
                type="button"
                class="shrink-0 rounded-full border px-4 py-2 text-sm transition-colors"
                :class="filters.category === collection.id ? 'border-ink bg-ink text-white' : 'border-line text-ink-soft hover:border-ink hover:text-ink'"
                :aria-pressed="filters.category === collection.id"
                @click="filterByCollection(collection.id)"
            >
                {{ collection.name }}
            </button>
        </div>

        <ProductGrid :products="products.data">
            <template #empty>{{ t('No products of this brand yet.') }}</template>
        </ProductGrid>

        <PaginationLinks class="mt-10 sm:mt-14" :meta="products.meta" :links="products.links" />
    </section>
</template>
