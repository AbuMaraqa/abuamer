<script setup>
import Icon from '../common/Icon.vue';

/**
 * "Why choose us" reasons with icons, managed from the company editor.
 */
defineProps({
    features: { type: Array, required: true },
    inverted: { type: Boolean, default: false },
});
</script>

<template>
    <div class="grid gap-px overflow-hidden rounded-2xl sm:grid-cols-2 lg:grid-cols-3" :class="inverted ? 'bg-white/10' : 'bg-line'">
        <div v-for="feature in features" :key="feature.id" class="flex flex-col gap-4 p-8" :class="inverted ? 'bg-ink' : 'bg-white'">
            <span class="flex size-12 items-center justify-center rounded-full" :class="inverted ? 'bg-brass-400/15 text-brass-300' : 'bg-brass-300/20 text-brass-700'">
                <Icon :name="feature.icon ?? 'gem'" :size="22" />
            </span>
            <h3 class="font-display text-2xl" :class="inverted ? 'text-white' : 'text-ink'">{{ feature.title }}</h3>
            <p v-if="feature.description" class="text-sm leading-relaxed" :class="inverted ? 'text-sand-300' : 'text-muted'">{{ feature.description }}</p>
        </div>
    </div>
</template>
