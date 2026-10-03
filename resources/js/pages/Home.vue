<script setup>
import { Link } from '@inertiajs/vue3';
import { route } from 'ziggy-js';
import CategoryGrid from '../components/category/CategoryGrid.vue';
import Icon from '../components/common/Icon.vue';
import ProductGrid from '../components/products/ProductGrid.vue';
import BrandStrip from '../components/sections/BrandStrip.vue';
import CtaSection from '../components/sections/CtaSection.vue';
import DepartmentShowcase from '../components/sections/DepartmentShowcase.vue';
import FeatureGrid from '../components/sections/FeatureGrid.vue';
import HeroSlider from '../components/sections/HeroSlider.vue';
import SectionHeading from '../components/sections/SectionHeading.vue';
import StatisticsBand from '../components/sections/StatisticsBand.vue';
import { useTranslations } from '../composables/useTranslations';

defineProps({
    company: { type: Object, required: true },
    slides: { type: Array, required: true },
    departments: { type: Array, required: true },
    collections: { type: Array, required: true },
    featuredProducts: { type: Array, required: true },
    brands: { type: Array, required: true },
    features: { type: Array, required: true },
    statistics: { type: Array, required: true },
});

const { t } = useTranslations();
</script>

<template>
    <HeroSlider v-if="slides.length > 0" :slides="slides" />

    <!-- Static hero, shown until slides are added in the control panel -->
    <section v-else class="relative isolate flex min-h-[92svh] items-end overflow-hidden bg-ink text-white">
        <img
            v-if="company.hero_image"
            :src="company.hero_image.large"
            alt=""
            fetchpriority="high"
            class="absolute inset-0 -z-20 h-full w-full object-cover"
        />
        <div
            v-else
            class="absolute inset-0 -z-20 bg-[linear-gradient(to_right,rgb(255_255_255/0.06)_1px,transparent_1px),linear-gradient(to_bottom,rgb(255_255_255/0.06)_1px,transparent_1px)] bg-[size:6rem_6rem]"
            aria-hidden="true"
        />
        <div class="absolute inset-0 -z-10 bg-gradient-to-t from-ink via-ink/45 to-ink/25" aria-hidden="true" />

        <div class="mx-auto w-full max-w-7xl px-4 pt-36 pb-16 sm:px-6 sm:pb-20 lg:px-8 lg:pb-28">
            <p v-if="company.tagline" class="eyebrow text-brass-300">{{ company.tagline }}</p>
            <h1 class="mt-5 max-w-4xl font-display text-4xl leading-[1.1] text-balance sm:text-6xl lg:text-7xl">
                {{ company.hero_title || company.name }}
            </h1>
            <p v-if="company.hero_subtitle" class="mt-5 max-w-xl text-base leading-relaxed text-white/80 sm:mt-6 sm:text-lg">{{ company.hero_subtitle }}</p>

            <div class="mt-10 flex flex-col gap-3 sm:flex-row">
                <Link :href="route('products.index')" class="inline-flex items-center justify-center gap-2 rounded-lg bg-white px-7 py-4 text-sm font-medium text-ink transition-colors hover:bg-sand-100">
                    {{ t('Explore collections') }}
                    <Icon name="arrow-right" :size="18" />
                </Link>
                <Link :href="route('contact')" class="inline-flex items-center justify-center rounded-lg border border-white/40 px-7 py-4 text-sm font-medium text-white transition-colors hover:border-white">
                    {{ t('Contact us') }}
                </Link>
            </div>
        </div>
    </section>

    <!-- Departments: the main product lines, when the catalog has several -->
    <section v-if="departments.length > 0" class="mx-auto max-w-7xl px-4 pt-16 sm:px-6 sm:pt-24 lg:px-8 lg:pt-28">
        <SectionHeading :eyebrow="t('Our departments')" :title="t('Everything for your home, under one roof')">
            <Link :href="route('products.index')" class="inline-flex items-center gap-2 text-sm font-medium text-ink hover:text-brass-700">
                {{ t('All products') }}
                <Icon name="arrow-right" :size="16" />
            </Link>
        </SectionHeading>

        <DepartmentShowcase class="mt-8 sm:mt-12" :departments="departments" />
    </section>

    <!-- About -->
    <section v-if="company.introduction" class="mx-auto grid max-w-7xl items-center gap-12 px-4 py-16 sm:gap-14 sm:px-6 sm:py-24 lg:grid-cols-2 lg:gap-20 lg:px-8 lg:py-32">
        <div class="relative">
            <!-- The offset frame stays inside the page padding on small screens; the wide offset needs the two-column layout's gap. -->
            <div
                class="absolute inset-0 translate-x-3 translate-y-3 rounded-2xl border border-brass-300/70 rtl:-translate-x-3 lg:-inset-3 lg:translate-x-6 lg:translate-y-6 lg:rtl:-translate-x-6"
                aria-hidden="true"
            />
            <div class="relative aspect-[4/3] overflow-hidden rounded-2xl bg-sand-200 lg:aspect-[4/5]">
                <img v-if="company.about_image" :src="company.about_image.large" alt="" loading="lazy" class="h-full w-full object-cover" />
            </div>
            <div v-if="company.founded_year" class="absolute start-6 bottom-6 rounded-xl bg-white/95 px-5 py-4 shadow-xl shadow-ink/10 backdrop-blur">
                <p class="text-xs text-muted">{{ t('Since') }}</p>
                <p class="font-display text-3xl text-ink tabular-nums lining-nums">{{ company.founded_year }}</p>
            </div>
        </div>

        <div>
            <p class="eyebrow">{{ t('About us') }}</p>
            <h2 class="mt-3 font-display text-3xl leading-tight text-balance text-ink sm:text-5xl">{{ company.name }}</h2>
            <p class="mt-5 text-base leading-relaxed whitespace-pre-line text-muted sm:mt-6 sm:text-lg">{{ company.introduction }}</p>
            <Link :href="route('about')" class="group mt-8 inline-flex items-center gap-2 text-sm font-medium text-ink">
                <span class="border-b border-brass-400 pb-1">{{ t('Discover our story') }}</span>
                <Icon name="arrow-right" :size="16" class="transition-transform group-hover:translate-x-1 rtl:group-hover:-translate-x-1" />
            </Link>
        </div>
    </section>

    <!-- Collections, when the catalog is a single product line -->
    <section v-if="departments.length === 0 && collections.length > 0" class="border-y border-line bg-white">
        <div class="mx-auto max-w-7xl px-4 py-16 sm:px-6 sm:py-24 lg:px-8">
            <SectionHeading :eyebrow="t('Collections')" :title="t('Designed for every space')">
                <Link :href="route('products.index')" class="inline-flex items-center gap-2 text-sm font-medium text-ink hover:text-brass-700">
                    {{ t('All products') }}
                    <Icon name="arrow-right" :size="16" />
                </Link>
            </SectionHeading>

            <CategoryGrid class="mt-8 sm:mt-12" :categories="collections" />
        </div>
    </section>

    <!-- Featured products -->
    <section v-if="featuredProducts.length > 0" class="mx-auto max-w-7xl px-4 py-16 sm:px-6 sm:py-24 lg:px-8">
        <SectionHeading :eyebrow="t('Featured')" :title="t('Selected for you')" />
        <ProductGrid class="mt-8 sm:mt-12" :products="featuredProducts" />
    </section>

    <!-- Brands -->
    <section v-if="brands.length > 0" class="border-t border-line bg-white">
        <div class="mx-auto max-w-7xl px-4 py-16 sm:px-6 sm:py-24 lg:px-8">
            <SectionHeading :eyebrow="t('Our partners')" :title="t('Brands we carry')">
                <Link :href="route('brands.index')" class="inline-flex items-center gap-2 text-sm font-medium text-ink hover:text-brass-700">
                    {{ t('All brands') }}
                    <Icon name="arrow-right" :size="16" />
                </Link>
            </SectionHeading>

            <BrandStrip class="mt-8 sm:mt-12" :brands="brands" />
        </div>
    </section>

    <!-- Why Nasaq + statistics -->
    <section v-if="features.length > 0 || statistics.length > 0" class="bg-ink">
        <div class="mx-auto flex max-w-7xl flex-col gap-10 px-4 py-16 sm:gap-16 sm:px-6 sm:py-24 lg:px-8 lg:py-28">
            <template v-if="features.length > 0">
                <SectionHeading :eyebrow="t('Why choose us')" :title="t('Why :name', { name: company.name })" inverted />
                <FeatureGrid :features="features" inverted />
            </template>
            <StatisticsBand v-if="statistics.length > 0" :statistics="statistics" inverted :class="{ 'border-t border-white/10 pt-10 sm:pt-16': features.length > 0 }" />
        </div>
    </section>

    <CtaSection :title="company.cta_title" :text="company.cta_text" />
</template>
