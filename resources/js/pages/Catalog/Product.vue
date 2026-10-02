<script setup>
import { Head } from '@inertiajs/vue3';
import AppBreadcrumbs from '../../components/breadcrumbs/AppBreadcrumbs.vue';
import ProductGallery from '../../components/products/ProductGallery.vue';
import ProductGrid from '../../components/products/ProductGrid.vue';
import ProductSpecifications from '../../components/products/ProductSpecifications.vue';
import { useTranslations } from '../../composables/useTranslations';

defineProps({
    product: { type: Object, required: true },
    relatedProducts: { type: Array, required: true },
    breadcrumbs: { type: Array, required: true },
});

const { t } = useTranslations();
</script>

<template>
    <Head :title="product.seo_title || product.name" />

    <section class="mx-auto max-w-7xl px-4 pt-10 pb-16 sm:px-6 lg:px-8 lg:pt-14">
        <AppBreadcrumbs :items="breadcrumbs" />

        <div class="mt-8 grid gap-10 lg:grid-cols-2 lg:gap-16">
            <ProductGallery :images="product.gallery" :alt="product.name" />

            <div class="flex flex-col gap-8 lg:py-4">
                <div class="flex flex-col gap-3">
                    <p v-if="product.category" class="eyebrow">{{ product.category.name }}</p>
                    <h1 class="font-display text-4xl leading-tight text-ink sm:text-5xl">{{ product.name }}</h1>
                    <p v-if="product.sku" class="text-sm text-muted">
                        {{ t('Code') }}: <span dir="ltr">{{ product.sku }}</span>
                    </p>
                </div>

                <p v-if="product.short_description" class="text-base leading-relaxed text-ink-soft">{{ product.short_description }}</p>

                <div v-if="product.specifications.length > 0" class="flex flex-col gap-4">
                    <h2 class="text-sm font-semibold text-ink">{{ t('Specifications') }}</h2>
                    <ProductSpecifications :specifications="product.specifications" />
                </div>

                <div v-if="product.description" class="flex flex-col gap-3">
                    <h2 class="text-sm font-semibold text-ink">{{ t('Description') }}</h2>
                    <p class="text-sm leading-loose whitespace-pre-line text-ink-soft">{{ product.description }}</p>
                </div>
            </div>
        </div>
    </section>

    <section v-if="relatedProducts.length > 0" class="border-t border-line bg-white">
        <div class="mx-auto max-w-7xl px-4 py-16 sm:px-6 lg:px-8">
            <h2 class="mb-10 font-display text-3xl text-ink">{{ t('You may also like') }}</h2>
            <ProductGrid :products="relatedProducts" />
        </div>
    </section>
</template>
