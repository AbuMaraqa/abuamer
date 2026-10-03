<script setup>
/**
 * A brand's logo, or its name set in the display face when no logo was uploaded yet.
 */
defineProps({
    brand: { type: Object, required: true },
    // Tailwind height class for the logo image; the name scales with the text size instead.
    imageClass: { type: String, default: 'h-10' },
});
</script>

<template>
    <img v-if="brand.logo" :src="brand.logo.thumb" :alt="brand.name" loading="lazy" class="w-auto max-w-full object-contain" :class="imageClass" />
    <span v-else class="font-display leading-none tracking-wide text-ink" dir="auto">{{ brand.name }}</span>
</template>
