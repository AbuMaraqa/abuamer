<script setup>
import { Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

/**
 * The uploaded logo, or the company name set as a wordmark when no logo exists yet.
 * On dark backgrounds the dedicated light logo is used, or the main logo turned white.
 */
const props = defineProps({
    inverted: { type: Boolean, default: false },
    href: { type: String, default: null },
    size: { type: String, default: 'md' },
});

const site = computed(() => usePage().props.site);
const source = computed(() => (props.inverted && site.value.logoLight ? site.value.logoLight : site.value.logo));
// Only a transparent logo can be turned white; an opaque one would become a white rectangle.
const needsWhiteFilter = computed(() => props.inverted && !site.value.logoLight && site.value.logoIsTransparent);

const heights = {
    sm: 'h-9',
    md: 'h-10 sm:h-12',
};
</script>

<template>
    <Link :href="href ?? route('home')" class="inline-flex shrink-0 items-center" :aria-label="site.name">
        <img
            v-if="source"
            :src="source"
            :alt="site.name"
            class="w-auto max-w-[12rem] object-contain transition"
            :class="[heights[size], { 'brightness-0 invert': needsWhiteFilter }]"
        />
        <span v-else class="font-display text-2xl leading-none whitespace-nowrap transition-colors sm:text-[1.7rem]" :class="inverted ? 'text-white' : 'text-ink'">
            {{ site.name }}
        </span>
    </Link>
</template>
