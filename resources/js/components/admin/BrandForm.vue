<script setup>
import { useForm, usePage } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import { route } from 'ziggy-js';
import { slugify } from '../../composables/useCategoryTree';
import { BRAND_COUNTRIES, countryName } from '../../composables/useCountryName';
import { useTranslations } from '../../composables/useTranslations';
import AppButton from '../common/AppButton.vue';
import Icon from '../common/Icon.vue';
import FormField from '../form/FormField.vue';
import ImageUpload from '../form/ImageUpload.vue';
import LocaleHeading from '../form/LocaleHeading.vue';
import SelectInput from '../form/SelectInput.vue';
import TextareaInput from '../form/TextareaInput.vue';
import TextInput from '../form/TextInput.vue';
import ToggleSwitch from '../form/ToggleSwitch.vue';

/**
 * Create or edit a brand. The name is written once (brands keep their name in every
 * language); only the description is translated.
 */
const props = defineProps({
    brand: { type: Object, default: null },
});

const { t } = useTranslations();
const page = usePage();
const locales = page.props.locale.supported;

const form = useForm({
    name: props.brand?.name ?? '',
    slug: props.brand?.slug ?? '',
    country: props.brand?.country ?? null,
    website: props.brand?.website ?? '',
    status: props.brand?.status ?? true,
    ...Object.fromEntries(locales.map(({ code }) => [code, { description: props.brand?.[code]?.description ?? '' }])),
    logo: null,
    remove_logo: false,
});

const slugTouched = ref(!!props.brand?.slug);

function updateName(name) {
    form.name = name;

    if (!slugTouched.value) {
        form.slug = slugify(name, { code: 'en', script: 'Latn' });
    }
}

function updateSlug(slug) {
    form.slug = slug;
    slugTouched.value = slug !== '';
}

/**
 * Countries in the alphabetical order of the control panel's language.
 */
const countryOptions = computed(() => [
    { value: null, label: t('Not specified') },
    ...BRAND_COUNTRIES.map((code) => ({ value: code, label: countryName(code) })).sort((a, b) => a.label.localeCompare(b.label, page.props.locale.current)),
]);

function submit() {
    const options = { forceFormData: true, preserveScroll: true };

    if (props.brand) {
        form.transform((data) => ({ ...data, _method: 'put' })).post(route('admin.brands.update', props.brand.id), options);
    } else {
        form.post(route('admin.brands.store'), options);
    }
}
</script>

<template>
    <form class="form-with-actions grid gap-6 lg:grid-cols-[1fr_20rem]" @submit.prevent="submit">
        <div class="flex min-w-0 flex-col gap-6">
            <section class="grid gap-5 rounded-2xl border border-line bg-white p-5 sm:p-6 md:grid-cols-2">
                <FormField :label="t('Brand name')" for="brand-name" :error="form.errors.name" :hint="t('As the manufacturer writes it, e.g. “Villeroy & Boch”.')" required>
                    <TextInput id="brand-name" :model-value="form.name" maxlength="100" dir="auto" required :invalid="!!form.errors.name" @update:model-value="updateName" />
                </FormField>

                <FormField :label="t('Slug')" for="brand-slug" :error="form.errors.slug" :hint="t('Used in the page address. Generated from the name.')">
                    <TextInput id="brand-slug" :model-value="form.slug" dir="ltr" class="font-mono text-xs" :invalid="!!form.errors.slug" @update:model-value="updateSlug" />
                </FormField>

                <FormField :label="t('Country of origin')" for="brand-country" :error="form.errors.country">
                    <SelectInput id="brand-country" v-model="form.country" :options="countryOptions" />
                </FormField>

                <FormField :label="t('Website')" for="brand-website" :error="form.errors.website" :hint="t('Optional, e.g. https://www.example.com')">
                    <TextInput id="brand-website" v-model="form.website" type="url" dir="ltr" maxlength="255" :invalid="!!form.errors.website" />
                </FormField>
            </section>

            <section class="rounded-2xl border border-line bg-white p-5 sm:p-6">
                <h2 class="text-base font-semibold text-ink">{{ t('About the brand') }}</h2>
                <p class="mt-1 mb-5 text-xs text-muted">{{ t('Shown on the brand page. Optional.') }}</p>

                <div class="grid gap-6 md:grid-cols-2">
                    <div v-for="locale in locales" :key="locale.code" :dir="locale.direction" :lang="locale.code" class="flex flex-col gap-4">
                        <LocaleHeading :locale="locale" />
                        <FormField :label="t('Description')" :for="`description-${locale.code}`" :error="form.errors[`${locale.code}.description`]">
                            <TextareaInput :id="`description-${locale.code}`" v-model="form[locale.code].description" rows="5" maxlength="3000" />
                        </FormField>
                    </div>
                </div>
            </section>
        </div>

        <aside class="flex flex-col gap-6">
            <section class="flex flex-col gap-3 rounded-2xl border border-line bg-white p-5">
                <div>
                    <h2 class="text-base font-semibold text-ink">{{ t('Logo') }}</h2>
                    <p class="mt-1 text-xs text-muted">{{ t('A PNG with a transparent background looks best.') }}</p>
                </div>
                <ImageUpload v-model="form.logo" v-model:removed="form.remove_logo" :current="brand?.logo ?? null" :error="form.errors.logo" contain />
            </section>

            <section class="flex flex-col gap-5 rounded-2xl border border-line bg-white p-5">
                <ToggleSwitch v-model="form.status" :label="t('Visible on website')" :description="t('Hidden brands have no page and are not named on their products.')" />
            </section>

            <div class="flex flex-col gap-3 lg:sticky lg:top-24">
                <div class="form-actions">
                    <AppButton type="submit" size="lg" class="grow" :loading="form.processing">
                        {{ brand ? t('Save changes') : t('Add brand') }}
                    </AppButton>
                    <AppButton variant="secondary" size="lg" :href="route('admin.brands.index')">{{ t('Cancel') }}</AppButton>
                </div>

                <a v-if="brand && brand.status" :href="brand.url" target="_blank" rel="noopener" class="inline-flex items-center gap-1.5 px-1 text-sm text-muted hover:text-ink">
                    <Icon name="globe" :size="16" />
                    {{ t('View on website') }}
                </a>
            </div>
        </aside>
    </form>
</template>
