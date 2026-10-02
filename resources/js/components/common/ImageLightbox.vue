<script setup>
import { usePage } from '@inertiajs/vue3';
import { computed, onBeforeUnmount, watch } from 'vue';
import Icon from './Icon.vue';

/**
 * Full-screen image viewer. v-model holds the open index (null when closed).
 */
const props = defineProps({
    images: { type: Array, required: true },
    alt: { type: String, default: '' },
});

const index = defineModel({ type: [Number, null], default: null });

const isRtl = usePage().props.locale.direction === 'rtl';
const current = computed(() => (index.value === null ? null : props.images[index.value]));

function step(delta) {
    index.value = (index.value + delta + props.images.length) % props.images.length;
}

function onKeydown(event) {
    if (event.key === 'Escape') {
        index.value = null;
    } else if (event.key === 'ArrowRight') {
        step(isRtl ? -1 : 1);
    } else if (event.key === 'ArrowLeft') {
        step(isRtl ? 1 : -1);
    }
}

watch(index, (value, previous) => {
    if (value !== null && previous === null) {
        document.addEventListener('keydown', onKeydown);
        document.body.style.overflow = 'hidden';
    } else if (value === null) {
        document.removeEventListener('keydown', onKeydown);
        document.body.style.overflow = '';
    }
});

onBeforeUnmount(() => {
    document.removeEventListener('keydown', onKeydown);
    document.body.style.overflow = '';
});
</script>

<template>
    <Teleport to="body">
        <Transition enter-active-class="transition-opacity duration-200" enter-from-class="opacity-0" leave-active-class="transition-opacity duration-150" leave-to-class="opacity-0">
            <div v-if="current" class="fixed inset-0 z-50 flex items-center justify-center bg-ink/95 p-4 sm:p-10" role="dialog" aria-modal="true" :aria-label="alt">
                <img :src="current.url" :alt="alt" class="max-h-full max-w-full rounded-lg object-contain" />

                <button type="button" class="absolute end-4 top-4 flex size-11 items-center justify-center rounded-full bg-white/10 text-white hover:bg-white/20" :aria-label="$t('Close')" @click="index = null">
                    <Icon name="x" />
                </button>

                <template v-if="images.length > 1">
                    <button type="button" class="absolute start-4 top-1/2 flex size-12 -translate-y-1/2 items-center justify-center rounded-full bg-white/10 text-white hover:bg-white/20" :aria-label="$t('Previous image')" @click="step(-1)">
                        <Icon name="chevron-right" class="rotate-180" />
                    </button>
                    <button type="button" class="absolute end-4 top-1/2 flex size-12 -translate-y-1/2 items-center justify-center rounded-full bg-white/10 text-white hover:bg-white/20" :aria-label="$t('Next image')" @click="step(1)">
                        <Icon name="chevron-right" />
                    </button>
                    <p class="absolute bottom-5 text-sm text-white/70 tabular-nums" dir="ltr">{{ index + 1 }} / {{ images.length }}</p>
                </template>
            </div>
        </Transition>
    </Teleport>
</template>
