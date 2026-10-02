<script setup>
import { Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

/**
 * The uploaded logo with the company name beside it (unless turned off in the settings
 * for logos that contain the name), or the name alone as a wordmark when no logo exists.
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
const showsName = computed(() => !source.value || site.value.showNameWithLogo);

const logoHeights = {
    sm: 'h-9',
    md: 'h-10 sm:h-12',
};

// Beside a logo, a long name wraps onto two balanced lines instead of crowding the header.
const nameSizes = {
    withLogo: {
        sm: 'max-w-44 text-base leading-tight text-balance',
        md: 'max-w-48 text-lg leading-tight text-balance sm:text-xl',
    },
    alone: {
        sm: 'text-2xl leading-none whitespace-nowrap',
        md: 'text-2xl leading-none whitespace-nowrap sm:text-[1.7rem]',
    },
};
</script>

<template>
    <Link :href="href ?? route('home')" class="inline-flex shrink-0 items-center gap-3" :aria-label="site.name">
        <img
            v-if="source"
            :src="source"
            :alt="showsName ? '' : site.name"
            class="w-auto max-w-[12rem] object-contain transition"
            :class="[logoHeights[size], { 'brightness-0 invert': needsWhiteFilter }]"
        />
        <span
            v-if="showsName"
            class="font-display transition-colors"
            :class="[nameSizes[source ? 'withLogo' : 'alone'][size], inverted ? 'text-white' : 'text-ink']"
        >
            {{ site.name }}
        </span>
    </Link>
</template>
