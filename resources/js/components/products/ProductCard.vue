<script setup>
import { Link } from '@inertiajs/vue3';
import ImagePlaceholder from '../common/ImagePlaceholder.vue';

defineProps({
    product: { type: Object, required: true },
});
</script>

<template>
    <Link :href="product.url" class="group flex flex-col gap-4">
        <div class="relative aspect-square overflow-hidden rounded-2xl bg-sand-100">
            <img
                v-if="product.image"
                :src="product.image.thumb"
                alt=""
                loading="lazy"
                class="h-full w-full object-cover transition-transform duration-700 ease-elegant motion-safe:group-hover:scale-105"
            />
            <ImagePlaceholder v-else />
            <span v-if="product.featured" class="absolute start-3 top-3 rounded-full bg-white/90 px-3 py-1 text-[11px] font-medium text-brass-700 backdrop-blur">
                {{ $t('Featured') }}
            </span>
        </div>
        <div class="flex flex-col gap-1">
            <p v-if="product.category" class="eyebrow">{{ product.category.name }}</p>
            <h3 class="font-display text-xl leading-snug text-ink transition-colors group-hover:text-brass-700">{{ product.name }}</h3>
            <p v-if="product.sku" class="text-xs text-muted" dir="ltr">{{ product.sku }}</p>
        </div>
    </Link>
</template>
