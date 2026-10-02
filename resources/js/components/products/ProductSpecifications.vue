<script setup>
defineProps({
    specifications: { type: Array, required: true },
});
</script>

<template>
    <dl v-if="specifications.length > 0" class="divide-y divide-line border-y border-line">
        <div v-for="specification in specifications" :key="specification.id" class="grid grid-cols-[minmax(0,2fr)_minmax(0,3fr)] gap-4 py-3.5 text-sm">
            <dt class="text-muted">{{ specification.label }}</dt>
            <dd class="text-ink">{{ specification.value }}</dd>
        </div>
    </dl>
</template>
