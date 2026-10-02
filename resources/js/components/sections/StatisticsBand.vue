<script setup>
defineProps({
    statistics: { type: Array, required: true },
    inverted: { type: Boolean, default: false },
});
</script>

<template>
    <dl class="grid grid-cols-2 gap-x-6 gap-y-10 lg:grid-cols-4">
        <div v-for="statistic in statistics" :key="statistic.id" class="flex flex-col-reverse gap-2 border-s ps-6" :class="inverted ? 'border-white/15' : 'border-line'">
            <dt class="text-sm" :class="inverted ? 'text-sand-300' : 'text-muted'">{{ statistic.title }}</dt>
            <dd class="font-display text-5xl tabular-nums sm:text-6xl" :class="inverted ? 'text-brass-300' : 'text-brass-700'" dir="ltr">{{ statistic.value }}</dd>
        </div>
    </dl>
</template>
