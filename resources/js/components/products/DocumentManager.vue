<script setup>
import { VueDraggable } from 'vue-draggable-plus';
import { useTranslations } from '../../composables/useTranslations';
import Icon from '../common/Icon.vue';

/**
 * Downloadable PDF files of a product (data sheets, installation guides, catalogues).
 * Existing files can be reordered and removed; new files are appended after them.
 * Nothing changes on the server until the product is saved.
 */
defineProps({
    errors: { type: Object, default: () => ({}) },
});

const documents = defineModel('documents', { type: Array, required: true });
const uploads = defineModel('uploads', { type: Array, required: true });

const { t } = useTranslations();

function addFiles(fileList) {
    for (const file of Array.from(fileList ?? [])) {
        if (file.type === 'application/pdf' || file.name.toLowerCase().endsWith('.pdf')) {
            uploads.value.push(file);
        }
    }
}

function formatSize(bytes) {
    return bytes >= 1024 * 1024 ? `${(bytes / 1024 / 1024).toFixed(1)} MB` : `${Math.max(1, Math.round(bytes / 1024))} KB`;
}
</script>

<template>
    <div class="flex flex-col gap-3">
        <VueDraggable v-if="documents.length > 0" v-model="documents" handle=".document-handle" :animation="150" tag="ul" class="flex flex-col gap-2">
            <li v-for="(document, index) in documents" :key="document.id" class="flex items-center gap-3 rounded-xl border border-line bg-sand-50 p-2.5">
                <button type="button" class="document-handle flex w-6 shrink-0 cursor-grab justify-center text-sand-400 hover:text-ink" :aria-label="t('Drag to reorder')">
                    <Icon name="grip-vertical" :size="16" />
                </button>
                <Icon name="file-text" :size="20" class="shrink-0 text-brass-600" />
                <a :href="document.url" target="_blank" rel="noopener" class="min-w-0 grow">
                    <span class="block truncate text-sm text-ink hover:underline"><bdi>{{ document.name }}</bdi></span>
                    <span class="block text-xs text-muted" dir="ltr">{{ document.extension }} · {{ document.size }}</span>
                </a>
                <button type="button" class="flex size-8 shrink-0 items-center justify-center rounded-lg text-muted hover:bg-danger/5 hover:text-danger" :aria-label="t('Remove file')" @click="documents.splice(index, 1)">
                    <Icon name="trash" :size="16" />
                </button>
            </li>
        </VueDraggable>

        <ul v-if="uploads.length > 0" class="flex flex-col gap-2">
            <li v-for="(file, index) in uploads" :key="`${file.name}-${index}`" class="flex flex-col gap-1 rounded-xl border border-dashed border-brass-400 bg-sand-50 p-2.5">
                <div class="flex items-center gap-3">
                    <span class="w-6 shrink-0" />
                    <Icon name="file-text" :size="20" class="shrink-0 text-brass-600" />
                    <span class="min-w-0 grow">
                        <span class="block truncate text-sm text-ink"><bdi>{{ file.name }}</bdi></span>
                        <span class="block text-xs text-muted" dir="ltr">PDF · {{ formatSize(file.size) }}</span>
                    </span>
                    <span class="shrink-0 rounded-full bg-brass-600 px-2 py-0.5 text-[10px] text-white">{{ t('New') }}</span>
                    <button type="button" class="flex size-8 shrink-0 items-center justify-center rounded-lg text-muted hover:bg-danger/5 hover:text-danger" :aria-label="t('Remove file')" @click="uploads.splice(index, 1)">
                        <Icon name="x" :size="16" />
                    </button>
                </div>
                <p v-if="errors[`document_uploads.${index}`]" class="ps-9 text-xs text-danger">{{ errors[`document_uploads.${index}`] }}</p>
            </li>
        </ul>

        <!-- The visually hidden input sits inside the label, so the label can show its keyboard focus. -->
        <label
            class="flex cursor-pointer items-center justify-center gap-2 rounded-xl border-2 border-dashed border-line px-4 py-5 text-sm text-muted transition-colors hover:border-sand-400 has-[:focus-visible]:outline-2 has-[:focus-visible]:outline-offset-2 has-[:focus-visible]:outline-brass-500"
            @dragover.prevent
            @drop.prevent="addFiles($event.dataTransfer?.files)"
        >
            <Icon name="upload" :size="18" />
            {{ t('Add PDF files') }}
            <input type="file" accept="application/pdf,.pdf" multiple class="sr-only" @change="addFiles($event.target.files); $event.target.value = ''" />
        </label>
        <p class="text-xs text-muted">{{ t('Data sheets, installation guides or catalogues. PDF, up to 10 MB each; the file name is shown to visitors.') }}</p>
        <p v-if="errors.document_uploads" class="text-sm text-danger">{{ errors.document_uploads }}</p>
    </div>
</template>
