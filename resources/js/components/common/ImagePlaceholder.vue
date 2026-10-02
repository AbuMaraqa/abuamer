<script setup>
import Icon from './Icon.vue';
</script>

<template>
    <!-- A quiet tile-grid pattern shown where a product or category has no image yet. -->
    <div
        class="flex h-full w-full items-center justify-center bg-sand-100 bg-[linear-gradient(to_right,var(--color-sand-200)_1px,transparent_1px),linear-gradient(to_bottom,var(--color-sand-200)_1px,transparent_1px)] bg-[size:2.5rem_2.5rem]"
        aria-hidden="true"
    >
        <!-- Scales with the frame: small in admin thumbnails, larger on catalog cards. -->
        <Icon name="image" :stroke-width="1.25" class="h-auto w-1/5 max-w-12 min-w-5 text-sand-300" />
    </div>
</template>
