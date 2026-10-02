<script setup>
import { Link } from '@inertiajs/vue3';
import { computed } from 'vue';
import Icon from './Icon.vue';

/**
 * Pagination for a Laravel paginator sent through an API resource collection ({ links, meta }).
 */
const props = defineProps({
    meta: { type: Object, required: true },
    links: { type: Object, required: true },
});

// Laravel's meta.links holds "previous", the page numbers (with "..." gaps), then "next".
const pages = computed(() => props.meta.links.slice(1, -1));
</script>

<template>
    <nav v-if="meta.last_page > 1" class="flex items-center justify-center gap-1" :aria-label="$t('Pagination')">
        <Link
            v-if="links.prev"
            :href="links.prev"
            class="flex size-11 items-center justify-center rounded-full text-ink-soft transition-colors hover:bg-sand-200"
            :aria-label="$t('Previous page')"
        >
            <Icon name="chevron-right" :size="18" class="rotate-180" />
        </Link>

        <template v-for="(page, index) in pages" :key="index">
            <span v-if="!page.url" class="px-2 text-sm text-muted">…</span>
            <Link
                v-else
                :href="page.url"
                class="flex size-11 items-center justify-center rounded-full text-sm tabular-nums transition-colors"
                :class="page.active ? 'bg-ink text-white' : 'text-ink-soft hover:bg-sand-200'"
                :aria-current="page.active ? 'page' : undefined"
            >
                {{ page.label }}
            </Link>
        </template>

        <Link
            v-if="links.next"
            :href="links.next"
            class="flex size-11 items-center justify-center rounded-full text-ink-soft transition-colors hover:bg-sand-200"
            :aria-label="$t('Next page')"
        >
            <Icon name="chevron-right" :size="18" />
        </Link>
    </nav>
</template>
