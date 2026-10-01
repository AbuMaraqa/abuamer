<script setup>
defineProps({
    label: { type: String, required: true },
    description: { type: String, default: null },
});

const model = defineModel({ type: Boolean, default: false });
</script>

<template>
    <label class="flex cursor-pointer items-start justify-between gap-4">
        <span class="flex flex-col gap-0.5">
            <span class="text-sm font-medium text-ink-soft">{{ label }}</span>
            <span v-if="description" class="text-xs text-muted">{{ description }}</span>
        </span>
        <span class="relative inline-flex shrink-0">
            <input v-model="model" type="checkbox" role="switch" class="peer sr-only" />
            <span class="h-6 w-11 rounded-full bg-sand-300 transition-colors peer-checked:bg-brass-500 peer-focus-visible:outline-2 peer-focus-visible:outline-offset-2 peer-focus-visible:outline-brass-500" />
            <span class="absolute top-0.5 start-0.5 size-5 rounded-full bg-white shadow transition-transform duration-200 peer-checked:ltr:translate-x-5 peer-checked:rtl:-translate-x-5" />
        </span>
    </label>
</template>
