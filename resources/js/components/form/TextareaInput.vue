<script setup>
defineOptions({ inheritAttrs: false });

defineProps({
    invalid: { type: Boolean, default: false },
});

const model = defineModel({ type: [String, null], default: '' });
</script>

<template>
    <textarea
        v-model="model"
        v-bind="$attrs"
        class="w-full rounded-lg border bg-white px-3.5 py-2.5 text-sm leading-relaxed text-ink placeholder:text-sand-400 transition-colors focus:border-brass-500 focus:ring-2 focus:ring-brass-500/20 focus:outline-none"
        :class="invalid ? 'border-danger/60' : 'border-line'"
        :aria-invalid="invalid || undefined"
    />
</template>
