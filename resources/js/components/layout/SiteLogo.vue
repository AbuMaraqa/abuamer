<script setup>
import { Link } from '@inertiajs/vue3';

/**
 * The uploaded logo, or the company name set as a wordmark when no logo exists yet.
 */
defineProps({
    inverted: { type: Boolean, default: false },
});
</script>

<template>
    <Link :href="route('home')" class="inline-flex items-center" :aria-label="$page.props.site.name">
        <img
            v-if="$page.props.site.logo"
            :src="$page.props.site.logo"
            :alt="$page.props.site.name"
            class="h-10 w-auto transition sm:h-12"
            :class="{ 'brightness-0 invert': inverted }"
        />
        <span v-else class="font-display text-2xl leading-none whitespace-nowrap transition-colors sm:text-[1.7rem]" :class="inverted ? 'text-white' : 'text-ink'">
            {{ $page.props.site.name }}
        </span>
    </Link>
</template>
