<script setup>
import { usePage } from '@inertiajs/vue3';
import { computed, onBeforeUnmount, onMounted, ref } from 'vue';
import { route } from 'ziggy-js';
import AppBreadcrumbs from '../../components/breadcrumbs/AppBreadcrumbs.vue';
import AppButton from '../../components/common/AppButton.vue';
import SocialIcon from '../../components/common/SocialIcon.vue';
import ProductGallery from '../../components/products/ProductGallery.vue';
import ProductGrid from '../../components/products/ProductGrid.vue';
import ProductSpecifications from '../../components/products/ProductSpecifications.vue';
import { useTranslations } from '../../composables/useTranslations';

const props = defineProps({
    product: { type: Object, required: true },
    relatedProducts: { type: Array, required: true },
    breadcrumbs: { type: Array, required: true },
});

const { t } = useTranslations();
const whatsapp = usePage().props.site.contact.whatsapp;

/**
 * A WhatsApp chat that opens with a message naming this product, its code and its link.
 */
const whatsAppUrl = computed(() => {
    const name = props.product.sku ? `${props.product.name} (${props.product.sku})` : props.product.name;
    const text = `${t('Hello, I would like to ask about :name', { name })}\n${props.product.url}`;

    return `https://wa.me/${whatsapp}?text=${encodeURIComponent(text)}`;
});

/**
 * On phones, once the visitor scrolls past the inquiry buttons, a bar keeps them at hand.
 */
const inquiryActions = ref(null);
const isInquiryBarShown = ref(false);
let observer = null;

onMounted(() => {
    // The top margin is the fixed header, which hides whatever scrolls beneath it.
    observer = new IntersectionObserver(
        ([entry]) => {
            isInquiryBarShown.value = !entry.isIntersecting && entry.boundingClientRect.top < 0;
        },
        { rootMargin: '-64px 0px 0px 0px' },
    );
    observer.observe(inquiryActions.value);
});

onBeforeUnmount(() => observer?.disconnect());
</script>

<template>
    <section class="mx-auto max-w-7xl px-4 pt-6 pb-12 sm:px-6 sm:pt-10 sm:pb-16 lg:px-8 lg:pt-14">
        <AppBreadcrumbs :items="breadcrumbs" />

        <div class="mt-6 grid gap-8 sm:mt-8 sm:gap-10 lg:grid-cols-2 lg:gap-16">
            <ProductGallery :images="product.gallery" :alt="product.name" />

            <div class="flex flex-col gap-7 sm:gap-8 lg:py-4">
                <div class="flex flex-col gap-3">
                    <p v-if="product.category" class="eyebrow">{{ product.category.name }}</p>
                    <h1 class="font-display text-3xl leading-tight text-balance text-ink sm:text-5xl">{{ product.name }}</h1>
                    <p v-if="product.sku" class="text-sm text-muted">
                        {{ t('Code') }}: <span dir="ltr">{{ product.sku }}</span>
                    </p>
                </div>

                <p v-if="product.short_description" class="text-base leading-relaxed text-ink-soft">{{ product.short_description }}</p>

                <!-- Prices are given on request, so asking about the product is the main action here. -->
                <div ref="inquiryActions" class="flex flex-col gap-3 sm:flex-row">
                    <AppButton :href="route('contact', { product: product.id })" size="lg">{{ t('Request a quote') }}</AppButton>
                    <a
                        v-if="whatsapp"
                        :href="whatsAppUrl"
                        target="_blank"
                        rel="noopener"
                        class="inline-flex items-center justify-center gap-2.5 rounded-lg border border-line bg-white px-6 py-3.5 text-sm font-medium text-ink transition-colors hover:border-sand-400"
                    >
                        <SocialIcon network="whatsapp" :size="18" class="text-[#25D366]" />
                        {{ t('Ask on WhatsApp') }}
                    </a>
                </div>

                <div v-if="product.specifications.length > 0" class="flex flex-col gap-4">
                    <h2 class="text-sm font-semibold text-ink">{{ t('Specifications') }}</h2>
                    <ProductSpecifications :specifications="product.specifications" />
                </div>

                <div v-if="product.description" class="flex flex-col gap-3">
                    <h2 class="text-sm font-semibold text-ink">{{ t('Description') }}</h2>
                    <p class="text-sm leading-loose whitespace-pre-line text-ink-soft">{{ product.description }}</p>
                </div>
            </div>
        </div>
    </section>

    <section v-if="relatedProducts.length > 0" class="border-t border-line bg-white">
        <div class="mx-auto max-w-7xl px-4 py-12 sm:px-6 sm:py-16 lg:px-8">
            <h2 class="mb-8 font-display text-2xl text-ink sm:mb-10 sm:text-3xl">{{ t('You may also like') }}</h2>
            <ProductGrid :products="relatedProducts" />
        </div>
    </section>

    <!-- Sticky at the end of the page content: it rides the bottom of the screen, then settles above the footer. -->
    <Transition
        enter-active-class="transition duration-300 ease-elegant"
        enter-from-class="translate-y-full opacity-0"
        leave-active-class="transition duration-200"
        leave-to-class="translate-y-full opacity-0"
    >
        <div
            v-show="isInquiryBarShown"
            class="sticky bottom-0 z-30 border-t border-line bg-white/95 px-4 py-3 shadow-[0_-12px_24px_-16px_rgb(29_27_24/0.25)] backdrop-blur-md lg:hidden"
        >
            <div class="flex items-center gap-3">
                <div class="min-w-0 grow">
                    <p class="truncate text-sm font-medium text-ink">{{ product.name }}</p>
                    <p v-if="product.sku" class="truncate text-xs text-muted"><span dir="ltr">{{ product.sku }}</span></p>
                </div>
                <a
                    v-if="whatsapp"
                    :href="whatsAppUrl"
                    target="_blank"
                    rel="noopener"
                    class="flex size-11 shrink-0 items-center justify-center rounded-lg bg-[#25D366] text-white transition-opacity hover:opacity-90"
                    :aria-label="t('Ask on WhatsApp')"
                >
                    <SocialIcon network="whatsapp" :size="20" />
                </a>
                <AppButton :href="route('contact', { product: product.id })" class="h-11 shrink-0">{{ t('Request a quote') }}</AppButton>
            </div>
        </div>
    </Transition>
</template>
