<script setup>
import { route } from 'ziggy-js';
import AppBreadcrumbs from '../../components/breadcrumbs/AppBreadcrumbs.vue';
import CategoryCard from '../../components/category/CategoryCard.vue';
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
    <section class="mx-auto max-w-7xl px-4 pt-10 pb-16 sm:px-6 lg:px-8 lg:pt-14">
        <AppBreadcrumbs :items="breadcrumbs" />

        <div class="mt-8 max-w-3xl">
            <p class="eyebrow">{{ t('Collections') }}</p>
            <h1 class="mt-3 font-display text-4xl leading-tight text-ink sm:text-5xl lg:text-6xl">{{ t('Products') }}</h1>
            <p class="mt-4 text-base leading-relaxed text-muted">
                {{ t('Explore our collections of porcelain, ceramic and natural-effect tiles for every space.') }}
            </p>
        </div>

        <div v-if="categories.length > 0" class="mt-12 grid gap-4 sm:grid-cols-2 lg:grid-cols-3 lg:gap-6">
            <CategoryCard v-for="category in categories" :key="category.id" :category="category" />
        </div>
    </section>

    <section id="products" class="border-t border-line bg-white">
        <div class="mx-auto max-w-7xl px-4 py-16 sm:px-6 lg:px-8">
            <div class="mb-10 flex flex-col gap-6 sm:flex-row sm:items-end sm:justify-between">
                <div>
                    <h2 class="font-display text-3xl text-ink sm:text-4xl">{{ t('All products') }}</h2>
                    <p class="mt-2 text-sm text-muted" aria-live="polite">{{ t(':count products', { count: products.meta.total }) }}</p>
                </div>
                <ProductFilters :filters="filters" :url="route('products.index')" />
            </div>

            <ProductGrid :products="products.data" />

            <PaginationLinks class="mt-14" :meta="products.meta" :links="products.links" />
        </div>
    </section>
</template>
