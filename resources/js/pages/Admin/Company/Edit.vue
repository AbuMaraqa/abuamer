<script setup>
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import { route } from 'ziggy-js';
import HighlightsEditor from '../../../components/admin/HighlightsEditor.vue';
import PageHeader from '../../../components/admin/PageHeader.vue';
import AppButton from '../../../components/common/AppButton.vue';
import Icon from '../../../components/common/Icon.vue';
import FormField from '../../../components/form/FormField.vue';
import ImageUpload from '../../../components/form/ImageUpload.vue';
import TextareaInput from '../../../components/form/TextareaInput.vue';
import TextInput from '../../../components/form/TextInput.vue';
import GalleryManager from '../../../components/products/GalleryManager.vue';
import { useTranslations } from '../../../composables/useTranslations';

/**
 * Company identity and the content of the home and about pages, in every language.
 */
const props = defineProps({
    company: { type: Object, required: true },
    featureIcons: { type: Array, required: true },
});

const { t } = useTranslations();
const locales = usePage().props.locale.supported;

const textFields = {
    identity: [
        { name: 'name', label: 'Company name', required: true },
        { name: 'tagline', label: 'Tagline' },
    ],
    home: [
        { name: 'hero_title', label: 'Hero headline' },
        { name: 'hero_subtitle', label: 'Hero text', multiline: true },
        { name: 'cta_title', label: 'Call to action headline' },
        { name: 'cta_text', label: 'Call to action text', multiline: true },
    ],
    about: [
        { name: 'introduction', label: 'Introduction', multiline: true, rows: 4 },
        { name: 'story', label: 'Our story', multiline: true, rows: 8 },
        { name: 'vision', label: 'Vision', multiline: true },
        { name: 'mission', label: 'Mission', multiline: true },
    ],
};

const tabs = [
    { key: 'identity', label: 'Identity', fields: ['name', 'tagline', 'founded_year'] },
    { key: 'home', label: 'Home page', fields: ['hero_title', 'hero_subtitle', 'cta_title', 'cta_text', 'hero_image'] },
    { key: 'about', label: 'About page', fields: ['introduction', 'story', 'vision', 'mission', 'about_image'] },
    { key: 'highlights', label: 'Values & strengths', fields: ['values', 'features', 'statistics'] },
    { key: 'gallery', label: 'Gallery', fields: ['gallery'] },
];

const activeTab = ref('identity');

const form = useForm({
    founded_year: props.company.founded_year ?? '',
    ...Object.fromEntries(locales.map(({ code }) => [code, { ...props.company[code] }])),
    hero_image: null,
    about_image: null,
    remove_hero_image: false,
    remove_about_image: false,
    gallery_uploads: [],
});

const lists = ref({
    values: props.company.values.map((item) => ({ ...item })),
    features: props.company.features.map((item) => ({ ...item })),
    statistics: props.company.statistics.map((item) => ({ ...item })),
});
const galleryImages = ref([...props.company.gallery]);

/**
 * Tabs holding at least one validation error, so problems on hidden tabs are visible.
 */
const tabsWithErrors = computed(() => {
    const keys = Object.keys(form.errors);

    return new Set(
        tabs
            .filter((tab) => keys.some((key) => tab.fields.some((field) => key === field || key.startsWith(`${field}.`) || locales.some(({ code }) => key === `${code}.${field}`))))
            .map((tab) => tab.key),
    );
});

function submit() {
    form.transform((data) => ({
        ...data,
        ...Object.fromEntries(
            Object.entries(lists.value).map(([list, items]) => [list, items.map(({ key, ...item }) => item)]),
        ),
        gallery: galleryImages.value.map((image) => image.id),
        _method: 'put',
    })).post(route('admin.company.update'), {
        forceFormData: true,
        preserveScroll: true,
        preserveState: 'errors',
    });
}
</script>

<template>
    <Head :title="t('Company')" />

    <PageHeader :title="t('Company')" :eyebrow="t('Content')" :description="t('The company identity and the content of the home and about pages.')" />

    <form class="flex flex-col gap-6" @submit.prevent="submit">
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

        <!-- Texts of the identity, home and about tabs -->
        <section v-for="group in ['identity', 'home', 'about']" v-show="activeTab === group" :key="group" class="grid gap-6 lg:grid-cols-[1fr_20rem]">
            <div class="grid gap-6 rounded-2xl border border-line bg-white p-5 sm:p-6 md:grid-cols-2">
                <div v-for="locale in locales" :key="locale.code" :dir="locale.code === 'ar' ? 'rtl' : 'ltr'" :lang="locale.code" class="flex flex-col gap-4">
                    <p class="text-xs font-semibold text-brass-600">{{ locale.native }}</p>
                    <FormField
                        v-for="field in textFields[group]"
                        :key="field.name"
                        :label="t(field.label)"
                        :for="`${field.name}-${locale.code}`"
                        :error="form.errors[`${locale.code}.${field.name}`]"
                        :required="field.required"
                    >
                        <TextareaInput v-if="field.multiline" :id="`${field.name}-${locale.code}`" v-model="form[locale.code][field.name]" :rows="field.rows ?? 3" />
                        <TextInput v-else :id="`${field.name}-${locale.code}`" v-model="form[locale.code][field.name]" :invalid="!!form.errors[`${locale.code}.${field.name}`]" />
                    </FormField>
                </div>
            </div>

            <aside class="flex flex-col gap-6">
                <template v-if="group === 'identity'">
                    <section class="rounded-2xl border border-line bg-white p-5">
                        <FormField :label="t('Founded in')" for="founded-year" :error="form.errors.founded_year">
                            <TextInput id="founded-year" v-model="form.founded_year" type="number" min="1900" dir="ltr" />
                        </FormField>
                    </section>
                    <Link :href="route('admin.settings.edit')" class="flex items-center gap-3 rounded-2xl border border-dashed border-line p-5 text-sm text-muted transition-colors hover:border-sand-400 hover:text-ink">
                        <Icon name="image" :size="18" class="shrink-0 text-brass-600" />
                        {{ t('The logo and browser icon are managed in Settings.') }}
                    </Link>
                </template>

                <Link v-if="group === 'home'" :href="route('admin.slides.index')" class="flex items-center gap-3 rounded-2xl border border-dashed border-line p-5 text-sm text-muted transition-colors hover:border-sand-400 hover:text-ink">
                    <Icon name="image" :size="18" class="shrink-0 text-brass-600" />
                    {{ t('When the slider has slides, they replace the hero headline and image below.') }}
                </Link>

                <section v-if="group === 'home'" class="flex flex-col gap-3 rounded-2xl border border-line bg-white p-5">
                    <h2 class="text-sm font-medium text-ink-soft">{{ t('Hero image') }}</h2>
                    <ImageUpload v-model="form.hero_image" v-model:removed="form.remove_hero_image" :current="company.hero_image" :error="form.errors.hero_image" :hint="t('A wide photograph, at least 1600 × 900 pixels.')" />
                </section>

                <section v-if="group === 'about'" class="flex flex-col gap-3 rounded-2xl border border-line bg-white p-5">
                    <h2 class="text-sm font-medium text-ink-soft">{{ t('About image') }}</h2>
                    <ImageUpload v-model="form.about_image" v-model:removed="form.remove_about_image" :current="company.about_image" :error="form.errors.about_image" />
                </section>
            </aside>
        </section>

        <section v-show="activeTab === 'highlights'" class="flex flex-col gap-6">
            <div class="rounded-2xl border border-line bg-white p-5 sm:p-6">
                <h2 class="text-base font-semibold text-ink">{{ t('Our values') }}</h2>
                <p class="mt-1 mb-5 text-xs text-muted">{{ t('Shown on the about page.') }}</p>
                <HighlightsEditor v-model="lists.values" kind="value" field="values" :errors="form.errors" :add-label="t('Add value')" />
            </div>
            <div class="rounded-2xl border border-line bg-white p-5 sm:p-6">
                <h2 class="text-base font-semibold text-ink">{{ t('Why choose us') }}</h2>
                <p class="mt-1 mb-5 text-xs text-muted">{{ t('Shown on the home and about pages.') }}</p>
                <HighlightsEditor v-model="lists.features" kind="feature" field="features" :icons="featureIcons" :errors="form.errors" :add-label="t('Add reason')" />
            </div>
            <div class="rounded-2xl border border-line bg-white p-5 sm:p-6">
                <h2 class="text-base font-semibold text-ink">{{ t('Statistics') }}</h2>
                <p class="mt-1 mb-5 text-xs text-muted">{{ t('For example: 25+ years of experience, 500+ projects.') }}</p>
                <HighlightsEditor v-model="lists.statistics" kind="statistic" field="statistics" :errors="form.errors" :add-label="t('Add statistic')" />
            </div>
        </section>

        <section v-show="activeTab === 'gallery'" class="rounded-2xl border border-line bg-white p-5 sm:p-6">
            <h2 class="text-base font-semibold text-ink">{{ t('Gallery') }}</h2>
            <p class="mt-1 mb-5 text-xs text-muted">{{ t('Projects and showroom photographs shown on the about page.') }}</p>
            <GalleryManager v-model:images="galleryImages" v-model:uploads="form.gallery_uploads" :errors="form.errors" />
        </section>

        <div class="sticky bottom-4 z-10 flex justify-end">
            <AppButton type="submit" size="lg" class="shadow-xl shadow-ink/10" :loading="form.processing">{{ t('Save changes') }}</AppButton>
        </div>
    </form>
</template>
