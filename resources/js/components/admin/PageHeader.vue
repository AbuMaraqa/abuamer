<script setup>
defineProps({
    title: { type: String, required: true },
    eyebrow: { type: String, default: null },
    description: { type: String, default: null },
});
</script>

<template>
    <div class="mb-8 flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
        <div class="flex flex-col gap-1.5">
            <p v-if="eyebrow" class="eyebrow">{{ eyebrow }}</p>
            <h1 class="font-display text-3xl text-ink sm:text-4xl">{{ title }}</h1>
            <p v-if="description" class="max-w-2xl text-sm text-muted">{{ description }}</p>
        </div>
        <div v-if="$slots.actions" class="flex flex-wrap items-center gap-3">
            <slot name="actions" />
        </div>
    </div>
</template>
