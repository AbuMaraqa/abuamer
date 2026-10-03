<script setup>
import { useForm, usePage } from '@inertiajs/vue3';
import { computed, nextTick, ref } from 'vue';
import { route } from 'ziggy-js';
import AppButton from '../components/common/AppButton.vue';
import Icon from '../components/common/Icon.vue';
import SocialIcon from '../components/common/SocialIcon.vue';
import FormField from '../components/form/FormField.vue';
import TextareaInput from '../components/form/TextareaInput.vue';
import TextInput from '../components/form/TextInput.vue';
import { useTranslations } from '../composables/useTranslations';

const props = defineProps({
    mapEmbedUrl: { type: String, default: null },
    workingHours: { type: String, default: null },
    // Filled in when the visitor comes from a product's "Request a quote" button.
    subject: { type: String, default: null },
});

const { t } = useTranslations();
const contact = usePage().props.site.contact;
const formElement = ref(null);

const form = useForm({
    name: '',
    email: '',
    phone: '',
    subject: props.subject ?? '',
    message: '',
    website: '',
});

const channels = computed(() =>
    [
        contact.phone && { icon: 'phone', label: t('Phone'), value: contact.phone, href: `tel:${contact.phone}`, ltr: true },
        contact.mobile && { icon: 'phone', label: t('Mobile'), value: contact.mobile, href: `tel:${contact.mobile}`, ltr: true },
        contact.email && { icon: 'mail', label: t('Email'), value: contact.email, href: `mailto:${contact.email}` },
        contact.address && { icon: 'map-pin', label: t('Address'), value: contact.address, href: contact.map_url },
    ].filter(Boolean),
);

function submit() {
    form.post(route('contact.store'), {
        preserveScroll: true,
        onSuccess: () => form.reset(),
        // Take the visitor (and screen readers) straight to the first field to fix.
        onError: () => nextTick(() => formElement.value?.querySelector('[aria-invalid="true"]')?.focus()),
    });
}
</script>

<template>
    <section class="mx-auto max-w-7xl px-4 pt-10 pb-8 sm:px-6 sm:pt-16 sm:pb-12 lg:px-8 lg:pt-20">
        <p class="eyebrow">{{ t('Contact') }}</p>
        <h1 class="mt-4 max-w-3xl font-display text-4xl leading-tight text-balance text-ink sm:text-6xl">{{ t('Let’s talk about your project') }}</h1>
        <p class="mt-4 max-w-2xl text-base leading-relaxed text-muted sm:mt-5 sm:text-lg">
            {{ t('Our team will help you choose the right tiles, sanitary ware and finishes for your project.') }}
        </p>
    </section>

    <section class="mx-auto grid max-w-7xl gap-8 px-4 pb-16 sm:gap-10 sm:px-6 sm:pb-24 lg:grid-cols-5 lg:gap-14 lg:px-8">
        <div class="flex flex-col gap-8 lg:col-span-2">
            <ul class="flex flex-col divide-y divide-line border-y border-line">
                <li v-for="channel in channels" :key="channel.label" class="flex gap-4 py-5">
                    <span class="flex size-11 shrink-0 items-center justify-center rounded-full bg-brass-300/20 text-brass-700">
                        <Icon :name="channel.icon" :size="20" />
                    </span>
                    <div class="min-w-0">
                        <p class="text-xs text-muted">{{ channel.label }}</p>
                        <a v-if="channel.href" :href="channel.href" :dir="channel.ltr ? 'ltr' : undefined" class="mt-0.5 block break-words text-ink hover:text-brass-700" :target="channel.icon === 'map-pin' ? '_blank' : undefined" rel="noopener">
                            {{ channel.value }}
                        </a>
                        <p v-else class="mt-0.5 text-ink">{{ channel.value }}</p>
                    </div>
                </li>
                <li v-if="workingHours" class="flex gap-4 py-5">
                    <span class="flex size-11 shrink-0 items-center justify-center rounded-full bg-brass-300/20 text-brass-700">
                        <Icon name="clock" :size="20" />
                    </span>
                    <div>
                        <p class="text-xs text-muted">{{ t('Working hours') }}</p>
                        <p class="mt-0.5 text-ink whitespace-pre-line">{{ workingHours }}</p>
                    </div>
                </li>
            </ul>

            <a
                v-if="contact.whatsapp"
                :href="`https://wa.me/${contact.whatsapp}`"
                target="_blank"
                rel="noopener"
                class="inline-flex items-center justify-center gap-3 rounded-xl bg-[#25D366] px-6 py-4 text-sm font-medium text-white transition-opacity hover:opacity-90"
            >
                <SocialIcon network="whatsapp" :size="20" />
                {{ t('Chat on WhatsApp') }}
            </a>

            <div v-if="Object.keys($page.props.site.social).length > 0" class="flex flex-wrap gap-2">
                <a
                    v-for="(url, network) in $page.props.site.social"
                    :key="network"
                    :href="url"
                    target="_blank"
                    rel="noopener"
                    class="flex size-11 items-center justify-center rounded-full border border-line text-ink-soft transition-colors hover:border-ink hover:text-ink"
                    :aria-label="network"
                >
                    <SocialIcon :network="network" :size="18" />
                </a>
            </div>
        </div>

        <form ref="formElement" class="flex flex-col gap-5 rounded-2xl border border-line bg-white p-5 sm:p-8 lg:col-span-3" novalidate @submit.prevent="submit">
            <h2 class="font-display text-2xl text-ink">{{ t('Send us a message') }}</h2>

            <div class="grid gap-5 sm:grid-cols-2">
                <FormField :label="t('Name')" for="contact-name" :error="form.errors.name" required>
                    <TextInput id="contact-name" v-model="form.name" autocomplete="name" required :invalid="!!form.errors.name" />
                </FormField>
                <FormField :label="t('Email')" for="contact-email" :error="form.errors.email" required>
                    <TextInput id="contact-email" v-model="form.email" type="email" autocomplete="email" dir="ltr" required :invalid="!!form.errors.email" />
                </FormField>
                <FormField :label="t('Phone')" for="contact-phone" :error="form.errors.phone">
                    <TextInput id="contact-phone" v-model="form.phone" type="tel" autocomplete="tel" dir="ltr" :invalid="!!form.errors.phone" />
                </FormField>
                <FormField :label="t('Subject')" for="contact-subject" :error="form.errors.subject">
                    <TextInput id="contact-subject" v-model="form.subject" :invalid="!!form.errors.subject" />
                </FormField>
            </div>

            <FormField :label="t('Message')" for="contact-message" :error="form.errors.message" required>
                <TextareaInput id="contact-message" v-model="form.message" rows="6" required :invalid="!!form.errors.message" />
            </FormField>

            <!-- Honeypot: hidden from people and assistive technology, often filled by bots. -->
            <div class="absolute -start-[9999px] h-0 overflow-hidden" aria-hidden="true">
                <label for="contact-website">Website</label>
                <input id="contact-website" v-model="form.website" type="text" tabindex="-1" autocomplete="off" />
            </div>

            <AppButton type="submit" size="lg" class="w-full sm:w-auto sm:self-start" :loading="form.processing">{{ t('Send message') }}</AppButton>
        </form>
    </section>

    <section v-if="mapEmbedUrl" class="border-t border-line">
        <iframe
            :src="mapEmbedUrl"
            :title="t('Our location on the map')"
            class="h-80 w-full grayscale-[35%] sm:h-[28rem]"
            loading="lazy"
            referrerpolicy="no-referrer-when-downgrade"
            allowfullscreen
        />
    </section>
</template>
