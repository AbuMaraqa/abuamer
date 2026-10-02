<script setup>
import { Link, router } from '@inertiajs/vue3';
import { computed, ref, watch } from 'vue';
import { route } from 'ziggy-js';
import { subtreeStats } from '../../composables/useCategoryTree';
import { useTranslations } from '../../composables/useTranslations';
import AppButton from '../common/AppButton.vue';
import Icon from '../common/Icon.vue';
import ModalDialog from '../common/ModalDialog.vue';

/**
 * Deleting never cascades silently: child categories require explicit consent, and a
 * subtree that still holds products cannot be deleted at all (the server enforces both).
 */
const props = defineProps({
    node: { type: Object, default: null },
});

const emit = defineEmits(['close']);
const { t } = useTranslations();

const withDescendants = ref(false);
const isDeleting = ref(false);
const error = ref(null);

watch(
    () => props.node,
    () => {
        withDescendants.value = false;
        error.value = null;
    },
);

const stats = computed(() => (props.node ? subtreeStats(props.node) : { categories: 0, products: 0 }));
const canDelete = computed(() => stats.value.products === 0 && (stats.value.categories === 0 || withDescendants.value));

function destroy() {
    isDeleting.value = true;

    router.delete(route('admin.categories.destroy', props.node.id), {
        data: { with_descendants: withDescendants.value },
        preserveScroll: true,
        onSuccess: () => emit('close'),
        onError: (errors) => {
            error.value = errors.category ?? Object.values(errors)[0];
        },
        onFinish: () => {
            isDeleting.value = false;
        },
    });
}
</script>

<template>
    <ModalDialog :show="node !== null" :title="t('Delete “:name”', { name: node?.name ?? '' })" max-width="sm" @close="emit('close')">
        <div class="flex flex-col gap-4 text-sm text-ink-soft">
            <div v-if="stats.products > 0" class="flex gap-3 rounded-xl bg-danger/5 p-4 text-danger">
                <Icon name="alert" class="shrink-0" />
                <div class="flex flex-col gap-2">
                    <p>{{ t('This category contains :count products (including its subcategories). Move them to another category before deleting it.', { count: stats.products }) }}</p>
                    <Link :href="route('admin.products.index', { category: node.id })" class="font-medium underline">{{ t('View these products') }}</Link>
                </div>
            </div>

            <template v-else>
                <p>{{ t('This action cannot be undone.') }}</p>

                <label v-if="stats.categories > 0" class="flex items-start gap-3 rounded-xl border border-line p-4">
                    <input v-model="withDescendants" type="checkbox" class="mt-0.5 size-4 accent-danger" />
                    <span>{{ t('Also delete its :count subcategories.', { count: stats.categories }) }}</span>
                </label>
            </template>

            <p v-if="error" class="text-danger" role="alert">{{ error }}</p>
        </div>

        <template #footer>
            <AppButton variant="secondary" @click="emit('close')">{{ t('Cancel') }}</AppButton>
            <AppButton variant="danger" :disabled="!canDelete" :loading="isDeleting" @click="destroy">{{ t('Delete') }}</AppButton>
        </template>
    </ModalDialog>
</template>
