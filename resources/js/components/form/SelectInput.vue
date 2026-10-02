<script setup>
import Icon from '../common/Icon.vue';

defineOptions({ inheritAttrs: false });

defineProps({
    options: { type: Array, required: true },
});

const model = defineModel({ type: [String, Number, null], default: null });
</script>

<template>
    <div class="relative">
        <select
            v-model="model"
            v-bind="$attrs"
            class="w-full appearance-none rounded-lg border border-line bg-white py-2.5 ps-3.5 pe-10 text-sm text-ink focus:border-brass-500 focus:ring-2 focus:ring-brass-500/20 focus:outline-none"
        >
            <option v-for="option in options" :key="String(option.value)" :value="option.value">{{ option.label }}</option>
        </select>
        <Icon name="chevron-down" :size="16" class="pointer-events-none absolute end-3 top-1/2 -translate-y-1/2 text-muted" />
    </div>
</template>
