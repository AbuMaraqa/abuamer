<script setup>
import { computed, nextTick, onBeforeUnmount, ref, useId, watch } from 'vue';
import { useTranslations } from '../../composables/useTranslations';
import { findPath, flattenTree, normalizeSearchText, subtreeIds } from '../../composables/useCategoryTree';
import Icon from '../common/Icon.vue';

/**
 * Pick one category from the tree (indented, searchable). Used for a category's
 * parent and a product's category.
 */
const props = defineProps({
    nodes: { type: Array, required: true },
    placeholder: { type: String, default: null },
    allowRoot: { type: Boolean, default: false },
    rootLabel: { type: String, default: null },
    excludeId: { type: Number, default: null },
    invalid: { type: Boolean, default: false },
    id: { type: String, default: null },
});

const model = defineModel({ type: [Number, null], default: null });

const { t } = useTranslations();
const listId = useId();
const isOpen = ref(false);
const term = ref('');
const root = ref(null);
const searchInput = ref(null);

/**
 * A category cannot be placed inside itself or anything below it.
 */
const disabledIds = computed(() => {
    if (props.excludeId === null) {
        return new Set();
    }

    const excluded = findPath(props.nodes, props.excludeId).at(-1);

    return new Set(excluded ? subtreeIds(excluded) : []);
});

const options = computed(() => {
    const needle = normalizeSearchText(term.value);

    return flattenTree(props.nodes).filter(({ node }) =>
        needle === '' || [node.name, ...Object.values(node.names ?? {})].some((name) => normalizeSearchText(name).includes(needle)),
    );
});

const selectedPath = computed(() =>
    model.value === null ? null : findPath(props.nodes, model.value).map((node) => node.name).join(' / '),
);

function choose(id) {
    model.value = id;
    isOpen.value = false;
}

function onDocumentClick(event) {
    if (!root.value?.contains(event.target)) {
        isOpen.value = false;
    }
}

watch(isOpen, async (open) => {
    document[open ? 'addEventListener' : 'removeEventListener']('click', onDocumentClick, true);

    if (open) {
        term.value = '';
        await nextTick();
        searchInput.value?.focus();
    }
});

onBeforeUnmount(() => document.removeEventListener('click', onDocumentClick, true));
</script>

<template>
    <div ref="root" class="relative">
        <button
            :id="id"
            type="button"
            class="flex w-full items-center justify-between gap-3 rounded-lg border bg-white px-3.5 py-2.5 text-start text-sm transition-colors focus:border-brass-500 focus:ring-2 focus:ring-brass-500/20 focus:outline-none"
            :class="invalid ? 'border-danger/60' : 'border-line'"
            aria-haspopup="listbox"
            :aria-expanded="isOpen"
            :aria-controls="listId"
            @click="isOpen = !isOpen"
            @keydown.escape="isOpen = false"
        >
            <span class="truncate" :class="selectedPath ? 'text-ink' : 'text-sand-400'">
                {{ selectedPath ?? (allowRoot ? (rootLabel ?? t('None (top level)')) : (placeholder ?? t('Select a category'))) }}
            </span>
            <Icon name="chevrons-up-down" :size="16" class="shrink-0 text-muted" />
        </button>

        <div
            v-if="isOpen"
            class="absolute inset-x-0 top-full z-40 mt-1 overflow-hidden rounded-xl border border-line bg-white shadow-xl shadow-ink/10"
            @keydown.escape="isOpen = false"
        >
            <div class="border-b border-line p-2">
                <div class="relative">
                    <Icon name="search" :size="16" class="pointer-events-none absolute start-3 top-1/2 -translate-y-1/2 text-muted" />
                    <input
                        ref="searchInput"
                        v-model="term"
                        type="search"
                        :placeholder="t('Search categories…')"
                        class="w-full rounded-lg bg-sand-100 py-2 ps-9 pe-3 text-sm focus:outline-none"
                    />
                </div>
            </div>

            <ul :id="listId" role="listbox" class="max-h-72 overflow-y-auto py-1">
                <li v-if="allowRoot && term === ''">
                    <button
                        type="button"
                        role="option"
                        :aria-selected="model === null"
                        class="flex w-full items-center gap-2 px-3.5 py-2 text-start text-sm hover:bg-sand-100"
                        :class="model === null ? 'font-medium text-brass-600' : 'text-ink-soft'"
                        @click="choose(null)"
                    >
                        {{ rootLabel ?? t('None (top level)') }}
                    </button>
                </li>
                <li v-for="{ node, depth } in options" :key="node.id">
                    <button
                        type="button"
                        role="option"
                        :aria-selected="model === node.id"
                        :disabled="disabledIds.has(node.id)"
                        class="flex w-full items-center gap-2 py-2 pe-3.5 text-start text-sm hover:bg-sand-100 disabled:cursor-not-allowed disabled:text-sand-400 disabled:hover:bg-transparent"
                        :class="model === node.id ? 'font-medium text-brass-600' : 'text-ink-soft'"
                        :style="{ paddingInlineStart: `${0.875 + depth * 1.125}rem` }"
                        @click="choose(node.id)"
                    >
                        <span v-if="depth > 0" class="text-sand-400" aria-hidden="true">└</span>
                        <span class="truncate">{{ node.name }}</span>
                        <Icon v-if="model === node.id" name="check" :size="14" class="ms-auto shrink-0" />
                    </button>
                </li>
                <li v-if="options.length === 0" class="px-3.5 py-3 text-sm text-muted">{{ t('No categories found.') }}</li>
            </ul>
        </div>
    </div>
</template>
