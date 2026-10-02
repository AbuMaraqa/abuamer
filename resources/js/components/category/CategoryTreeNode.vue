<script setup>
import { Link } from '@inertiajs/vue3';
import { computed, inject, onMounted, ref } from 'vue';
import { route } from 'ziggy-js';
import { useTranslations } from '../../composables/useTranslations';
import { normalizeSearchText } from '../../composables/useCategoryTree';
import DropdownItem from '../common/DropdownItem.vue';
import DropdownMenu from '../common/DropdownMenu.vue';
import Icon from '../common/Icon.vue';
import CategoryTreeList from './CategoryTreeList.vue';

const props = defineProps({
    node: { type: Object, required: true },
    index: { type: Number, required: true },
    siblingCount: { type: Number, required: true },
    parentId: { type: Number, default: null },
    ancestorHidden: { type: Boolean, default: false },
});

const tree = inject('categoryTree');
const { t } = useTranslations();
const row = ref(null);

const isExpanded = computed(() => tree.isExpanded(props.node.id));
const hasChildren = computed(() => props.node.children.length > 0);
const isHighlighted = computed(() => tree.highlightId.value === props.node.id);
const isMatch = computed(() => tree.matchedIds.value.has(props.node.id));
const otherName = computed(() => Object.entries(props.node.names ?? {}).find(([locale]) => locale !== tree.locale)?.[1]);

/**
 * Split the name around the search term so the match can be marked.
 */
const nameParts = computed(() => {
    const name = props.node.name;
    const term = tree.searchTerm.value.trim();

    if (!isMatch.value || term === '') {
        return [{ text: name, match: false }];
    }

    const start = normalizeSearchText(name).indexOf(normalizeSearchText(term));

    if (start < 0 || normalizeSearchText(name).length !== name.toLowerCase().length) {
        return [{ text: name, match: false }];
    }

    const end = start + normalizeSearchText(term).length;

    return [
        { text: name.slice(0, start), match: false },
        { text: name.slice(start, end), match: true },
        { text: name.slice(end), match: false },
    ];
});

onMounted(() => {
    if (isHighlighted.value) {
        row.value?.scrollIntoView({ block: 'center', behavior: 'smooth' });
    }
});
</script>

<template>
    <li :data-id="node.id" class="category-node">
        <div
            ref="row"
            class="group flex items-center gap-1.5 rounded-lg py-1.5 pe-1.5 ps-1 transition-colors"
            :class="[
                isHighlighted ? 'bg-brass-300/20 ring-1 ring-brass-400' : 'hover:bg-sand-100',
                tree.isDragDisabled.value ? '' : 'sm:ps-0',
            ]"
        >
            <button
                v-if="tree.can.move && !tree.isDragDisabled.value"
                type="button"
                class="category-drag-handle flex size-7 shrink-0 cursor-grab items-center justify-center rounded text-sand-400 transition-colors hover:text-ink active:cursor-grabbing"
                :aria-label="t('Drag to move :name', { name: node.name })"
            >
                <Icon name="grip-vertical" :size="16" />
            </button>

            <button
                v-if="hasChildren"
                type="button"
                class="flex size-7 shrink-0 items-center justify-center rounded text-muted transition-colors hover:bg-sand-200 hover:text-ink"
                :aria-expanded="isExpanded"
                :aria-label="isExpanded ? t('Collapse :name', { name: node.name }) : t('Expand :name', { name: node.name })"
                @click="tree.toggle(node.id)"
            >
                <Icon name="chevron-down" :size="16" class="transition-transform duration-200" :class="isExpanded ? '' : 'ltr:-rotate-90 rtl:rotate-90'" />
            </button>
            <span v-else class="flex size-7 shrink-0 items-center justify-center text-sand-300" aria-hidden="true">
                <span class="size-1.5 rounded-full bg-current" />
            </span>

            <div class="min-w-0 grow">
                <div class="flex flex-wrap items-center gap-x-2 gap-y-0.5">
                    <component
                        :is="tree.can.update ? Link : 'span'"
                        :href="tree.can.update ? route('admin.categories.edit', node.id) : undefined"
                        class="truncate text-sm font-medium"
                        :class="node.status && !ancestorHidden ? 'text-ink' : 'text-muted'"
                    >
                        <template v-for="(part, partIndex) in nameParts" :key="partIndex">
                            <mark v-if="part.match" class="rounded bg-brass-300/50 text-ink">{{ part.text }}</mark>
                            <template v-else>{{ part.text }}</template>
                        </template>
                    </component>

                    <span v-if="!node.status" class="rounded-full bg-sand-200 px-2 py-0.5 text-[11px] text-muted">{{ t('Hidden') }}</span>
                    <span v-else-if="ancestorHidden" class="rounded-full border border-dashed border-sand-300 px-2 py-0.5 text-[11px] text-muted">
                        {{ t('Hidden by parent') }}
                    </span>
                </div>
                <p v-if="otherName" class="truncate text-xs text-muted">{{ otherName }}</p>
            </div>

            <span
                class="hidden shrink-0 rounded-full bg-sand-100 px-2.5 py-0.5 text-xs text-muted tabular-nums sm:inline"
                :title="t('Products directly in this category')"
            >
                {{ t(':count products', { count: node.products_count ?? 0 }) }}
            </span>

            <DropdownMenu :label="t('Actions for :name', { name: node.name })">
                <template #trigger>
                    <Icon name="more-vertical" :size="18" :stroke-width="2.5" />
                </template>

                <DropdownItem v-if="tree.can.create" icon="plus" :href="route('admin.categories.create', { parent_id: node.id })">
                    {{ t('Add subcategory') }}
                </DropdownItem>
                <DropdownItem v-if="tree.can.update" icon="edit" :href="route('admin.categories.edit', node.id)">
                    {{ t('Edit') }}
                </DropdownItem>
                <template v-if="tree.can.move && !tree.isSearching.value">
                    <DropdownItem icon="move" @select="tree.openMoveDialog(node)">{{ t('Move to…') }}</DropdownItem>
                    <DropdownItem v-if="index > 0" icon="chevron-down" class="[&_svg]:rotate-180" @select="tree.move(node.id, parentId, index - 1)">
                        {{ t('Move up') }}
                    </DropdownItem>
                    <DropdownItem v-if="index < siblingCount - 1" icon="chevron-down" @select="tree.move(node.id, parentId, index + 1)">
                        {{ t('Move down') }}
                    </DropdownItem>
                </template>
                <DropdownItem v-if="tree.can.update" :icon="node.status ? 'eye-off' : 'eye'" @select="tree.toggleStatus(node)">
                    {{ node.status ? t('Hide from website') : t('Show on website') }}
                </DropdownItem>
                <DropdownItem v-if="node.status && !ancestorHidden" icon="globe" :href="node.url" external>
                    {{ t('View on website') }}
                </DropdownItem>
                <DropdownItem v-if="tree.can.delete" icon="trash" danger @select="tree.openDeleteDialog(node)">
                    {{ t('Delete') }}
                </DropdownItem>
            </DropdownMenu>
        </div>

        <CategoryTreeList
            v-if="isExpanded"
            v-model:nodes="node.children"
            :parent-id="node.id"
            :ancestor-hidden="ancestorHidden || !node.status"
            class="category-children ms-[1.6rem] border-s border-line ps-2"
        />
    </li>
</template>
