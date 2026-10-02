<script setup>
import ProductCard from './ProductCard.vue';

defineProps({
    products: { type: Array, required: true },
});
</script>

<template>
    <div v-if="products.length > 0" class="grid grid-cols-2 gap-x-3 gap-y-8 sm:gap-x-6 sm:gap-y-10 lg:grid-cols-3 xl:grid-cols-4">
        <ProductCard v-for="product in products" :key="product.id" :product="product" />
    </div>
    <p v-else class="rounded-2xl border border-dashed border-line px-6 py-16 text-center text-sm text-muted">
        <slot name="empty">{{ $t('No products found.') }}</slot>
    </p>
</template>
