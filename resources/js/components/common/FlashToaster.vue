<script setup>
import { router } from '@inertiajs/vue3';
import { onBeforeUnmount, ref } from 'vue';
import Icon from './Icon.vue';

/**
 * Shows `Inertia::flash('toast', ['type' => ..., 'message' => ...])` messages.
 */
const toasts = ref([]);
let nextId = 0;

function dismiss(id) {
    toasts.value = toasts.value.filter((toast) => toast.id !== id);
}

function show(toast) {
    const id = ++nextId;
    toasts.value.push({ id, type: toast.type ?? 'success', message: toast.message });
    setTimeout(() => dismiss(id), 5000);
}

const removeListener = router.on('flash', (event) => {
    if (event.detail.flash?.toast) {
        show(event.detail.flash.toast);
    }
});

onBeforeUnmount(removeListener);
</script>

<template>
    <div class="pointer-events-none fixed inset-x-4 bottom-4 z-50 flex flex-col items-center gap-2 sm:inset-x-auto sm:end-6 sm:items-end">
        <TransitionGroup
            enter-active-class="transition duration-300 ease-elegant"
            enter-from-class="translate-y-2 opacity-0"
            leave-active-class="transition duration-200"
            leave-to-class="opacity-0"
        >
            <div
                v-for="toast in toasts"
                :key="toast.id"
                role="status"
                class="pointer-events-auto flex w-full max-w-sm items-start gap-3 rounded-lg border bg-white px-4 py-3 text-sm shadow-lg shadow-ink/5"
                :class="toast.type === 'error' ? 'border-danger/30 text-danger' : 'border-line text-ink'"
            >
                <Icon :name="toast.type === 'error' ? 'alert' : 'check'" :size="18" class="mt-0.5 shrink-0" :class="toast.type === 'error' ? '' : 'text-success'" />
                <p class="grow">{{ toast.message }}</p>
                <button type="button" class="shrink-0 text-muted hover:text-ink" :aria-label="$t('Close')" @click="dismiss(toast.id)">
                    <Icon name="x" :size="16" />
                </button>
            </div>
        </TransitionGroup>
    </div>
</template>
