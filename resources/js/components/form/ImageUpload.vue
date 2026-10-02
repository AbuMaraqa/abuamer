<script setup>
import { computed, onBeforeUnmount, ref, useId } from 'vue';
import Icon from '../common/Icon.vue';

/**
 * A single-image picker with preview. v-model holds the newly chosen File;
 * `removed` is set when the user clears an image that already exists.
 */
const props = defineProps({
    current: { type: Object, default: null },
    error: { type: String, default: null },
    hint: { type: String, default: null },
});

const file = defineModel({ type: [File, null], default: null });
const removed = defineModel('removed', { type: Boolean, default: false });

const inputId = useId();
const previewUrl = ref(null);
const isDragging = ref(false);

const shownImage = computed(() => previewUrl.value ?? (removed.value ? null : (props.current?.thumb ?? null)));

function setFile(selected) {
    if (!selected || !selected.type.startsWith('image/')) {
        return;
    }

    if (previewUrl.value) {
        URL.revokeObjectURL(previewUrl.value);
    }

    file.value = selected;
    previewUrl.value = URL.createObjectURL(selected);
    removed.value = false;
}

function clear() {
    if (previewUrl.value) {
        URL.revokeObjectURL(previewUrl.value);
    }

    previewUrl.value = null;
    file.value = null;
    removed.value = !!props.current;
}

function onDrop(event) {
    isDragging.value = false;
    setFile(event.dataTransfer?.files?.[0]);
}

onBeforeUnmount(() => previewUrl.value && URL.revokeObjectURL(previewUrl.value));
</script>

<template>
    <div class="flex flex-col gap-2">
        <div v-if="shownImage" class="group relative overflow-hidden rounded-xl border border-line bg-sand-100">
            <img :src="shownImage" alt="" class="aspect-[4/3] w-full object-cover" />
            <div class="absolute inset-x-0 bottom-0 flex justify-end gap-2 bg-gradient-to-t from-ink/60 to-transparent p-3">
                <label :for="inputId" class="cursor-pointer rounded-lg bg-white/90 px-3 py-1.5 text-xs font-medium text-ink hover:bg-white">
                    {{ $t('Replace') }}
                </label>
                <button type="button" class="rounded-lg bg-white/90 px-3 py-1.5 text-xs font-medium text-danger hover:bg-white" @click="clear">
                    {{ $t('Remove') }}
                </button>
            </div>
        </div>

        <label
            v-else
            :for="inputId"
            class="flex aspect-[4/3] cursor-pointer flex-col items-center justify-center gap-3 rounded-xl border-2 border-dashed px-6 text-center transition-colors"
            :class="isDragging ? 'border-brass-500 bg-brass-300/10' : error ? 'border-danger/50' : 'border-line hover:border-sand-400'"
            @dragover.prevent="isDragging = true"
            @dragleave="isDragging = false"
            @drop.prevent="onDrop"
        >
            <Icon name="upload" :size="28" class="text-sand-400" />
            <span class="text-sm text-ink-soft">{{ $t('Drop an image here or click to choose') }}</span>
            <span class="text-xs text-muted">JPG, PNG, WebP</span>
        </label>

        <input :id="inputId" type="file" accept="image/jpeg,image/png,image/webp" class="sr-only" @change="setFile($event.target.files[0]); $event.target.value = ''" />

        <p v-if="error" class="text-sm text-danger" role="alert">{{ error }}</p>
        <p v-else-if="hint" class="text-xs text-muted">{{ hint }}</p>
    </div>
</template>
