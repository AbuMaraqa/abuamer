<script setup>
import { Link } from '@inertiajs/vue3';
import Icon from '../common/Icon.vue';

/**
 * The main product lines (e.g. "Tiles & Marble" and "Sanitary Ware") as large panels,
 * each with links to its collections.
 */
defineProps({
    departments: { type: Array, required: true },
});

const MAX_COLLECTIONS = 6;
</script>

<template>
    <div class="grid gap-4 sm:gap-6" :class="departments.length === 2 ? 'lg:grid-cols-2' : 'md:grid-cols-2 xl:grid-cols-3'">
        <article v-for="(department, index) in departments" :key="department.id" class="group relative isolate flex min-h-[30rem] overflow-hidden rounded-3xl bg-ink text-white sm:min-h-[34rem]">
            <img
                v-if="department.image"
                :src="department.image.large"
                alt=""
                loading="lazy"
                class="absolute inset-0 -z-20 h-full w-full object-cover transition-transform duration-[1.2s] ease-elegant motion-safe:group-hover:scale-105"
            />
            <div
                v-else
                class="absolute inset-0 -z-20 bg-[linear-gradient(to_right,rgb(255_255_255/0.06)_1px,transparent_1px),linear-gradient(to_bottom,rgb(255_255_255/0.06)_1px,transparent_1px)] bg-[size:4rem_4rem]"
                aria-hidden="true"
            />
            <div class="absolute inset-0 -z-10 bg-gradient-to-t from-ink via-ink/55 to-ink/5" aria-hidden="true" />

            <div class="mt-auto flex w-full flex-col gap-5 p-6 sm:p-8 lg:p-10">
                <span class="font-display text-sm text-brass-300 tabular-nums lining-nums" dir="ltr">{{ String(index + 1).padStart(2, '0') }}</span>
                <h3 class="font-display text-3xl leading-tight text-balance sm:text-5xl">
                    <!-- The whole panel is clickable through this link; the collection links sit above it. -->
                    <Link
                        :href="department.url"
                        class="after:absolute after:inset-0 after:rounded-3xl focus-visible:outline-none focus-visible:after:outline-2 focus-visible:after:-outline-offset-4 focus-visible:after:outline-brass-300"
                    >
                        {{ department.name }}
                    </Link>
                </h3>

                <ul v-if="department.children.length > 0" class="relative z-10 flex flex-wrap gap-2">
                    <li v-for="collection in department.children.slice(0, MAX_COLLECTIONS)" :key="collection.id">
                        <Link :href="collection.url" class="inline-block rounded-full border border-white/25 bg-white/10 px-3.5 py-1.5 text-xs text-white backdrop-blur-sm transition-colors hover:border-white hover:bg-white hover:text-ink sm:text-sm">
                            {{ collection.name }}
                        </Link>
                    </li>
                    <li v-if="department.children.length > MAX_COLLECTIONS" class="flex items-center px-1.5 text-xs text-white/70 sm:text-sm">
                        {{ $t('+:count more', { count: department.children.length - MAX_COLLECTIONS }) }}
                    </li>
                </ul>

                <span class="inline-flex items-center gap-2 text-sm font-medium text-white">
                    <span class="border-b border-brass-300 pb-1">{{ $t('Explore the department') }}</span>
                    <Icon name="arrow-right" :size="16" class="transition-transform group-hover:translate-x-1 rtl:group-hover:-translate-x-1" />
                </span>
            </div>
        </article>
    </div>
</template>
