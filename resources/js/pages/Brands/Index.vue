<script setup>
import AppBreadcrumbs from '../../components/breadcrumbs/AppBreadcrumbs.vue';
import BrandCard from '../../components/brands/BrandCard.vue';
import CtaSection from '../../components/sections/CtaSection.vue';
import { useTranslations } from '../../composables/useTranslations';

defineProps({
    brands: { type: Array, required: true },
    breadcrumbs: { type: Array, required: true },
});

const { t } = useTranslations();
</script>

<template>
    <section class="mx-auto max-w-7xl px-4 pt-6 pb-12 sm:px-6 sm:pt-10 sm:pb-16 lg:px-8 lg:pt-14">
        <AppBreadcrumbs :items="breadcrumbs" />

        <div class="mt-6 max-w-3xl sm:mt-8">
            <p class="eyebrow">{{ t('Our partners') }}</p>
            <h1 class="mt-3 font-display text-4xl leading-tight text-ink sm:text-5xl lg:text-6xl">{{ t('Brands') }}</h1>
            <p class="mt-4 text-base leading-relaxed text-muted">
                {{ t('We work with leading international manufacturers, so every mixer, basin and tile we supply comes with its maker’s quality and warranty.') }}
            </p>
        </div>

        <div v-if="brands.length > 0" class="mt-10 grid grid-cols-2 gap-3 sm:mt-14 sm:gap-5 md:grid-cols-3 lg:grid-cols-4">
            <BrandCard v-for="brand in brands" :key="brand.id" :brand="brand" />
        </div>
        <p v-else class="mt-10 rounded-2xl border border-dashed border-line px-6 py-16 text-center text-sm text-muted">{{ t('No brands yet.') }}</p>
    </section>

    <CtaSection :title="$page.props.site.name" :text="t('Visit our showroom to see the products of these brands up close, or ask our team for a quote.')" />
</template>
