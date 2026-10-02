<script setup>
import { Link } from '@inertiajs/vue3';
import { route } from 'ziggy-js';
import Icon from '../common/Icon.vue';

/**
 * Desktop product navigation: one column per collection with its sub-collections.
 */
defineProps({
    collections: { type: Array, required: true },
});

const emit = defineEmits(['navigate']);

const MAX_CHILDREN = 6;
</script>

<template>
    <div class="border-t border-line bg-white shadow-2xl shadow-ink/10">
        <div class="mx-auto grid max-w-7xl grid-cols-[repeat(auto-fill,minmax(12rem,1fr))] gap-x-10 gap-y-8 px-8 py-10">
            <div v-for="collection in collections" :key="collection.id" class="flex flex-col gap-3">
                <Link :href="collection.url" class="font-display text-xl text-ink transition-colors hover:text-brass-700" @click="emit('navigate')">
                    {{ collection.name }}
                </Link>
                <ul v-if="collection.children.length > 0" class="flex flex-col gap-2">
                    <li v-for="child in collection.children.slice(0, MAX_CHILDREN)" :key="child.id">
                        <Link :href="child.url" class="text-sm text-muted transition-colors hover:text-ink" @click="emit('navigate')">{{ child.name }}</Link>
                    </li>
                    <li v-if="collection.children.length > MAX_CHILDREN">
                        <Link :href="collection.url" class="text-sm text-brass-700 hover:underline" @click="emit('navigate')">{{ $t('View all') }}</Link>
                    </li>
                </ul>
            </div>

            <Link
                :href="route('products.index')"
                class="group flex flex-col justify-between gap-6 self-start rounded-2xl bg-sand-100 p-6 transition-colors hover:bg-sand-200"
                @click="emit('navigate')"
            >
                <span class="eyebrow">{{ $t('Catalog') }}</span>
                <span class="flex items-center justify-between gap-3 font-display text-xl text-ink">
                    {{ $t('All products') }}
                    <Icon name="arrow-right" :size="18" class="transition-transform group-hover:translate-x-1 rtl:group-hover:-translate-x-1" />
                </span>
            </Link>
        </div>
    </div>
</template>
