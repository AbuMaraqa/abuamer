<script setup>
import { ref } from 'vue';
import ImageLightbox from '../components/common/ImageLightbox.vue';
import CtaSection from '../components/sections/CtaSection.vue';
import FeatureGrid from '../components/sections/FeatureGrid.vue';
import SectionHeading from '../components/sections/SectionHeading.vue';
import StatisticsBand from '../components/sections/StatisticsBand.vue';
import { useTranslations } from '../composables/useTranslations';

defineProps({
    company: { type: Object, required: true },
    values: { type: Array, required: true },
    features: { type: Array, required: true },
    statistics: { type: Array, required: true },
});

const { t } = useTranslations();
const lightboxIndex = ref(null);
</script>

<template>
    <section class="relative overflow-hidden bg-ink text-white">
        <img v-if="company.about_image" :src="company.about_image.large" alt="" class="absolute inset-0 h-full w-full object-cover opacity-35" />
        <div class="absolute inset-0 bg-gradient-to-t from-ink to-ink/30" aria-hidden="true" />
        <div class="relative mx-auto max-w-7xl px-4 pt-28 pb-16 sm:px-6 lg:px-8 lg:pt-36 lg:pb-20">
            <p class="eyebrow text-brass-300">{{ t('About us') }}</p>
            <h1 class="mt-4 max-w-3xl font-display text-5xl leading-tight sm:text-6xl">{{ company.name }}</h1>
            <p v-if="company.tagline" class="mt-5 max-w-2xl text-lg text-white/80">{{ company.tagline }}</p>
        </div>
    </section>

    <section v-if="company.introduction || company.story" class="mx-auto grid max-w-7xl gap-12 px-4 py-24 sm:px-6 lg:grid-cols-12 lg:gap-16 lg:px-8">
        <div class="lg:col-span-5">
            <p class="eyebrow">{{ t('Our story') }}</p>
            <p v-if="company.introduction" class="mt-5 font-display text-3xl leading-snug text-ink sm:text-4xl">{{ company.introduction }}</p>
        </div>
        <div v-if="company.story" class="text-base leading-loose whitespace-pre-line text-ink-soft lg:col-span-7 lg:pt-10">{{ company.story }}</div>
    </section>

    <section v-if="company.vision || company.mission" class="border-y border-line bg-white">
        <div class="mx-auto grid max-w-7xl gap-6 px-4 py-20 sm:px-6 md:grid-cols-2 lg:px-8">
            <article v-if="company.vision" class="flex flex-col gap-4 rounded-2xl bg-sand-100 p-8 lg:p-10">
                <p class="eyebrow">{{ t('Vision') }}</p>
                <p class="font-display text-2xl leading-relaxed text-ink">{{ company.vision }}</p>
            </article>
            <article v-if="company.mission" class="flex flex-col gap-4 rounded-2xl bg-sand-100 p-8 lg:p-10">
                <p class="eyebrow">{{ t('Mission') }}</p>
                <p class="font-display text-2xl leading-relaxed text-ink">{{ company.mission }}</p>
            </article>
        </div>
    </section>

    <section v-if="values.length > 0" class="mx-auto max-w-7xl px-4 py-24 sm:px-6 lg:px-8">
        <SectionHeading :eyebrow="t('Our values')" :title="t('What guides our work')" />
        <ol class="mt-12 grid gap-x-10 gap-y-12 sm:grid-cols-2 lg:grid-cols-3">
            <li v-for="(value, index) in values" :key="value.id" class="flex flex-col gap-3 border-t border-line pt-6">
                <span class="font-display text-lg text-brass-600 tabular-nums lining-nums" dir="ltr">{{ String(index + 1).padStart(2, '0') }}</span>
                <h3 class="font-display text-2xl text-ink">{{ value.title }}</h3>
                <p v-if="value.description" class="text-sm leading-relaxed text-muted">{{ value.description }}</p>
            </li>
        </ol>
    </section>

    <section v-if="features.length > 0 || statistics.length > 0" class="bg-ink">
        <div class="mx-auto flex max-w-7xl flex-col gap-16 px-4 py-24 sm:px-6 lg:px-8">
            <template v-if="features.length > 0">
                <SectionHeading :eyebrow="t('Why choose us')" :title="t('Why :name', { name: company.name })" inverted />
                <FeatureGrid :features="features" inverted />
            </template>
            <StatisticsBand v-if="statistics.length > 0" :statistics="statistics" inverted :class="{ 'border-t border-white/10 pt-16': features.length > 0 }" />
        </div>
    </section>

    <section v-if="company.gallery.length > 0" class="mx-auto max-w-7xl px-4 py-24 sm:px-6 lg:px-8">
        <SectionHeading :eyebrow="t('Gallery')" :title="t('Projects and showroom')" />
        <div class="mt-12 columns-2 gap-4 md:columns-3 [&>*]:mb-4">
            <button
                v-for="(image, index) in company.gallery"
                :key="image.id"
                type="button"
                class="block w-full overflow-hidden rounded-xl bg-sand-200"
                :aria-label="t('Show image :number', { number: index + 1 })"
                @click="lightboxIndex = index"
            >
                <img :src="image.large" alt="" loading="lazy" class="w-full transition-transform duration-700 ease-elegant motion-safe:hover:scale-105" />
            </button>
        </div>
        <ImageLightbox v-model="lightboxIndex" :images="company.gallery" :alt="company.name" />
    </section>

    <CtaSection :title="company.cta_title" :text="company.cta_text" />
</template>
