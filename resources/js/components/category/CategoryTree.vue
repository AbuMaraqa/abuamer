<script setup>
import { router, usePage } from '@inertiajs/vue3';
import { computed, provide, ref, watch } from 'vue';
import { route } from 'ziggy-js';
import { filterTree, findPath, flattenTree } from '../../composables/useCategoryTree';
import { useTranslations } from '../../composables/useTranslations';
import Icon from '../common/Icon.vue';
import CategoryDeleteDialog from './CategoryDeleteDialog.vue';
import CategoryMoveDialog from './CategoryMoveDialog.vue';
import CategoryTreeList from './CategoryTreeList.vue';

/**
 * The control panel tree manager: expand/collapse, search, drag & drop, and node actions.
 *
 * Drag & drop is applied to a local copy immediately; the server then confirms the move
 * and returns the authoritative tree. If the server refuses (for example a cycle), the
 * local copy is reset to the last tree the server sent.
 */
const props = defineProps({
    tree: { type: Array, required: true },
    highlightId: { type: Number, default: null },
});

const page = usePage();
const { t } = useTranslations();

const STORAGE_KEY = 'nasaq.admin.category-tree.expanded';
const permissions = page.props.auth.user?.can ?? {};
const can = {
    create: !!permissions['categories.create'],
    update: !!permissions['categories.update'],
    delete: !!permissions['categories.delete'],
    move: !!permissions['categories.move'],
};

const clone = (value) => JSON.parse(JSON.stringify(value));
const nodes = ref(clone(props.tree));

watch(
    () => props.tree,
    (tree) => {
        nodes.value = clone(tree);
    },
);

/* Expansion --------------------------------------------------------------- */

function readStoredExpanded() {
    try {
        const stored = JSON.parse(localStorage.getItem(STORAGE_KEY) ?? 'null');

        return Array.isArray(stored) ? stored : null;
    } catch {
        return null;
    }
}

const expanded = ref(new Set(readStoredExpanded() ?? props.tree.map((node) => node.id)));

if (props.highlightId !== null) {
    findPath(nodes.value, props.highlightId)
        .slice(0, -1)
        .forEach((ancestor) => expanded.value.add(ancestor.id));
}

watch(
    () => [...expanded.value],
    (ids) => {
        try {
            localStorage.setItem(STORAGE_KEY, JSON.stringify(ids));
        } catch {
            // Storage may be unavailable (private mode); expansion simply isn't remembered.
        }
    },
);

/* Search ------------------------------------------------------------------ */

const searchTerm = ref('');
const isSearching = computed(() => searchTerm.value.trim() !== '');
const filtered = computed(() => (isSearching.value ? filterTree(nodes.value, searchTerm.value) : null));
const searchExpanded = ref(new Set());

watch(filtered, (result) => {
    searchExpanded.value = new Set(result?.expandedIds ?? []);
});

const visibleNodes = computed(() => filtered.value?.nodes ?? nodes.value);
const matchedIds = computed(() => filtered.value?.matchedIds ?? new Set());
const activeExpanded = computed(() => (isSearching.value ? searchExpanded.value : expanded.value));

function isExpanded(id) {
    return activeExpanded.value.has(id);
}

function toggle(id) {
    const set = activeExpanded.value;

    set.has(id) ? set.delete(id) : set.add(id);
}

function expandAll() {
    expanded.value = new Set(flattenTree(nodes.value).filter(({ node }) => node.children.length > 0).map(({ node }) => node.id));
}

function collapseAll() {
    expanded.value = new Set();
}

/* Drag & drop and server actions ----------------------------------------- */

const isDragging = ref(false);
const isSaving = ref(false);
const isDragDisabled = computed(() => !can.move || isSearching.value || isSaving.value);
let hoverTimer = null;
let hoveredId = null;

function dragStarted() {
    isDragging.value = true;
}

function dragEnded() {
    isDragging.value = false;
    clearTimeout(hoverTimer);
    hoveredId = null;
}

function hoverWhileDragging(id) {
    if (id === hoveredId) {
        return;
    }

    clearTimeout(hoverTimer);
    hoveredId = id;

    if (id !== null && !expanded.value.has(id)) {
        hoverTimer = setTimeout(() => expanded.value.add(id), 650);
    }
}

function notifyError(errors) {
    const message = Object.values(errors ?? {})[0] ?? t('The change could not be saved. Please try again.');

    router.flash('toast', { type: 'error', message });
}

/**
 * Persist a move: the category becomes the child of `parentId` at the 0-based `index`.
 */
function move(id, parentId, index) {
    let succeeded = false;
    isSaving.value = true;

    router.patch(
        route('admin.categories.move', id),
        { parent_id: parentId, sort_order: index + 1 },
        {
            preserveScroll: true,
            preserveState: true,
            only: ['tree'],
            onSuccess: () => {
                succeeded = true;
            },
            onError: notifyError,
            onFinish: () => {
                isSaving.value = false;

                if (!succeeded) {
                    nodes.value = clone(props.tree);
                }
            },
        },
    );
}

function toggleStatus(node) {
    router.patch(
        route('admin.categories.status', node.id),
        { status: !node.status },
        { preserveScroll: true, preserveState: true, only: ['tree'], onError: notifyError },
    );
}

/* Dialogs ----------------------------------------------------------------- */

const movingNode = ref(null);
const deletingNode = ref(null);

function moveFromDialog({ parentId, index }) {
    move(movingNode.value.id, parentId, index);
    movingNode.value = null;
}

provide('categoryTree', {
    locale: page.props.locale.current,
    can,
    highlightId: computed(() => props.highlightId),
    searchTerm,
    isSearching,
    matchedIds,
    isDragDisabled,
    isExpanded,
    toggle,
    move,
    toggleStatus,
    dragStarted,
    dragEnded,
    hoverWhileDragging,
    openMoveDialog: (node) => (movingNode.value = node),
    openDeleteDialog: (node) => (deletingNode.value = node),
});
</script>

<template>
    <div :class="{ 'is-dragging': isDragging }">
        <div class="mb-4 flex flex-col gap-3 sm:flex-row sm:items-center">
            <div class="relative grow">
                <Icon name="search" :size="18" class="pointer-events-none absolute start-3.5 top-1/2 -translate-y-1/2 text-muted" />
                <input
                    v-model="searchTerm"
                    type="search"
                    :placeholder="t('Search categories in Arabic or English…')"
                    :aria-label="t('Search categories')"
                    class="w-full rounded-xl border border-line bg-white py-2.5 ps-11 pe-4 text-sm placeholder:text-sand-400 focus:border-brass-500 focus:ring-2 focus:ring-brass-500/20 focus:outline-none"
                />
            </div>

            <div class="flex items-center gap-2">
                <button type="button" class="inline-flex items-center gap-1.5 rounded-lg px-3 py-2 text-sm text-ink-soft transition-colors hover:bg-sand-200" :disabled="isSearching" @click="expandAll">
                    <Icon name="chevrons-up-down" :size="16" />
                    {{ t('Expand all') }}
                </button>
                <button type="button" class="inline-flex items-center gap-1.5 rounded-lg px-3 py-2 text-sm text-ink-soft transition-colors hover:bg-sand-200" :disabled="isSearching" @click="collapseAll">
                    <Icon name="chevrons-down-up" :size="16" />
                    {{ t('Collapse all') }}
                </button>
            </div>
        </div>

        <p v-if="can.move" class="mb-3 flex items-center gap-2 text-xs text-muted">
            <Icon name="grip-vertical" :size="14" />
            <span v-if="isSearching">{{ t('Clear the search to reorder categories.') }}</span>
            <span v-else>{{ t('Drag a category to reorder it, or drop it onto another category to move it inside.') }}</span>
            <span v-if="isSaving" class="ms-auto text-brass-600">{{ t('Saving…') }}</span>
        </p>

        <div class="rounded-2xl border border-line bg-white p-2 sm:p-3">
            <CategoryTreeList
                v-if="visibleNodes.length > 0"
                :nodes="visibleNodes"
                :parent-id="null"
                @update:nodes="(updated) => !isSearching && (nodes = updated)"
            />

            <p v-else-if="isSearching" class="px-4 py-10 text-center text-sm text-muted">
                {{ t('No categories match ":term".', { term: searchTerm }) }}
            </p>

            <div v-else class="flex flex-col items-center gap-3 px-4 py-14 text-center">
                <Icon name="folder-tree" :size="36" class="text-sand-300" />
                <p class="text-sm text-muted">{{ t('No categories yet. Start with your first top-level category.') }}</p>
            </div>
        </div>

        <CategoryMoveDialog :node="movingNode" :nodes="nodes" @close="movingNode = null" @move="moveFromDialog" />
        <CategoryDeleteDialog :node="deletingNode" @close="deletingNode = null" />
    </div>
</template>
