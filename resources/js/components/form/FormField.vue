<script setup>
defineProps({
    label: { type: String, default: null },
    for: { type: String, default: null },
    error: { type: String, default: null },
    hint: { type: String, default: null },
    required: { type: Boolean, default: false },
});
</script>

<template>
    <div class="flex flex-col gap-1.5">
        <label v-if="label" :for="$props.for" class="text-sm font-medium text-ink-soft">
            {{ label }}
            <span v-if="required" class="text-danger" aria-hidden="true">*</span>
        </label>
        <slot />
        <p v-if="error" class="text-sm text-danger" role="alert">{{ error }}</p>
        <p v-else-if="hint" class="text-xs text-muted">{{ hint }}</p>
    </div>
</template>
