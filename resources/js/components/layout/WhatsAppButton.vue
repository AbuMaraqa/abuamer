<script setup>
import SocialIcon from '../common/SocialIcon.vue';

defineProps({
    number: { type: String, required: true },
});
</script>

<template>
    <a
        :href="`https://wa.me/${number}`"
        target="_blank"
        rel="noopener"
        class="fixed end-4 bottom-4 z-30 flex size-12 items-center justify-center rounded-full bg-[#25D366] text-white shadow-xl shadow-ink/20 transition-transform motion-safe:hover:scale-105 motion-safe:focus-visible:scale-105 sm:end-6 sm:bottom-6 sm:size-14"
        :aria-label="$t('Chat with us on WhatsApp')"
    >
        <SocialIcon network="whatsapp" :size="24" />
    </a>
</template>
