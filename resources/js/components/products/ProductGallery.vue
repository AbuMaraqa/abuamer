<script setup>
import { usePage } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import Icon from '../common/Icon.vue';
import ImageLightbox from '../common/ImageLightbox.vue';
import ImagePlaceholder from '../common/ImagePlaceholder.vue';

const props = defineProps({
    images: { type: Array, required: true },
    alt: { type: String, required: true },
});

const isRtl = usePage().props.locale.direction === 'rtl';
const activeIndex = ref(0);
const active = computed(() => props.images[activeIndex.value] ?? null);

// The main image opens full screen, where the texture of a tile can be seen up close.
const lightboxIndex = ref(null);

/**
 * Arrow keys follow the reading direction: "next" is to the left in Arabic.
 */
function step(event) {
    if (props.images.length < 2) {
        return;
    }

    const forward = event.key === (isRtl ? 'ArrowLeft' : 'ArrowRight');
    activeIndex.value = (activeIndex.value + (forward ? 1 : -1) + props.images.length) % props.images.length;
}
</script>

<template>
    <div class="flex flex-col gap-4">
        <component
            :is="active ? 'button' : 'div'"
            :type="active ? 'button' : undefined"
            class="group relative block aspect-square w-full overflow-hidden rounded-2xl bg-sand-100"
            :class="{ 'cursor-zoom-in': active }"
            :aria-label="active ? `${$t('Enlarge image')}, ${$t('Image :current of :total', { current: activeIndex + 1, total: images.length })}` : undefined"
            @click="active && (lightboxIndex = activeIndex)"
            @keydown.left.prevent="step"
            @keydown.right.prevent="step"
        >
            <Transition mode="out-in" enter-active-class="transition-opacity duration-300" enter-from-class="opacity-0" leave-active-class="transition-opacity duration-200" leave-to-class="opacity-0">
                <img v-if="active" :key="active.id" :src="active.large" :alt="alt" class="h-full w-full object-cover" />
                <ImagePlaceholder v-else />
            </Transition>

            <span
                v-if="active"
                class="absolute end-4 bottom-4 flex size-11 items-center justify-center rounded-full bg-white/90 text-ink shadow-lg shadow-ink/10 backdrop-blur transition-opacity duration-200 sm:opacity-0 sm:group-hover:opacity-100 sm:group-focus-visible:opacity-100"
                aria-hidden="true"
            >
                <Icon name="zoom-in" :size="20" />
            </span>
        </component>

        <div v-if="images.length > 1" class="flex gap-3 overflow-x-auto pb-1">
            <button
                v-for="(image, index) in images"
                :key="image.id"
                type="button"
                class="size-20 shrink-0 overflow-hidden rounded-xl border-2 transition sm:size-24"
                :class="index === activeIndex ? 'border-brass-500' : 'border-transparent opacity-70 hover:opacity-100'"
                :aria-label="$t('Show image :number', { number: index + 1 })"
                :aria-pressed="index === activeIndex"
                @click="activeIndex = index"
            >
                <img :src="image.thumb" alt="" loading="lazy" class="h-full w-full object-cover" />
            </button>
        </div>

        <ImageLightbox v-model="lightboxIndex" :images="images" :alt="alt" />
    </div>
</template>
