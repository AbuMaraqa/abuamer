<script setup>
import { Link } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
    variant: { type: String, default: 'primary' },
    size: { type: String, default: 'md' },
    href: { type: String, default: null },
    type: { type: String, default: 'button' },
    loading: { type: Boolean, default: false },
});

const variants = {
    primary: 'bg-ink text-white hover:bg-ink-soft',
    accent: 'bg-brass-500 text-white hover:bg-brass-600',
    secondary: 'border border-line bg-white text-ink hover:border-sand-400',
    danger: 'bg-danger text-white hover:bg-danger/90',
    ghost: 'text-ink-soft hover:bg-sand-200',
};

const sizes = {
    sm: 'px-3 py-1.5 text-xs gap-1.5',
    md: 'px-4 py-2.5 text-sm gap-2',
    lg: 'px-6 py-3.5 text-sm gap-2.5',
};

const classes = computed(() => [
    'inline-flex items-center justify-center rounded-lg font-medium transition-colors disabled:cursor-not-allowed disabled:opacity-60',
    variants[props.variant],
    sizes[props.size],
]);
</script>

<template>
    <Link v-if="href" :href="href" :class="classes"><slot /></Link>
    <button v-else :type="type" :class="classes" :disabled="loading || $attrs.disabled">
        <svg v-if="loading" class="size-4 animate-spin" viewBox="0 0 24 24" fill="none" aria-hidden="true">
            <circle cx="12" cy="12" r="9" stroke="currentColor" stroke-opacity="0.25" stroke-width="3" />
            <path d="M21 12a9 9 0 0 0-9-9" stroke="currentColor" stroke-width="3" stroke-linecap="round" />
        </svg>
        <slot />
    </button>
</template>
