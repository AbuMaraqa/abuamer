<script setup>
import { inject } from 'vue';
import { VueDraggable } from 'vue-draggable-plus';
import CategoryTreeNode from './CategoryTreeNode.vue';

/**
 * One level of the tree: a sortable list whose items render their own children
 * through this same component, so any depth is supported.
 */
const props = defineProps({
    parentId: { type: Number, default: null },
    ancestorHidden: { type: Boolean, default: false },
});

const nodes = defineModel('nodes', { type: Array, required: true });

const tree = inject('categoryTree');

/**
 * Fired on the destination list for drops from another list (a new parent)
 * and for reordering within this list (same parent).
 */
function onDrop(event) {
    tree.move(Number(event.item.dataset.id), props.parentId, event.newIndex);
}

/**
 * Hovering a node while dragging opens it, so a category can be dropped into a collapsed node or a leaf.
 */
function onMove(event) {
    tree.hoverWhileDragging(Number(event.related?.dataset?.id) || null);

    return true;
}
</script>

<template>
    <VueDraggable
        v-model="nodes"
        tag="ul"
        group="categories"
        handle=".category-drag-handle"
        :disabled="tree.isDragDisabled.value"
        :animation="180"
        :swap-threshold="0.65"
        :empty-insert-threshold="18"
        :fallback-on-body="true"
        :delay="120"
        :delay-on-touch-only="true"
        ghost-class="category-ghost"
        chosen-class="category-chosen"
        :class="{ 'category-list-empty': nodes.length === 0 }"
        @start="tree.dragStarted()"
        @end="tree.dragEnded()"
        @add="onDrop"
        @update="onDrop"
        @move="onMove"
    >
        <CategoryTreeNode
            v-for="(node, index) in nodes"
            :key="node.id"
            :node="node"
            :index="index"
            :sibling-count="nodes.length"
            :parent-id="parentId"
            :ancestor-hidden="ancestorHidden"
        />
    </VueDraggable>
</template>
