<script setup>
import { route } from 'ziggy-js';
import AppButton from '../common/AppButton.vue';
import SocialIcon from '../common/SocialIcon.vue';

defineProps({
    title: { type: String, default: null },
    text: { type: String, default: null },
});
</script>

<template>
    <section class="bg-sand-100">
        <div class="mx-auto flex max-w-4xl flex-col items-center gap-5 px-4 py-16 text-center sm:gap-6 sm:px-6 sm:py-24 lg:py-28">
            <h2 class="font-display text-3xl leading-tight text-balance text-ink sm:text-5xl">{{ title || $t('Let’s bring your space to life') }}</h2>
            <p class="max-w-2xl text-base leading-relaxed text-muted">
                {{ text || $t('Visit our showroom or tell us about your project, and our team will help you choose the right tiles.') }}
            </p>
            <div class="mt-2 flex w-full flex-col gap-3 sm:w-auto sm:flex-row">
                <AppButton :href="route('contact')" size="lg">{{ $t('Request a quote') }}</AppButton>
                <a
                    v-if="$page.props.site.contact.whatsapp"
                    :href="`https://wa.me/${$page.props.site.contact.whatsapp}`"
                    target="_blank"
                    rel="noopener"
                    class="inline-flex items-center justify-center gap-2.5 rounded-lg border border-line bg-white px-6 py-3.5 text-sm font-medium text-ink transition-colors hover:border-sand-400"
                >
                    <SocialIcon network="whatsapp" :size="18" class="text-[#25D366]" />
                    {{ $t('Chat on WhatsApp') }}
                </a>
            </div>
        </div>
    </section>
</template>
