<script setup>
import { Link } from '@inertiajs/vue3';
import { countryName } from '../../composables/useCountryName';
import Icon from '../common/Icon.vue';
import BrandLogo from './BrandLogo.vue';

defineProps({
    brand: { type: Object, required: true },
});
</script>

<template>
    <Link :href="brand.url" class="group flex flex-col overflow-hidden rounded-2xl border border-line bg-white transition-colors hover:border-sand-400">
        <div class="flex aspect-[3/2] items-center justify-center bg-sand-50 px-8 text-2xl transition-colors group-hover:bg-sand-100 sm:text-3xl">
            <BrandLogo :brand="brand" image-class="max-h-16 sm:max-h-20" />
        </div>
        <div class="flex items-center justify-between gap-3 border-t border-line px-4 py-3.5 sm:px-5">
            <div class="min-w-0">
                <p class="truncate text-sm font-medium text-ink" dir="auto">{{ brand.name }}</p>
                <p class="mt-0.5 truncate text-xs text-muted">
                    <template v-if="brand.country">{{ countryName(brand.country) }}</template>
                    <template v-if="brand.country && brand.products_count !== undefined"> · </template>
                    <template v-if="brand.products_count !== undefined">{{ $t(':count products', { count: brand.products_count }) }}</template>
                </p>
            </div>
            <Icon name="arrow-right" :size="18" class="shrink-0 text-muted transition-transform group-hover:translate-x-1 group-hover:text-ink rtl:group-hover:-translate-x-1" />
        </div>
    </Link>
</template>
