<script setup>
import { Link } from '@inertiajs/vue3';
import Icon from './Icon.vue';

defineProps({
    icon: { type: String, default: null },
    href: { type: String, default: null },
    external: { type: Boolean, default: false },
    danger: { type: Boolean, default: false },
});

const emit = defineEmits(['select']);
</script>

<template>
    <component
        :is="href ? (external ? 'a' : Link) : 'button'"
        :href="href ?? undefined"
        :type="href ? undefined : 'button'"
        :target="external ? '_blank' : undefined"
        :rel="external ? 'noopener' : undefined"
        role="menuitem"
        class="flex w-full items-center gap-2.5 px-3.5 py-2 text-start text-sm transition-colors"
        :class="danger ? 'text-danger hover:bg-danger/5' : 'text-ink-soft hover:bg-sand-100 hover:text-ink'"
        @click="emit('select')"
    >
        <Icon v-if="icon" :name="icon" :size="16" class="shrink-0" />
        <slot />
    </component>
</template>
