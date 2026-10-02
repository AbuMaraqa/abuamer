<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import { VueDraggable } from 'vue-draggable-plus';
import { route } from 'ziggy-js';
import PageHeader from '../../../components/admin/PageHeader.vue';
import AppButton from '../../../components/common/AppButton.vue';
import Icon from '../../../components/common/Icon.vue';
import ImagePlaceholder from '../../../components/common/ImagePlaceholder.vue';
import ModalDialog from '../../../components/common/ModalDialog.vue';
import { useTranslations } from '../../../composables/useTranslations';

const props = defineProps({
    slides: { type: Array, required: true },
});

const { t } = useTranslations();

const items = ref([...props.slides]);
watch(() => props.slides, (slides) => (items.value = [...slides]));

const sync = { preserveScroll: true, preserveState: true, only: ['slides'] };

function saveOrder() {
    router.patch(route('admin.slides.reorder'), { ids: items.value.map((slide) => slide.id) }, sync);
}

function toggleStatus(slide) {
    router.patch(route('admin.slides.status', slide.id), { status: !slide.status }, sync);
}

const deleting = ref(null);
const isDeleting = ref(false);

function destroy() {
    isDeleting.value = true;

    router.delete(route('admin.slides.destroy', deleting.value.id), {
        preserveScroll: true,
        onSuccess: () => (deleting.value = null),
        onFinish: () => (isDeleting.value = false),
    });
}
</script>

<template>
    <Head :title="t('Slider')" />

    <PageHeader :title="t('Slider')" :eyebrow="t('Home page')" :description="t('Full-screen slides at the top of the home page. Drag to change their order.')">
        <template #actions>
            <AppButton :href="route('admin.slides.create')">
                <Icon name="plus" :size="16" />
                {{ t('Add slide') }}
            </AppButton>
        </template>
    </PageHeader>

    <VueDraggable v-if="items.length > 0" v-model="items" handle=".slide-handle" :animation="180" tag="ol" class="flex flex-col gap-3" @end="saveOrder">
        <li v-for="(slide, index) in items" :key="slide.id" class="flex items-center gap-3 rounded-2xl border border-line bg-white p-3 sm:gap-4">
            <button type="button" class="slide-handle flex size-9 shrink-0 cursor-grab items-center justify-center text-sand-400 hover:text-ink" :aria-label="t('Drag to reorder')">
                <Icon name="grip-vertical" :size="18" />
            </button>

            <div class="relative aspect-video w-28 shrink-0 overflow-hidden rounded-lg bg-sand-100 sm:w-44">
                <img v-if="slide.image" :src="slide.image.thumb" alt="" class="h-full w-full object-cover" :class="{ 'opacity-40 grayscale': !slide.status }" />
                <ImagePlaceholder v-else />
                <span class="absolute start-1.5 top-1.5 rounded bg-ink/70 px-1.5 py-0.5 text-[10px] text-white tabular-nums" dir="ltr">{{ String(index + 1).padStart(2, '0') }}</span>
            </div>

            <div class="min-w-0 grow">
                <p v-if="slide.eyebrow" class="truncate text-xs text-brass-600">{{ slide.eyebrow }}</p>
                <Link :href="route('admin.slides.edit', slide.id)" class="block truncate font-medium text-ink hover:text-brass-700">{{ slide.title }}</Link>
                <p class="mt-1 flex items-center gap-1.5 truncate text-xs text-muted">
                    <template v-if="slide.button">
                        <Icon name="arrow-right" :size="12" />
                        {{ slide.button.label }}
                    </template>
                    <template v-else>{{ t('No button') }}</template>
                </p>
            </div>

            <button
                type="button"
                class="hidden shrink-0 rounded-full px-3 py-1 text-xs sm:inline-flex"
                :class="slide.status ? 'bg-success/10 text-success' : 'bg-sand-200 text-muted'"
                :title="slide.status ? t('Hide from website') : t('Show on website')"
                @click="toggleStatus(slide)"
            >
                {{ slide.status ? t('Visible') : t('Hidden') }}
            </button>

            <div class="flex shrink-0 items-center">
                <button type="button" class="flex size-9 items-center justify-center rounded-lg text-muted hover:bg-sand-100 hover:text-ink sm:hidden" :aria-label="slide.status ? t('Hide from website') : t('Show on website')" @click="toggleStatus(slide)">
                    <Icon :name="slide.status ? 'eye' : 'eye-off'" :size="16" />
                </button>
                <Link :href="route('admin.slides.edit', slide.id)" class="flex size-9 items-center justify-center rounded-lg text-muted hover:bg-sand-100 hover:text-ink" :aria-label="t('Edit :name', { name: slide.title })">
                    <Icon name="edit" :size="16" />
                </Link>
                <button type="button" class="flex size-9 items-center justify-center rounded-lg text-muted hover:bg-danger/5 hover:text-danger" :aria-label="t('Delete :name', { name: slide.title })" @click="deleting = slide">
                    <Icon name="trash" :size="16" />
                </button>
            </div>
        </li>
    </VueDraggable>

    <div v-else class="flex flex-col items-center gap-3 rounded-2xl border border-dashed border-line px-6 py-16 text-center">
        <Icon name="image" :size="36" class="text-sand-300" />
        <p class="text-sm text-ink-soft">{{ t('No slides yet.') }}</p>
        <p class="max-w-md text-xs text-muted">{{ t('Until you add slides, the home page shows the hero headline and image from the Company page.') }}</p>
    </div>

    <ModalDialog :show="deleting !== null" :title="t('Delete “:name”', { name: deleting?.title ?? '' })" max-width="sm" @close="deleting = null">
        <p class="text-sm text-ink-soft">{{ t('This action cannot be undone.') }}</p>
        <template #footer>
            <AppButton variant="secondary" @click="deleting = null">{{ t('Cancel') }}</AppButton>
            <AppButton variant="danger" :loading="isDeleting" @click="destroy">{{ t('Delete') }}</AppButton>
        </template>
    </ModalDialog>
</template>
