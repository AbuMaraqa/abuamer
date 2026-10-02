<script setup>
import { Link } from '@inertiajs/vue3';
import AppBreadcrumbs from '../../components/breadcrumbs/AppBreadcrumbs.vue';
import CategoryCard from '../../components/category/CategoryCard.vue';
import PaginationLinks from '../../components/common/PaginationLinks.vue';
import ProductFilters from '../../components/products/ProductFilters.vue';
import ProductGrid from '../../components/products/ProductGrid.vue';
import { useTranslations } from '../../composables/useTranslations';

defineProps({
    category: { type: Object, required: true },
    children: { type: Array, required: true },
    relatedCategories: { type: Array, required: true },
    products: { type: Object, required: true },
    filters: { type: Object, required: true },
    breadcrumbs: { type: Array, required: true },
});

const { t } = useTranslations();
</script>

<template>
    <section class="relative overflow-hidden bg-ink text-white">
        <img v-if="category.image" :src="category.image.large" alt="" class="absolute inset-0 h-full w-full object-cover opacity-45" />
        <div class="absolute inset-0 bg-gradient-to-t from-ink via-ink/40 to-ink/10" aria-hidden="true" />

        <div class="relative mx-auto flex min-h-[22rem] max-w-7xl flex-col justify-end gap-6 px-4 pt-24 pb-12 sm:px-6 lg:min-h-[28rem] lg:px-8">
            <AppBreadcrumbs :items="breadcrumbs" class="[&_a]:text-white/70 [&_a:hover]:text-white [&_span]:text-white [&_svg]:text-white/40" />
            <div class="max-w-3xl">
                <h1 class="font-display text-4xl leading-tight sm:text-5xl lg:text-6xl">{{ category.name }}</h1>
                <p v-if="category.description" class="mt-4 text-base leading-relaxed whitespace-pre-line text-white/80">{{ category.description }}</p>
            </div>
        </div>
    </section>

    <section v-if="children.length > 0" class="mx-auto max-w-7xl px-4 py-16 sm:px-6 lg:px-8">
        <h2 class="mb-8 font-display text-3xl text-ink">{{ t('Collections in :name', { name: category.name }) }}</h2>
        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3 lg:gap-6">
            <CategoryCard v-for="child in children" :key="child.id" :category="child" />
        </div>
    </section>

    <section class="border-t border-line bg-white">
        <div class="mx-auto max-w-7xl px-4 py-16 sm:px-6 lg:px-8">
            <div class="mb-10 flex flex-col gap-6 sm:flex-row sm:items-end sm:justify-between">
                <div>
                    <h2 class="font-display text-3xl text-ink">{{ t('Products') }}</h2>
                    <p class="mt-2 text-sm text-muted" aria-live="polite">{{ t(':count products', { count: products.meta.total }) }}</p>
                </div>
                <ProductFilters :filters="filters" :url="category.url" />
            </div>

            <ProductGrid :products="products.data">
                <template #empty>{{ t('No products in this collection yet.') }}</template>
            </ProductGrid>

            <PaginationLinks class="mt-14" :meta="products.meta" :links="products.links" />
        </div>
    </section>

    <section v-if="relatedCategories.length > 0" class="mx-auto max-w-7xl px-4 py-16 sm:px-6 lg:px-8">
        <h2 class="mb-6 font-display text-2xl text-ink">{{ t('Related collections') }}</h2>
        <div class="flex flex-wrap gap-3">
            <Link
                v-for="related in relatedCategories"
                :key="related.id"
                :href="related.url"
                class="rounded-full border border-line px-5 py-2.5 text-sm text-ink-soft transition-colors hover:border-ink hover:text-ink"
            >
                {{ related.name }}
            </Link>
        </div>
    </section>
</template>
