<script setup>
import { onBeforeUnmount, ref, watch } from 'vue';

defineProps({
    label: { type: String, required: true },
});

const isOpen = ref(false);
const root = ref(null);

function close() {
    isOpen.value = false;
}

function onDocumentClick(event) {
    if (!root.value?.contains(event.target)) {
        close();
    }
}

function onKeydown(event) {
    if (event.key === 'Escape') {
        close();
    }
}

// Listeners exist only while the menu is open, so a long list of menus stays cheap.
watch(isOpen, (open) => {
    const method = open ? 'addEventListener' : 'removeEventListener';
    document[method]('click', onDocumentClick, true);
    document[method]('keydown', onKeydown);
});

onBeforeUnmount(close);
</script>

<template>
    <div ref="root" class="relative">
        <button
            type="button"
            class="flex size-8 items-center justify-center rounded-lg text-muted transition-colors hover:bg-sand-200 hover:text-ink"
            aria-haspopup="menu"
            :aria-expanded="isOpen"
            :aria-label="label"
            @click="isOpen = !isOpen"
        >
            <slot name="trigger" />
        </button>

        <Transition
            enter-active-class="transition duration-150 ease-elegant"
            enter-from-class="-translate-y-1 opacity-0"
            leave-active-class="transition duration-100"
            leave-to-class="opacity-0"
        >
            <div
                v-if="isOpen"
                role="menu"
                class="absolute end-0 top-full z-30 mt-1 w-56 rounded-xl border border-line bg-white py-1.5 shadow-xl shadow-ink/10"
                @click="close"
            >
                <slot />
            </div>
        </Transition>
    </div>
</template>
