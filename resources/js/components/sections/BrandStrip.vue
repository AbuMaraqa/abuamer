<script setup>
import { Link } from '@inertiajs/vue3';
import BrandLogo from '../brands/BrandLogo.vue';

/**
 * A row of brand logos, each leading to its brand page. Logos show in grey until hovered,
 * so differently coloured logos sit together calmly.
 */
defineProps({
    brands: { type: Array, required: true },
});
</script>

<template>
    <ul class="grid grid-cols-2 gap-px overflow-hidden rounded-2xl border border-line bg-line sm:grid-cols-3 lg:grid-cols-6">
        <li v-for="brand in brands" :key="brand.id" class="bg-white">
            <Link
                :href="brand.url"
                class="group flex aspect-[3/2] items-center justify-center px-6 text-xl transition-colors hover:bg-sand-50 sm:text-2xl"
                :aria-label="brand.name"
            >
                <span class="flex max-w-full items-center justify-center opacity-70 grayscale transition duration-300 group-hover:opacity-100 group-hover:grayscale-0">
                    <BrandLogo :brand="brand" image-class="max-h-12 sm:max-h-14" />
                </span>
            </Link>
        </li>
    </ul>
</template>
