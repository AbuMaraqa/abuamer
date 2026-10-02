<script setup>
import { Link, usePage } from '@inertiajs/vue3';
import { computed, nextTick, onBeforeUnmount, onMounted, ref } from 'vue';
import { useTranslations } from '../../composables/useTranslations';
import Icon from '../common/Icon.vue';

/**
 * Full-screen home page slider: crossfading slides with a slow zoom, staggered text,
 * progress indicators, swipe and keyboard navigation (aware of the reading direction).
 *
 * Autoplay pauses while hovered, while a control has keyboard focus, while the tab is
 * hidden, when the visitor presses pause, and is off for visitors who prefer reduced motion.
 */
const props = defineProps({
    slides: { type: Array, required: true },
});

const DURATION = 7000;
const FADE = 1200;

const { t } = useTranslations();
const isRtl = usePage().props.locale.direction === 'rtl';

const current = ref(0);
const leaving = ref(null);
const progress = ref(0);
const isReady = ref(false);
const isPausedByUser = ref(false);
const isHovered = ref(false);
const hasKeyboardFocus = ref(false);
const isTabHidden = ref(false);
const prefersReducedMotion = ref(false);

const count = computed(() => props.slides.length);
const isPlaying = computed(
    () => count.value > 1 && !isPausedByUser.value && !isHovered.value && !hasKeyboardFocus.value && !isTabHidden.value && !prefersReducedMotion.value,
);

let frame = null;
let lastTime = null;
let leavingTimer = null;
let pointerStartX = null;

function go(index) {
    if (index === current.value) {
        return;
    }

    clearTimeout(leavingTimer);
    leaving.value = current.value;
    current.value = (index + count.value) % count.value;
    progress.value = 0;
    leavingTimer = setTimeout(() => (leaving.value = null), FADE);
}

const next = () => go((current.value + 1) % count.value);
const previous = () => go((current.value - 1 + count.value) % count.value);

function tick(time) {
    if (lastTime !== null && isPlaying.value) {
        progress.value = Math.min(1, progress.value + (time - lastTime) / DURATION);

        if (progress.value >= 1) {
            next();
        }
    }

    lastTime = time;
    frame = requestAnimationFrame(tick);
}

/** Swipe on touch screens: towards the reading direction goes forward. */
function onPointerDown(event) {
    if (event.pointerType !== 'mouse') {
        pointerStartX = event.clientX;
    }
}

function onPointerUp(event) {
    if (pointerStartX === null) {
        return;
    }

    const delta = event.clientX - pointerStartX;
    pointerStartX = null;

    if (Math.abs(delta) > 50) {
        (isRtl ? delta > 0 : delta < 0) ? next() : previous();
    }
}

function onKeydown(event) {
    if (event.key !== 'ArrowLeft' && event.key !== 'ArrowRight') {
        return;
    }

    event.preventDefault();
    (event.key === 'ArrowRight') !== isRtl ? next() : previous();
}

function onFocusIn(event) {
    hasKeyboardFocus.value = event.target.matches(':focus-visible');
}

function onVisibilityChange() {
    isTabHidden.value = document.hidden;
}

function indicatorWidth(index) {
    if (index < current.value) {
        return '100%';
    }

    return index === current.value ? `${(prefersReducedMotion.value ? 1 : progress.value) * 100}%` : '0%';
}

function isInternal(url) {
    return url.startsWith('/') || url.startsWith(window.location.origin);
}

const pad = (number) => String(number).padStart(2, '0');

onMounted(async () => {
    prefersReducedMotion.value = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    document.addEventListener('visibilitychange', onVisibilityChange);
    frame = requestAnimationFrame(tick);

    // Let the first slide render hidden once, so its text animates in like the others.
    await nextTick();
    requestAnimationFrame(() => (isReady.value = true));
});

onBeforeUnmount(() => {
    cancelAnimationFrame(frame);
    clearTimeout(leavingTimer);
    document.removeEventListener('visibilitychange', onVisibilityChange);
});
</script>

<template>
    <section
        class="relative isolate h-svh min-h-[36rem] touch-pan-y overflow-hidden bg-ink text-white select-none"
        aria-roledescription="carousel"
        :aria-label="t('Featured collections')"
        @pointerenter="$event.pointerType === 'mouse' && (isHovered = true)"
        @pointerleave="isHovered = false"
        @pointerdown="onPointerDown"
        @pointerup="onPointerUp"
        @focusin="onFocusIn"
        @focusout="hasKeyboardFocus = false"
        @keydown="onKeydown"
    >
        <div
            v-for="(slide, index) in slides"
            :id="`slide-${slide.id}`"
            :key="slide.id"
            role="group"
            aria-roledescription="slide"
            :aria-label="t('Slide :number of :total', { number: index + 1, total: count })"
            :aria-hidden="index !== current"
            :inert="index !== current"
            class="absolute inset-0 transition-opacity ease-elegant motion-reduce:duration-300"
            :class="[index === current ? 'z-10 opacity-100' : 'z-0 opacity-0', { 'is-current': index === current && isReady }]"
            :style="{ transitionDuration: `${FADE}ms` }"
        >
            <picture v-if="slide.image">
                <source v-if="slide.mobileImage" media="(max-width: 767px)" :srcset="slide.mobileImage.large" />
                <img
                    :src="slide.image.large"
                    alt=""
                    class="slide-image absolute inset-0 h-full w-full object-cover"
                    :class="{ 'is-animated': index === current || index === leaving }"
                    :loading="index === 0 ? 'eager' : 'lazy'"
                    :fetchpriority="index === 0 ? 'high' : 'auto'"
                    decoding="async"
                    draggable="false"
                />
            </picture>
            <div
                v-else
                class="absolute inset-0 bg-[linear-gradient(to_right,rgb(255_255_255/0.06)_1px,transparent_1px),linear-gradient(to_bottom,rgb(255_255_255/0.06)_1px,transparent_1px)] bg-[size:6rem_6rem]"
                aria-hidden="true"
            />

            <div class="absolute inset-0 bg-gradient-to-t from-ink/90 via-ink/25 to-ink/45" aria-hidden="true" />
            <div class="absolute inset-0 from-ink/55 via-ink/10 to-transparent ltr:bg-gradient-to-r rtl:bg-gradient-to-l" aria-hidden="true" />

            <div class="relative mx-auto flex h-full max-w-7xl flex-col justify-end px-4 pb-36 sm:px-6 lg:px-8 lg:pb-44">
                <div class="max-w-3xl">
                    <p v-if="slide.eyebrow" class="slide-reveal eyebrow flex items-center gap-3 text-brass-300" style="--reveal-delay: 150ms">
                        <span class="h-px w-10 bg-brass-300/80" aria-hidden="true" />
                        {{ slide.eyebrow }}
                    </p>
                    <component
                        :is="index === 0 ? 'h1' : 'h2'"
                        class="slide-reveal mt-5 font-display text-4xl leading-[1.08] text-balance sm:text-6xl lg:text-7xl"
                        style="--reveal-delay: 300ms"
                    >
                        {{ slide.title }}
                    </component>
                    <p v-if="slide.text" class="slide-reveal mt-6 max-w-xl text-base leading-relaxed text-white/80 sm:text-lg" style="--reveal-delay: 450ms">
                        {{ slide.text }}
                    </p>
                    <div v-if="slide.button" class="slide-reveal mt-9" style="--reveal-delay: 600ms">
                        <component
                            :is="isInternal(slide.button.url) ? Link : 'a'"
                            :href="slide.button.url"
                            :target="isInternal(slide.button.url) ? undefined : '_blank'"
                            :rel="isInternal(slide.button.url) ? undefined : 'noopener'"
                            class="group inline-flex items-center gap-3 rounded-lg bg-white px-7 py-4 text-sm font-medium text-ink transition-colors hover:bg-sand-100"
                        >
                            {{ slide.button.label }}
                            <Icon name="arrow-right" :size="18" class="transition-transform group-hover:translate-x-1 rtl:group-hover:-translate-x-1" />
                        </component>
                    </div>
                </div>
            </div>
        </div>

        <!-- Controls -->
        <!-- Below the desktop width the row keeps clear of the floating WhatsApp button in its end corner. -->
        <div v-if="count > 1" class="absolute inset-x-0 bottom-0 z-20">
            <div
                class="mx-auto flex max-w-7xl items-center gap-4 px-4 pb-8 sm:gap-6 sm:px-6 lg:px-8 lg:pb-10"
                :class="{ 'max-sm:pe-20 sm:max-lg:pe-24': $page.props.site.contact.whatsapp }"
            >
                <p class="shrink-0 font-display text-lg tabular-nums lining-nums" dir="ltr" aria-live="polite">
                    <span class="text-white">{{ pad(current + 1) }}</span>
                    <span class="text-white/40"> / {{ pad(count) }}</span>
                </p>

                <div class="flex grow items-center gap-2">
                    <button
                        v-for="(slide, index) in slides"
                        :key="slide.id"
                        type="button"
                        class="relative h-8 grow cursor-pointer"
                        :aria-label="t('Go to slide :number', { number: index + 1 })"
                        :aria-current="index === current ? 'true' : undefined"
                        :aria-controls="`slide-${slide.id}`"
                        @click="go(index)"
                    >
                        <span class="absolute inset-x-0 top-1/2 h-px -translate-y-1/2 bg-white/25">
                            <span class="absolute inset-y-0 start-0 bg-brass-300" :style="{ width: indicatorWidth(index) }" />
                        </span>
                    </button>
                </div>

                <div class="flex shrink-0 items-center gap-2">
                    <button
                        type="button"
                        class="flex size-11 items-center justify-center rounded-full text-white/80 transition-colors hover:text-white"
                        :aria-label="isPausedByUser ? t('Play slideshow') : t('Pause slideshow')"
                        @click="isPausedByUser = !isPausedByUser"
                    >
                        <Icon :name="isPausedByUser ? 'play' : 'pause'" :size="16" />
                    </button>
                    <!-- Phones swipe or tap the progress bars instead of these arrows. -->
                    <button
                        type="button"
                        class="hidden size-12 items-center justify-center rounded-full border border-white/30 transition-colors hover:border-white hover:bg-white hover:text-ink sm:flex"
                        :aria-label="t('Previous slide')"
                        @click="previous"
                    >
                        <Icon name="chevron-right" :size="20" class="rotate-180" />
                    </button>
                    <button
                        type="button"
                        class="hidden size-12 items-center justify-center rounded-full border border-white/30 transition-colors hover:border-white hover:bg-white hover:text-ink sm:flex"
                        :aria-label="t('Next slide')"
                        @click="next"
                    >
                        <Icon name="chevron-right" :size="20" />
                    </button>
                </div>
            </div>
        </div>
    </section>
</template>

<style scoped>
@keyframes slide-zoom {
    from {
        transform: scale(1.12);
    }

    to {
        transform: scale(1);
    }
}

.slide-image.is-animated {
    animation: slide-zoom 9s cubic-bezier(0.22, 1, 0.36, 1) forwards;
}

.slide-reveal {
    opacity: 0;
    transform: translateY(1.5rem);
    transition:
        opacity 0.9s cubic-bezier(0.22, 1, 0.36, 1) var(--reveal-delay, 0ms),
        transform 0.9s cubic-bezier(0.22, 1, 0.36, 1) var(--reveal-delay, 0ms);
}

.is-current .slide-reveal {
    opacity: 1;
    transform: none;
}

@media (prefers-reduced-motion: reduce) {
    .slide-image.is-animated {
        animation: none;
    }

    .slide-reveal {
        transform: none;
        transition: opacity 0.3s;
    }
}
</style>
