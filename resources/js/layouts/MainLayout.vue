<script setup>
import { usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import FlashToaster from '../components/common/FlashToaster.vue';
import SiteFooter from '../components/layout/SiteFooter.vue';
import SeoHead from '../components/layout/SeoHead.vue';
import SiteHeader from '../components/layout/SiteHeader.vue';
import WhatsAppButton from '../components/layout/WhatsAppButton.vue';

const page = usePage();

/**
 * The home page hero runs underneath the transparent header; other pages start below it.
 */
const startsBelowHeader = computed(() => page.component !== 'Home');
</script>

<template>
    <SeoHead />

    <div class="flex min-h-svh flex-col">
        <a href="#content" class="sr-only z-50 rounded bg-ink px-4 py-2 text-white focus:not-sr-only focus:fixed focus:start-4 focus:top-4">
            {{ $t('Skip to content') }}
        </a>

        <SiteHeader />

        <main id="content" class="grow" :class="{ 'pt-20': startsBelowHeader }">
            <slot />
        </main>

        <SiteFooter />

        <WhatsAppButton v-if="$page.props.site.contact.whatsapp" :number="$page.props.site.contact.whatsapp" />
        <FlashToaster />
    </div>
</template>
