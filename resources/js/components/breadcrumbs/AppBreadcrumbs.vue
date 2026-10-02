<script setup>
import { Link } from '@inertiajs/vue3';
import Icon from '../common/Icon.vue';

/**
 * Renders a trail generated on the server from the category ancestry.
 */
defineProps({
    items: { type: Array, required: true },
});
</script>

<template>
    <nav :aria-label="$t('Breadcrumb')">
        <ol class="flex flex-wrap items-center gap-x-1.5 gap-y-1 text-xs text-muted">
            <li v-for="(item, index) in items" :key="index" class="flex items-center gap-1.5">
                <Link v-if="item.url && index < items.length - 1" :href="item.url" class="transition-colors hover:text-ink">{{ item.label }}</Link>
                <span v-else aria-current="page" class="text-ink-soft">{{ item.label }}</span>
                <Icon v-if="index < items.length - 1" name="chevron-right" :size="12" class="text-sand-400" />
            </li>
        </ol>
    </nav>
</template>
