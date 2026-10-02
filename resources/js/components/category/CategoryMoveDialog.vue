<script setup>
import { computed, ref, watch } from 'vue';
import { findNode } from '../../composables/useCategoryTree';
import { useTranslations } from '../../composables/useTranslations';
import AppButton from '../common/AppButton.vue';
import ModalDialog from '../common/ModalDialog.vue';
import FormField from '../form/FormField.vue';
import CategoryTreeSelect from './CategoryTreeSelect.vue';

/**
 * Keyboard- and touch-friendly alternative to drag & drop.
 */
const props = defineProps({
    node: { type: Object, default: null },
    nodes: { type: Array, required: true },
});

const emit = defineEmits(['close', 'move']);
const { t } = useTranslations();

const parentId = ref(null);
const placement = ref('last');

watch(
    () => props.node,
    (node) => {
        parentId.value = node?.parent_id ?? null;
        placement.value = 'last';
    },
);

const destinationChildren = computed(() => {
    const children = parentId.value === null ? props.nodes : (findNode(props.nodes, parentId.value)?.children ?? []);

    return children.filter((child) => child.id !== props.node?.id);
});

function submit() {
    emit('move', {
        parentId: parentId.value,
        index: placement.value === 'first' ? 0 : destinationChildren.value.length,
    });
}
</script>

<template>
    <ModalDialog :show="node !== null" :title="t('Move “:name”', { name: node?.name ?? '' })" @close="emit('close')">
        <form id="category-move-form" class="flex flex-col gap-5" @submit.prevent="submit">
            <FormField :label="t('New parent category')" for="move-parent">
                <CategoryTreeSelect id="move-parent" v-model="parentId" :nodes="nodes" :exclude-id="node?.id ?? null" allow-root />
            </FormField>

            <fieldset class="flex flex-col gap-2">
                <legend class="mb-1 text-sm font-medium text-ink-soft">{{ t('Position') }}</legend>
                <label class="flex items-center gap-2 text-sm text-ink-soft">
                    <input v-model="placement" type="radio" value="first" class="accent-brass-500" />
                    {{ t('At the beginning') }}
                </label>
                <label class="flex items-center gap-2 text-sm text-ink-soft">
                    <input v-model="placement" type="radio" value="last" class="accent-brass-500" />
                    {{ t('At the end') }}
                </label>
            </fieldset>
        </form>

        <template #footer>
            <AppButton variant="secondary" @click="emit('close')">{{ t('Cancel') }}</AppButton>
            <AppButton type="submit" form="category-move-form">{{ t('Move') }}</AppButton>
        </template>
    </ModalDialog>
</template>
