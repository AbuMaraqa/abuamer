<script setup>
defineProps({
    eyebrow: { type: String, default: null },
    title: { type: String, required: true },
    description: { type: String, default: null },
    inverted: { type: Boolean, default: false },
});
</script>

<template>
    <div class="flex flex-col gap-4 sm:gap-6 md:flex-row md:items-end md:justify-between">
        <div class="max-w-2xl">
            <p v-if="eyebrow" class="eyebrow" :class="{ 'text-brass-300': inverted }">{{ eyebrow }}</p>
            <h2 class="mt-3 font-display text-3xl leading-tight text-balance sm:text-5xl" :class="inverted ? 'text-white' : 'text-ink'">{{ title }}</h2>
            <p v-if="description" class="mt-4 text-base leading-relaxed" :class="inverted ? 'text-sand-300' : 'text-muted'">{{ description }}</p>
        </div>
        <div v-if="$slots.default" class="shrink-0">
            <slot />
        </div>
    </div>
</template>
