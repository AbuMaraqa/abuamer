<script setup>
import { router } from '@inertiajs/vue3';
import { ref } from 'vue';
import { route } from 'ziggy-js';
import { useTranslations } from '../../composables/useTranslations';
import AppButton from '../common/AppButton.vue';
import ModalDialog from '../common/ModalDialog.vue';

const props = defineProps({
    product: { type: Object, default: null },
});

const emit = defineEmits(['close']);
const { t } = useTranslations();
const isDeleting = ref(false);

function destroy() {
    isDeleting.value = true;

    router.delete(route('admin.products.destroy', props.product.id), {
        onSuccess: () => emit('close'),
        onFinish: () => {
            isDeleting.value = false;
        },
    });
}
</script>

<template>
    <ModalDialog :show="product !== null" :title="t('Delete “:name”', { name: product?.name ?? '' })" max-width="sm" @close="emit('close')">
        <p class="text-sm text-ink-soft">{{ t('The product, its specifications and images will be deleted. This action cannot be undone.') }}</p>

        <template #footer>
            <AppButton variant="secondary" @click="emit('close')">{{ t('Cancel') }}</AppButton>
            <AppButton variant="danger" :loading="isDeleting" @click="destroy">{{ t('Delete') }}</AppButton>
        </template>
    </ModalDialog>
</template>
