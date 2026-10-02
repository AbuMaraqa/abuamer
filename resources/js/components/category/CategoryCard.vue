<script setup>
import { Link } from '@inertiajs/vue3';
import Icon from '../common/Icon.vue';
import ImagePlaceholder from '../common/ImagePlaceholder.vue';

defineProps({
    category: { type: Object, required: true },
});
</script>

<template>
    <Link :href="category.url" class="group relative block overflow-hidden rounded-2xl bg-sand-200">
        <div class="aspect-[4/5] sm:aspect-[4/3]">
            <img
                v-if="category.image"
                :src="category.image.large"
                alt=""
                loading="lazy"
                class="h-full w-full object-cover transition-transform duration-700 ease-elegant motion-safe:group-hover:scale-105"
            />
            <ImagePlaceholder v-else />
        </div>
        <div class="absolute inset-0 bg-gradient-to-t from-ink/75 via-ink/10 to-transparent" aria-hidden="true" />
        <div class="absolute inset-x-0 bottom-0 flex items-end justify-between gap-4 p-5 sm:p-6">
            <h3 class="font-display text-2xl text-white sm:text-3xl">{{ category.name }}</h3>
            <span class="flex size-10 shrink-0 items-center justify-center rounded-full border border-white/40 text-white transition-colors group-hover:bg-white group-hover:text-ink">
                <Icon name="arrow-right" :size="18" />
            </span>
        </div>
    </Link>
</template>
