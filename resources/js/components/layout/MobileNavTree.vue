<script setup>
import { Link } from '@inertiajs/vue3';
import { ref } from 'vue';
import Icon from '../common/Icon.vue';

/**
 * Collapsible category navigation for small screens; recursive, so any depth sent works.
 */
defineProps({
    nodes: { type: Array, required: true },
    depth: { type: Number, default: 0 },
});

const open = ref(new Set());

function toggle(id) {
    open.value.has(id) ? open.value.delete(id) : open.value.add(id);
}
</script>

<template>
    <ul class="flex flex-col" :class="depth > 0 ? 'border-s border-line ms-3 ps-3' : ''">
        <li v-for="node in nodes" :key="node.id">
            <div class="flex items-center justify-between gap-2">
                <Link :href="node.url" class="grow py-2.5" :class="depth === 0 ? 'text-base text-ink' : 'text-sm text-muted'">{{ node.name }}</Link>
                <button
                    v-if="node.children?.length"
                    type="button"
                    class="flex size-11 items-center justify-center text-muted"
                    :aria-expanded="open.has(node.id)"
                    :aria-label="open.has(node.id) ? $t('Collapse :name', { name: node.name }) : $t('Expand :name', { name: node.name })"
                    @click="toggle(node.id)"
                >
                    <Icon name="chevron-down" :size="18" class="transition-transform duration-200" :class="{ 'rotate-180': open.has(node.id) }" />
                </button>
            </div>
            <MobileNavTree v-if="node.children?.length && open.has(node.id)" :nodes="node.children" :depth="depth + 1" />
        </li>
    </ul>
</template>
