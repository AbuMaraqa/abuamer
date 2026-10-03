<script setup>
import { Link } from '@inertiajs/vue3';
import { route } from 'ziggy-js';
import Icon from '../common/Icon.vue';

/**
 * Desktop product navigation: one column per collection (or per department, such as
 * "Sanitary Ware") with its sub-collections. Long lists flow into two columns.
 */
defineProps({
    collections: { type: Array, required: true },
});

const emit = defineEmits(['navigate']);

const MAX_CHILDREN = 12;
const WIDE_AFTER = 6;
</script>

<template>
    <div class="border-t border-line bg-white shadow-2xl shadow-ink/10">
        <div class="mx-auto grid max-w-7xl grid-cols-4 gap-x-10 gap-y-8 px-8 py-10">
            <div
                v-for="collection in collections"
                :key="collection.id"
                class="flex flex-col gap-3"
                :class="{ 'col-span-2': collection.children.length > WIDE_AFTER }"
            >
                <Link :href="collection.url" class="group inline-flex items-center gap-2 self-start font-display text-xl text-ink transition-colors hover:text-brass-700" @click="emit('navigate')">
                    {{ collection.name }}
                    <Icon name="arrow-right" :size="16" class="text-brass-500 opacity-0 transition group-hover:opacity-100" />
                </Link>
                <ul
                    v-if="collection.children.length > 0"
                    class="flex flex-col gap-2 border-t border-line pt-3"
                    :class="{ 'grid grid-cols-2 gap-x-8': collection.children.length > WIDE_AFTER }"
                >
                    <li v-for="child in collection.children.slice(0, MAX_CHILDREN)" :key="child.id">
                        <Link :href="child.url" class="text-sm text-muted transition-colors hover:text-ink" @click="emit('navigate')">{{ child.name }}</Link>
                    </li>
                    <li v-if="collection.children.length > MAX_CHILDREN">
                        <Link :href="collection.url" class="text-sm text-brass-700 hover:underline" @click="emit('navigate')">{{ $t('View all') }}</Link>
                    </li>
                </ul>
            </div>

            <div class="flex flex-col gap-3 self-start">
                <Link
                    :href="route('products.index')"
                    class="group flex flex-col justify-between gap-6 rounded-2xl bg-sand-100 p-6 transition-colors hover:bg-sand-200"
                    @click="emit('navigate')"
                >
                    <span class="eyebrow">{{ $t('Catalog') }}</span>
                    <span class="flex items-center justify-between gap-3 font-display text-xl text-ink">
                        {{ $t('All products') }}
                        <Icon name="arrow-right" :size="18" class="transition-transform group-hover:translate-x-1 rtl:group-hover:-translate-x-1" />
                    </span>
                </Link>
                <Link
                    v-if="$page.props.site.hasBrands"
                    :href="route('brands.index')"
                    class="group flex items-center justify-between gap-3 rounded-2xl border border-line px-6 py-4 text-sm font-medium text-ink transition-colors hover:border-sand-400"
                    @click="emit('navigate')"
                >
                    <span class="inline-flex items-center gap-2">
                        <Icon name="tag" :size="16" class="text-brass-600" />
                        {{ $t('Shop by brand') }}
                    </span>
                    <Icon name="arrow-right" :size="16" class="transition-transform group-hover:translate-x-1 rtl:group-hover:-translate-x-1" />
                </Link>
            </div>
        </div>
    </div>
</template>
