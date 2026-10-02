<script setup>
import { router } from '@inertiajs/vue3';
import { onBeforeUnmount, ref, watch } from 'vue';
import Icon from '../common/Icon.vue';

/**
 * Server-side search: typing reloads only the product list, keeping the rest of the page.
 */
const props = defineProps({
    filters: { type: Object, required: true },
    url: { type: String, required: true },
});

const term = ref(props.filters.q ?? '');
let timer = null;

watch(term, (value) => {
    clearTimeout(timer);
    timer = setTimeout(() => {
        router.get(
            props.url,
            { ...props.filters, q: value || undefined, page: undefined },
            { only: ['products', 'filters'], preserveState: true, preserveScroll: true, replace: true },
        );
    }, 350);
});

onBeforeUnmount(() => clearTimeout(timer));
</script>

<template>
    <div class="relative w-full sm:max-w-sm">
        <Icon name="search" :size="18" class="pointer-events-none absolute start-4 top-1/2 -translate-y-1/2 text-muted" />
        <input
            v-model="term"
            type="search"
            :placeholder="$t('Search by name or code…')"
            :aria-label="$t('Search products')"
            class="w-full rounded-full border border-line bg-white py-3 ps-11 pe-4 text-sm placeholder:text-sand-400 focus:border-brass-500 focus:ring-2 focus:ring-brass-500/20 focus:outline-none"
        />
    </div>
</template>
