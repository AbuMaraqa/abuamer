<script setup>
import { onBeforeUnmount, ref, useId } from 'vue';
import { VueDraggable } from 'vue-draggable-plus';
import { useTranslations } from '../../composables/useTranslations';
import Icon from '../common/Icon.vue';

/**
 * Existing gallery images can be reordered (drag) and removed; new files are previewed
 * and appended after them. Nothing changes on the server until the product is saved.
 */
defineProps({
    errors: { type: Object, default: () => ({}) },
});

const images = defineModel('images', { type: Array, required: true });
const uploads = defineModel('uploads', { type: Array, required: true });

const { t } = useTranslations();
const inputId = useId();
const previews = ref([]);

function addFiles(fileList) {
    for (const file of Array.from(fileList ?? [])) {
        if (!file.type.startsWith('image/')) {
            continue;
        }

        uploads.value.push(file);
        previews.value.push(URL.createObjectURL(file));
    }
}

function removeUpload(index) {
    URL.revokeObjectURL(previews.value[index]);
    uploads.value.splice(index, 1);
    previews.value.splice(index, 1);
}

function removeImage(index) {
    images.value.splice(index, 1);
}

onBeforeUnmount(() => previews.value.forEach((url) => URL.revokeObjectURL(url)));
</script>

<template>
    <div class="flex flex-col gap-3">
        <VueDraggable v-if="images.length > 0" v-model="images" :animation="150" class="grid grid-cols-3 gap-3 sm:grid-cols-4 lg:grid-cols-5">
            <figure v-for="(image, index) in images" :key="image.id" class="group relative aspect-square cursor-grab overflow-hidden rounded-xl border border-line bg-sand-100">
                <img :src="image.thumb" alt="" class="h-full w-full object-cover" />
                <button
                    type="button"
                    class="absolute end-1.5 top-1.5 flex size-7 items-center justify-center rounded-full bg-white/90 text-danger shadow sm:opacity-0 sm:group-hover:opacity-100 sm:focus:opacity-100"
                    :aria-label="t('Remove image')"
                    @click="removeImage(index)"
                >
                    <Icon name="x" :size="14" />
                </button>
            </figure>
        </VueDraggable>

        <div class="grid grid-cols-3 gap-3 sm:grid-cols-4 lg:grid-cols-5">
            <figure v-for="(preview, index) in previews" :key="preview" class="group relative aspect-square overflow-hidden rounded-xl border border-dashed border-brass-400 bg-sand-100">
                <img :src="preview" alt="" class="h-full w-full object-cover" />
                <span class="absolute start-1.5 bottom-1.5 rounded-full bg-brass-600 px-2 py-0.5 text-[10px] text-white">{{ t('New') }}</span>
                <button
                    type="button"
                    class="absolute end-1.5 top-1.5 flex size-7 items-center justify-center rounded-full bg-white/90 text-danger shadow"
                    :aria-label="t('Remove image')"
                    @click="removeUpload(index)"
                >
                    <Icon name="x" :size="14" />
                </button>
                <p v-if="errors[`gallery_uploads.${index}`]" class="absolute inset-x-0 bottom-0 bg-danger/90 p-1 text-[10px] text-white">
                    {{ errors[`gallery_uploads.${index}`] }}
                </p>
            </figure>

            <label
                :for="inputId"
                class="flex aspect-square cursor-pointer flex-col items-center justify-center gap-2 rounded-xl border-2 border-dashed border-line text-center text-xs text-muted transition-colors hover:border-sand-400"
                @dragover.prevent
                @drop.prevent="addFiles($event.dataTransfer?.files)"
            >
                <Icon name="plus" :size="20" />
                {{ t('Add images') }}
            </label>
        </div>

        <input :id="inputId" type="file" accept="image/jpeg,image/png,image/webp" multiple class="sr-only" @change="addFiles($event.target.files); $event.target.value = ''" />
        <p class="text-xs text-muted">{{ t('Drag images to reorder. At least 600 × 600 pixels, up to 8 MB each.') }}</p>
        <p v-if="errors.gallery_uploads" class="text-sm text-danger">{{ errors.gallery_uploads }}</p>
    </div>
</template>
