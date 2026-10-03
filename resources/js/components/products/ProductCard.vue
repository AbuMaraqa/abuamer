<script setup>
import { Link } from '@inertiajs/vue3';
import ImagePlaceholder from '../common/ImagePlaceholder.vue';

defineProps({
    product: { type: Object, required: true },
});
</script>

<template>
    <Link :href="product.url" class="group flex flex-col gap-3 sm:gap-4">
        <div class="relative aspect-square overflow-hidden rounded-xl bg-sand-100 sm:rounded-2xl">
            <img
                v-if="product.image"
                :src="product.image.thumb"
                alt=""
                loading="lazy"
                class="h-full w-full object-cover transition-transform duration-700 ease-elegant motion-safe:group-hover:scale-105"
            />
            <ImagePlaceholder v-else />
            <span v-if="product.featured" class="absolute start-2.5 top-2.5 rounded-full bg-white/90 px-2.5 py-1 text-[11px] font-medium text-brass-700 backdrop-blur sm:start-3 sm:top-3 sm:px-3">
                {{ $t('Featured') }}
            </span>
        </div>
        <div class="flex flex-col gap-1">
            <p v-if="product.category" class="eyebrow">{{ product.category.name }}</p>
            <h3 class="font-display text-lg leading-snug text-ink transition-colors group-hover:text-brass-700 sm:text-xl">{{ product.name }}</h3>
            <!-- The brand and code are isolated from the text direction, so they still align with the name in right-to-left pages. -->
            <p v-if="product.brand || product.sku" class="flex min-w-0 items-center gap-1.5 text-xs text-muted">
                <bdi v-if="product.brand" class="truncate font-medium text-ink-soft">{{ product.brand.name }}</bdi>
                <span v-if="product.brand && product.sku" class="text-sand-400" aria-hidden="true">·</span>
                <span v-if="product.sku" class="truncate" dir="ltr">{{ product.sku }}</span>
            </p>
        </div>
    </Link>
</template>
