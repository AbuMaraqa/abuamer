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
    <!-- Up to six per row; an incomplete last row is centred. -->
    <ul class="flex flex-wrap justify-center gap-3">
        <li
            v-for="brand in brands"
            :key="brand.id"
            class="basis-[calc((100%-0.75rem)/2)] sm:basis-[calc((100%-1.5rem)/3)] lg:basis-[calc((100%-3.75rem)/6)]"
        >
            <Link
                :href="brand.url"
                class="group flex aspect-[3/2] items-center justify-center rounded-2xl border border-line bg-white px-6 text-xl transition-colors hover:border-sand-400 sm:text-2xl"
                :aria-label="brand.name"
            >
                <span class="flex max-w-full items-center justify-center opacity-70 grayscale transition duration-300 group-hover:opacity-100 group-hover:grayscale-0">
                    <BrandLogo :brand="brand" image-class="max-h-12 sm:max-h-14" />
                </span>
            </Link>
        </li>
    </ul>
</template>
