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
        class="fixed end-5 bottom-5 z-30 flex size-14 items-center justify-center rounded-full bg-[#25D366] text-white shadow-xl shadow-ink/20 transition-transform hover:scale-105 focus-visible:scale-105 motion-reduce:transition-none sm:end-6 sm:bottom-6"
        :aria-label="$t('Chat with us on WhatsApp')"
    >
        <SocialIcon network="whatsapp" :size="26" />
    </a>
</template>
