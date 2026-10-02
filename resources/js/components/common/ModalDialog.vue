<script setup>
import { nextTick, onBeforeUnmount, ref, useId, watch } from 'vue';
import Icon from './Icon.vue';

const props = defineProps({
    show: { type: Boolean, default: false },
    title: { type: String, required: true },
    maxWidth: { type: String, default: 'md' },
});

const emit = defineEmits(['close']);

const panel = ref(null);
const titleId = useId();

const widths = {
    sm: 'sm:max-w-sm',
    md: 'sm:max-w-lg',
    lg: 'sm:max-w-2xl',
};

function onKeydown(event) {
    if (event.key === 'Escape') {
        emit('close');
    }
}

function release() {
    document.removeEventListener('keydown', onKeydown);
    document.body.style.overflow = '';
}

watch(
    () => props.show,
    async (show) => {
        if (!show) {
            release();

            return;
        }

        document.addEventListener('keydown', onKeydown);
        document.body.style.overflow = 'hidden';

        await nextTick();
        panel.value?.querySelector('[autofocus], input, select, textarea, button:not([data-dialog-close])')?.focus();
    },
);

onBeforeUnmount(release);
</script>

<template>
    <Teleport to="body">
        <Transition
            enter-active-class="transition duration-200 ease-elegant"
            enter-from-class="opacity-0"
            leave-active-class="transition duration-150"
            leave-to-class="opacity-0"
        >
            <div v-if="show" class="fixed inset-0 z-50 flex items-end justify-center p-0 sm:items-center sm:p-6">
                <div class="absolute inset-0 bg-ink/50 backdrop-blur-[2px]" aria-hidden="true" @click="emit('close')" />

                <div
                    ref="panel"
                    role="dialog"
                    aria-modal="true"
                    :aria-labelledby="titleId"
                    class="relative flex max-h-[90svh] w-full flex-col rounded-t-2xl bg-white shadow-2xl shadow-ink/20 sm:rounded-2xl"
                    :class="widths[maxWidth]"
                >
                    <header class="flex items-center justify-between gap-4 border-b border-line px-6 py-4">
                        <h2 :id="titleId" class="font-display text-xl text-ink">{{ title }}</h2>
                        <button type="button" data-dialog-close class="text-muted transition-colors hover:text-ink" :aria-label="$t('Close')" @click="emit('close')">
                            <Icon name="x" />
                        </button>
                    </header>

                    <div class="overflow-y-auto px-6 py-5">
                        <slot />
                    </div>

                    <footer v-if="$slots.footer" class="flex flex-col-reverse gap-3 border-t border-line px-6 py-4 sm:flex-row sm:justify-end">
                        <slot name="footer" />
                    </footer>
                </div>
            </div>
        </Transition>
    </Teleport>
</template>
