<script setup>
import { Link, useForm, usePage } from '@inertiajs/vue3';
import { reactive } from 'vue';
import { route } from 'ziggy-js';
import { slugify } from '../../composables/useCategoryTree';
import { useTranslations } from '../../composables/useTranslations';
import AppButton from '../common/AppButton.vue';
import FormField from '../form/FormField.vue';
import LocaleHeading from '../form/LocaleHeading.vue';
import ImageUpload from '../form/ImageUpload.vue';
import TextareaInput from '../form/TextareaInput.vue';
import TextInput from '../form/TextInput.vue';
import ToggleSwitch from '../form/ToggleSwitch.vue';
import CategoryTreeSelect from './CategoryTreeSelect.vue';

/**
 * Create or edit a category in every language. Slugs follow the name until edited by hand.
 */
const props = defineProps({
    category: { type: Object, default: null },
    categories: { type: Array, required: true },
    parentId: { type: Number, default: null },
});

const { t } = useTranslations();
const locales = usePage().props.locale.supported;

const emptyTranslation = () => ({ name: '', slug: '', description: '', seo_title: '', seo_description: '' });

const form = useForm({
    parent_id: props.category?.parent_id ?? props.parentId,
    status: props.category?.status ?? true,
    ...Object.fromEntries(locales.map(({ code }) => [code, { ...emptyTranslation(), ...(props.category?.[code] ?? {}) }])),
    image: null,
    remove_image: false,
});

// A slug is "touched" once it no longer matches what the name would generate.
const slugTouched = reactive(Object.fromEntries(locales.map(({ code }) => [code, !!props.category?.[code]?.slug])));

function updateName(locale, name) {
    form[locale].name = name;

    if (!slugTouched[locale]) {
        form[locale].slug = slugify(name, locales.find(({ code }) => code === locale));
    }
}

function updateSlug(locale, slug) {
    form[locale].slug = slug;
    slugTouched[locale] = slug !== '';
}

function submit() {
    const options = { forceFormData: true, preserveScroll: true };

    if (props.category) {
        form.transform((data) => ({ ...data, _method: 'put' })).post(route('admin.categories.update', props.category.id), options);
    } else {
        form.post(route('admin.categories.store'), options);
    }
}
</script>

<template>
    <form class="form-with-actions grid gap-6 lg:grid-cols-[1fr_20rem]" @submit.prevent="submit">
        <div class="flex min-w-0 flex-col gap-6">
            <section class="rounded-2xl border border-line bg-white p-5 sm:p-6">
                <h2 class="mb-5 text-base font-semibold text-ink">{{ t('Content') }}</h2>

                <div class="grid gap-6 md:grid-cols-2">
                    <div v-for="locale in locales" :key="locale.code" :dir="locale.direction" :lang="locale.code" class="flex flex-col gap-4">
                        <LocaleHeading :locale="locale" />

                        <FormField :label="t('Name')" :for="`name-${locale.code}`" :error="form.errors[`${locale.code}.name`]" :required="locale.required">
                            <TextInput
                                :id="`name-${locale.code}`"
                                :model-value="form[locale.code].name"
                                :invalid="!!form.errors[`${locale.code}.name`]"
                                :required="locale.required"
                                @update:model-value="updateName(locale.code, $event)"
                            />
                        </FormField>

                        <FormField :label="t('Slug')" :for="`slug-${locale.code}`" :error="form.errors[`${locale.code}.slug`]" :hint="t('Used in the page address. Generated from the name.')">
                            <TextInput
                                :id="`slug-${locale.code}`"
                                :model-value="form[locale.code].slug"
                                :invalid="!!form.errors[`${locale.code}.slug`]"
                                class="font-mono text-xs"
                                @update:model-value="updateSlug(locale.code, $event)"
                            />
                        </FormField>

                        <FormField :label="t('Description')" :for="`description-${locale.code}`" :error="form.errors[`${locale.code}.description`]">
                            <TextareaInput :id="`description-${locale.code}`" v-model="form[locale.code].description" rows="5" :invalid="!!form.errors[`${locale.code}.description`]" />
                        </FormField>
                    </div>
                </div>
            </section>

            <details class="group rounded-2xl border border-line bg-white p-5 sm:p-6">
                <summary class="cursor-pointer list-none text-base font-semibold text-ink">
                    {{ t('Search engine optimisation') }}
                    <span class="block text-xs font-normal text-muted">{{ t('Optional. The name and description are used when left empty.') }}</span>
                </summary>

                <div class="mt-5 grid gap-6 md:grid-cols-2">
                    <div v-for="locale in locales" :key="locale.code" :dir="locale.direction" :lang="locale.code" class="flex flex-col gap-4">
                        <p class="text-xs font-semibold text-brass-600">{{ locale.native }}</p>
                        <FormField :label="t('SEO title')" :for="`seo-title-${locale.code}`" :error="form.errors[`${locale.code}.seo_title`]">
                            <TextInput :id="`seo-title-${locale.code}`" v-model="form[locale.code].seo_title" maxlength="255" />
                        </FormField>
                        <FormField :label="t('SEO description')" :for="`seo-description-${locale.code}`" :error="form.errors[`${locale.code}.seo_description`]">
                            <TextareaInput :id="`seo-description-${locale.code}`" v-model="form[locale.code].seo_description" rows="3" maxlength="500" />
                        </FormField>
                    </div>
                </div>
            </details>
        </div>

        <aside class="flex flex-col gap-6">
            <section class="flex flex-col gap-5 rounded-2xl border border-line bg-white p-5 sm:p-6">
                <FormField :label="t('Parent category')" for="parent" :error="form.errors.parent_id">
                    <CategoryTreeSelect
                        id="parent"
                        v-model="form.parent_id"
                        :nodes="categories"
                        :exclude-id="category?.id ?? null"
                        :invalid="!!form.errors.parent_id"
                        allow-root
                    />
                </FormField>

                <ToggleSwitch v-model="form.status" :label="t('Visible on website')" :description="t('Hiding a category also hides everything inside it.')" />
            </section>

            <section class="flex flex-col gap-3 rounded-2xl border border-line bg-white p-5 sm:p-6">
                <h2 class="text-sm font-medium text-ink-soft">{{ t('Image') }}</h2>
                <ImageUpload
                    v-model="form.image"
                    v-model:removed="form.remove_image"
                    :current="category?.image ?? null"
                    :error="form.errors.image"
                    :hint="t('At least 300 × 200 pixels, up to 5 MB.')"
                />
            </section>

            <div class="form-actions lg:sticky lg:top-24">
                <AppButton type="submit" size="lg" class="grow" :loading="form.processing">
                    {{ category ? t('Save changes') : t('Create category') }}
                </AppButton>
                <AppButton variant="secondary" size="lg" :href="route('admin.categories.index')">{{ t('Cancel') }}</AppButton>
            </div>
        </aside>
    </form>
</template>
