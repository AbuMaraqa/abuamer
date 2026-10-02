<script setup>
import CategoryCard from './CategoryCard.vue';

/**
 * Collection cards. On phones they form a swipeable row in which the next card peeks in,
 * so a few tall cards do not push the products far down the page; from the small
 * breakpoint up they form a grid.
 */
defineProps({
    categories: { type: Array, required: true },
});
</script>

<template>
    <div
        class="-mx-4 flex snap-x snap-mandatory scroll-px-4 gap-3 overflow-x-auto px-4 pb-1 [scrollbar-width:none] sm:mx-0 sm:grid sm:snap-none sm:grid-cols-2 sm:gap-4 sm:overflow-visible sm:px-0 sm:pb-0 lg:grid-cols-3 lg:gap-6 [&::-webkit-scrollbar]:hidden"
    >
        <CategoryCard
            v-for="category in categories"
            :key="category.id"
            :category="category"
            class="shrink-0 snap-start sm:w-auto"
            :class="categories.length > 1 ? 'w-[82%]' : 'w-full'"
        />
    </div>
</template>
