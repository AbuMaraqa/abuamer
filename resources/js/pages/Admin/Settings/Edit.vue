<script setup>
import { Head, useForm, usePage } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import { route } from 'ziggy-js';
import PageHeader from '../../../components/admin/PageHeader.vue';
import AppButton from '../../../components/common/AppButton.vue';
import Icon from '../../../components/common/Icon.vue';
import SocialIcon from '../../../components/common/SocialIcon.vue';
import FormField from '../../../components/form/FormField.vue';
import ImageUpload from '../../../components/form/ImageUpload.vue';
import LocaleHeading from '../../../components/form/LocaleHeading.vue';
import SelectInput from '../../../components/form/SelectInput.vue';
import TextareaInput from '../../../components/form/TextareaInput.vue';
import TextInput from '../../../components/form/TextInput.vue';
import ToggleSwitch from '../../../components/form/ToggleSwitch.vue';
import { useTranslations } from '../../../composables/useTranslations';

const props = defineProps({
    settings: { type: Object, required: true },
    branding: { type: Object, required: true },
    fonts: { type: Array, required: true },
});

const { t } = useTranslations();
const locales = usePage().props.locale.supported;

const tabs = [
    { key: 'branding', label: 'Logo & icon' },
    { key: 'contact', label: 'Contact details' },
    { key: 'social', label: 'Social media' },
    { key: 'seo', label: 'Search engines' },
    { key: 'site', label: 'Website' },
];
const activeTab = ref('branding');

const brandingFields = ['logo', 'logo_light', 'favicon'];
const brandingErrorKeys = [...brandingFields, 'site.show_name_with_logo'];

const form = useForm({
    ...JSON.parse(JSON.stringify(props.settings)),
    ...Object.fromEntries(brandingFields.flatMap((field) => [[field, null], [`remove_${field}`, false]])),
});

const networks = ['facebook', 'instagram', 'youtube', 'tiktok', 'linkedin', 'x'];
const localeOptions = locales.map(({ code, native }) => ({ value: code, label: native }));

const tabsWithErrors = computed(
    () => new Set(Object.keys(form.errors).map((key) => (brandingErrorKeys.includes(key) ? 'branding' : key.split('.')[0]))),
);

function submit() {
    form.transform((data) => ({ ...data, _method: 'put' })).post(route('admin.settings.update'), {
        forceFormData: true,
        preserveScroll: true,
        onSuccess: () => {
            form.reset(...brandingFields, ...brandingFields.map((field) => `remove_${field}`));
            // Apply the chosen font right away; other pages pick it up on their next load.
            document.documentElement.style.setProperty('--font-site', `var(--font-${form.site.font})`);
        },
    });
}
</script>

<template>
    <Head :title="t('Settings')" />

    <PageHeader :title="t('Settings')" :eyebrow="t('Website')" />

    <form class="flex max-w-4xl flex-col gap-6" @submit.prevent="submit">
        <div class="-mx-4 overflow-x-auto px-4 sm:mx-0 sm:px-0">
            <div class="flex w-max gap-1 rounded-xl bg-sand-200 p-1" role="tablist">
                <button
                    v-for="tab in tabs"
                    :key="tab.key"
                    type="button"
                    role="tab"
                    :aria-selected="activeTab === tab.key"
                    class="relative rounded-lg px-4 py-2 text-sm transition-colors"
                    :class="activeTab === tab.key ? 'bg-white font-medium text-ink shadow-sm' : 'text-ink-soft hover:text-ink'"
                    @click="activeTab = tab.key"
                >
                    {{ t(tab.label) }}
                    <span v-if="tabsWithErrors.has(tab.key)" class="absolute end-1.5 top-1.5 size-2 rounded-full bg-danger" :aria-label="t('Contains errors')" />
                </button>
            </div>
        </div>

        <section v-show="activeTab === 'branding'" class="grid gap-6 md:grid-cols-3">
            <div class="flex flex-col gap-3 rounded-2xl border border-line bg-white p-5">
                <div>
                    <h2 class="text-sm font-semibold text-ink">{{ t('Logo') }}</h2>
                    <p class="mt-1 text-xs text-muted">{{ t('Shown in the header on light backgrounds.') }}</p>
                </div>
                <ImageUpload
                    v-model="form.logo"
                    v-model:removed="form.remove_logo"
                    :current="branding.logo"
                    :error="form.errors.logo"
                    accept="image/png,image/webp"
                    contain
                    :hint="t('A PNG or WebP with a transparent background, at least 120 × 40 pixels.')"
                />
            </div>

            <div class="flex flex-col gap-3 rounded-2xl border border-line bg-white p-5">
                <div>
                    <h2 class="text-sm font-semibold text-ink">{{ t('Logo for dark backgrounds') }}</h2>
                    <p class="mt-1 text-xs text-muted">{{ t('Optional. Used over the home slider, in the footer and in the control panel. Without it, a transparent main logo is shown in white and any other logo is shown as it is.') }}</p>
                </div>
                <ImageUpload
                    v-model="form.logo_light"
                    v-model:removed="form.remove_logo_light"
                    :current="branding.logo_light"
                    :error="form.errors.logo_light"
                    accept="image/png,image/webp"
                    contain
                    dark
                />
            </div>

            <div class="flex flex-col gap-3 rounded-2xl border border-line bg-white p-5">
                <div>
                    <h2 class="text-sm font-semibold text-ink">{{ t('Browser icon (favicon)') }}</h2>
                    <p class="mt-1 text-xs text-muted">{{ t('Shown in the browser tab and bookmarks.') }}</p>
                </div>
                <ImageUpload
                    v-model="form.favicon"
                    v-model:removed="form.remove_favicon"
                    :current="branding.favicon"
                    :error="form.errors.favicon"
                    accept="image/png"
                    contain
                    :hint="t('A square PNG, at least 48 × 48 pixels.')"
                />
            </div>

            <div class="rounded-2xl border border-line bg-white p-5 md:col-span-3">
                <ToggleSwitch
                    v-model="form.site.show_name_with_logo"
                    :label="t('Show the company name next to the logo')"
                    :description="t('Turn it off when the logo already contains the company name.')"
                />
            </div>
        </section>

        <section v-show="activeTab === 'contact'" class="flex flex-col gap-6 rounded-2xl border border-line bg-white p-5 sm:p-6">
            <div class="grid gap-5 sm:grid-cols-2">
                <FormField :label="t('Phone')" for="phone" :error="form.errors['contact.phone']">
                    <TextInput id="phone" v-model="form.contact.phone" type="tel" dir="ltr" />
                </FormField>
                <FormField :label="t('Mobile')" for="mobile" :error="form.errors['contact.mobile']">
                    <TextInput id="mobile" v-model="form.contact.mobile" type="tel" dir="ltr" />
                </FormField>
                <FormField :label="t('WhatsApp number')" for="whatsapp" :error="form.errors['contact.whatsapp']" :hint="t('International format without + or spaces, e.g. 9665XXXXXXXX.')">
                    <TextInput id="whatsapp" v-model="form.contact.whatsapp" inputmode="numeric" dir="ltr" />
                </FormField>
                <FormField :label="t('Email')" for="email" :error="form.errors['contact.email']">
                    <TextInput id="email" v-model="form.contact.email" type="email" dir="ltr" />
                </FormField>
            </div>

            <div class="grid gap-5 md:grid-cols-2">
                <div v-for="locale in locales" :key="locale.code" :dir="locale.direction" :lang="locale.code" class="flex flex-col gap-4">
                    <LocaleHeading :locale="locale" />
                    <FormField :label="t('Address')" :for="`address-${locale.code}`" :error="form.errors[`contact.address.${locale.code}`]">
                        <TextareaInput :id="`address-${locale.code}`" v-model="form.contact.address[locale.code]" rows="2" />
                    </FormField>
                    <FormField :label="t('Working hours')" :for="`hours-${locale.code}`" :error="form.errors[`contact.working_hours.${locale.code}`]">
                        <TextInput :id="`hours-${locale.code}`" v-model="form.contact.working_hours[locale.code]" />
                    </FormField>
                </div>
            </div>

            <div class="grid gap-5">
                <FormField :label="t('Google Maps link')" for="map-url" :error="form.errors['contact.map_url']" :hint="t('Opens the location in Google Maps.')">
                    <TextInput id="map-url" v-model="form.contact.map_url" type="url" dir="ltr" />
                </FormField>
                <FormField :label="t('Google Maps embed address')" for="map-embed" :error="form.errors['contact.map_embed_url']" :hint="t('In Google Maps choose Share → Embed a map, then paste the address that follows src= in the code.')">
                    <TextInput id="map-embed" v-model="form.contact.map_embed_url" type="url" dir="ltr" />
                </FormField>
                <FormField :label="t('Send contact form messages to')" for="recipient" :error="form.errors['contact.form_recipient']" :hint="t('Messages are always saved in the control panel as well.')">
                    <TextInput id="recipient" v-model="form.contact.form_recipient" type="email" dir="ltr" />
                </FormField>
            </div>
        </section>

        <section v-show="activeTab === 'social'" class="grid gap-5 rounded-2xl border border-line bg-white p-5 sm:grid-cols-2 sm:p-6">
            <FormField v-for="network in networks" :key="network" :for="`social-${network}`" :error="form.errors[`social.${network}`]">
                <div class="relative">
                    <SocialIcon :network="network" :size="18" class="pointer-events-none absolute start-3.5 top-1/2 -translate-y-1/2 text-muted" />
                    <TextInput :id="`social-${network}`" v-model="form.social[network]" type="url" dir="ltr" class="ps-11" :placeholder="`https://${network === 'x' ? 'x' : network}.com/…`" :aria-label="network" />
                </div>
            </FormField>
        </section>

        <section v-show="activeTab === 'seo'" class="flex flex-col gap-5 rounded-2xl border border-line bg-white p-5 sm:p-6">
            <p class="text-sm text-muted">{{ t('Used for the home page and for any page without its own search engine texts.') }}</p>
            <div class="grid gap-6 md:grid-cols-2">
                <div v-for="locale in locales" :key="locale.code" :dir="locale.direction" :lang="locale.code" class="flex flex-col gap-4">
                    <LocaleHeading :locale="locale" />
                    <FormField :label="t('SEO title')" :for="`meta-title-${locale.code}`" :error="form.errors[`seo.meta_title.${locale.code}`]">
                        <TextInput :id="`meta-title-${locale.code}`" v-model="form.seo.meta_title[locale.code]" />
                    </FormField>
                    <FormField :label="t('SEO description')" :for="`meta-description-${locale.code}`" :error="form.errors[`seo.meta_description.${locale.code}`]">
                        <TextareaInput :id="`meta-description-${locale.code}`" v-model="form.seo.meta_description[locale.code]" rows="3" />
                    </FormField>
                    <FormField :label="t('Keywords')" :for="`meta-keywords-${locale.code}`" :error="form.errors[`seo.meta_keywords.${locale.code}`]" :hint="t('Separated by commas.')">
                        <TextInput :id="`meta-keywords-${locale.code}`" v-model="form.seo.meta_keywords[locale.code]" />
                    </FormField>
                </div>
            </div>
        </section>

        <section v-show="activeTab === 'site'" class="flex flex-col gap-6 rounded-2xl border border-line bg-white p-5 sm:p-6">
            <fieldset class="flex flex-col gap-3">
                <legend class="mb-1 text-sm font-medium text-ink-soft">{{ t('Website font') }}</legend>
                <div class="grid gap-3 sm:grid-cols-2">
                    <label
                        v-for="font in fonts"
                        :key="font.value"
                        class="flex cursor-pointer flex-col gap-3 rounded-xl border-2 p-4 transition-colors"
                        :class="form.site.font === font.value ? 'border-brass-500 bg-brass-300/10' : 'border-line hover:border-sand-400'"
                    >
                        <span class="flex items-center justify-between gap-3">
                            <span class="text-sm font-medium text-ink" dir="ltr">{{ font.label }}</span>
                            <input v-model="form.site.font" type="radio" name="font" :value="font.value" class="size-4 accent-brass-500" />
                        </span>
                        <span class="flex flex-col gap-1 text-ink" :style="{ fontFamily: `var(--font-${font.value})` }">
                            <span class="text-xl" dir="rtl" lang="ar">نُسق للبلاط والأدوات الصحية</span>
                            <span class="text-sm text-muted" dir="rtl" lang="ar">بلاط ورخام وأدوات صحية — 60 × 120 سم</span>
                            <span class="text-sm text-muted" dir="ltr" lang="en">Premium tiles, marble and sanitary ware</span>
                        </span>
                    </label>
                </div>
                <p v-if="form.errors['site.font']" class="text-sm text-danger">{{ form.errors['site.font'] }}</p>
                <p v-else class="text-xs text-muted">{{ t('Used for the text of the website and the control panel. Headings keep their display font.') }}</p>
            </fieldset>

            <FormField :label="t('Default language')" for="default-locale" :error="form.errors['site.default_locale']" :hint="t('The language visitors see when they open the website address without a language.')">
                <SelectInput id="default-locale" v-model="form.site.default_locale" :options="localeOptions" class="sm:max-w-xs" />
            </FormField>

            <div class="flex flex-col gap-3">
                <ToggleSwitch v-model="form.site.maintenance_mode" :label="t('Maintenance mode')" :description="t('Visitors see a “we will be back soon” page. You can still browse the website while signed in.')" />
                <p v-if="form.site.maintenance_mode" class="flex items-center gap-2 rounded-lg bg-danger/5 px-3 py-2 text-sm text-danger">
                    <Icon name="alert" :size="16" />
                    {{ t('The website is hidden from visitors while maintenance mode is on.') }}
                </p>
            </div>
        </section>

        <!-- Stays in reach while scrolling the long tabs, like the company page. -->
        <div class="sticky bottom-4 z-10 flex justify-end">
            <AppButton type="submit" size="lg" class="shadow-xl shadow-ink/10" :loading="form.processing">{{ t('Save changes') }}</AppButton>
        </div>
    </form>
</template>
