<script setup>
import { route } from 'ziggy-js';
import AppBreadcrumbs from '../../components/breadcrumbs/AppBreadcrumbs.vue';
import CategoryGrid from '../../components/category/CategoryGrid.vue';
import PaginationLinks from '../../components/common/PaginationLinks.vue';
import ProductFilters from '../../components/products/ProductFilters.vue';
import ProductGrid from '../../components/products/ProductGrid.vue';
import { useTranslations } from '../../composables/useTranslations';

defineProps({
    categories: { type: Array, required: true },
    products: { type: Object, required: true },
    filters: { type: Object, required: true },
    breadcrumbs: { type: Array, required: true },
});

const { t } = useTranslations();
</script>

<template>
    <section class="mx-auto max-w-7xl px-4 pt-6 pb-12 sm:px-6 sm:pt-10 sm:pb-16 lg:px-8 lg:pt-14">
        <AppBreadcrumbs :items="breadcrumbs" />

        <div class="mt-6 max-w-3xl sm:mt-8">
            <p class="eyebrow">{{ t('Collections') }}</p>
            <h1 class="mt-3 font-display text-4xl leading-tight text-ink sm:text-5xl lg:text-6xl">{{ t('Products') }}</h1>
            <p class="mt-4 text-base leading-relaxed text-muted">
                {{ t('Explore our collections of porcelain, ceramic and natural-effect tiles for every space.') }}
            </p>
        </div>

        <CategoryGrid v-if="categories.length > 0" class="mt-8 sm:mt-12" :categories="categories" />
    </section>

    <section id="products" class="border-t border-line bg-white">
        <div class="mx-auto max-w-7xl px-4 py-12 sm:px-6 sm:py-16 lg:px-8">
            <div class="mb-8 flex flex-col gap-5 sm:mb-10 sm:flex-row sm:items-end sm:justify-between">
                <div>
                    <h2 class="font-display text-3xl text-ink sm:text-4xl">{{ t('All products') }}</h2>
                    <p class="mt-2 text-sm text-muted" aria-live="polite">{{ t(':count products', { count: products.meta.total }) }}</p>
                </div>
                <ProductFilters :filters="filters" :url="route('products.index')" />
            </div>

            <ProductGrid :products="products.data" />

            <PaginationLinks class="mt-10 sm:mt-14" :meta="products.meta" :links="products.links" />
        </div>
    </section>
</template>
