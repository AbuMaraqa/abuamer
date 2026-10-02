<script setup>
import { useForm, usePage } from '@inertiajs/vue3';
import { reactive, ref } from 'vue';
import { route } from 'ziggy-js';
import { slugify } from '../../composables/useCategoryTree';
import { useTranslations } from '../../composables/useTranslations';
import CategoryTreeSelect from '../category/CategoryTreeSelect.vue';
import AppButton from '../common/AppButton.vue';
import Icon from '../common/Icon.vue';
import FormField from '../form/FormField.vue';
import LocaleHeading from '../form/LocaleHeading.vue';
import ImageUpload from '../form/ImageUpload.vue';
import TextareaInput from '../form/TextareaInput.vue';
import TextInput from '../form/TextInput.vue';
import ToggleSwitch from '../form/ToggleSwitch.vue';
import GalleryManager from './GalleryManager.vue';
import ProductSpecificationsEditor from './ProductSpecificationsEditor.vue';

/**
 * Create or edit a product. The form always submits the complete desired state
 * (translations, ordered specifications, kept gallery images and new uploads).
 */
const props = defineProps({
    product: { type: Object, default: null },
    categories: { type: Array, required: true },
    categoryId: { type: Number, default: null },
});

const emit = defineEmits(['delete']);

const { t } = useTranslations();
const locales = usePage().props.locale.supported;
const translatedFields = ['name', 'slug', 'short_description', 'description', 'seo_title', 'seo_description'];

const form = useForm({
    category_id: props.product?.category_id ?? props.categoryId,
    sku: props.product?.sku ?? '',
    status: props.product?.status ?? true,
    featured: props.product?.featured ?? false,
    sort_order: props.product?.sort_order ?? 0,
    ...Object.fromEntries(
        locales.map(({ code }) => [code, Object.fromEntries(translatedFields.map((field) => [field, props.product?.[code]?.[field] ?? '']))]),
    ),
    main_image: null,
    remove_main_image: false,
    gallery_uploads: [],
});

const specifications = ref((props.product?.specifications ?? []).map((specification) => ({ ...specification })));
const galleryImages = ref([...(props.product?.gallery ?? [])]);
const slugTouched = reactive(Object.fromEntries(locales.map(({ code }) => [code, !!props.product?.[code]?.slug])));

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
    form.transform((data) => ({
        ...data,
        specifications: specifications.value.map(({ id, ...translations }) => ({
            id,
            ...Object.fromEntries(locales.map(({ code }) => [code, translations[code]])),
        })),
        gallery: galleryImages.value.map((image) => image.id),
        ...(props.product ? { _method: 'put' } : {}),
    }));

    const url = props.product ? route('admin.products.update', props.product.id) : route('admin.products.store');

    form.post(url, {
        forceFormData: true,
        preserveScroll: true,
        // Keep typed input only when validation fails; after a save the page remounts from the
        // server's state, so newly uploaded images become regular (kept) gallery images.
        preserveState: 'errors',
    });
}
</script>

<template>
    <form class="grid gap-6 lg:grid-cols-[1fr_20rem]" @submit.prevent="submit">
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

                        <FormField :label="t('Short description')" :for="`short-${locale.code}`" :error="form.errors[`${locale.code}.short_description`]">
                            <TextareaInput :id="`short-${locale.code}`" v-model="form[locale.code].short_description" rows="2" maxlength="500" />
                        </FormField>

                        <FormField :label="t('Description')" :for="`description-${locale.code}`" :error="form.errors[`${locale.code}.description`]">
                            <TextareaInput :id="`description-${locale.code}`" v-model="form[locale.code].description" rows="7" />
                        </FormField>
                    </div>
                </div>
            </section>

            <section class="rounded-2xl border border-line bg-white p-5 sm:p-6">
                <h2 class="text-base font-semibold text-ink">{{ t('Specifications') }}</h2>
                <p class="mt-1 mb-5 text-xs text-muted">{{ t('Shown as a table on the product page, in this order.') }}</p>
                <ProductSpecificationsEditor v-model="specifications" :errors="form.errors" />
            </section>

            <section class="grid gap-6 rounded-2xl border border-line bg-white p-5 sm:p-6 md:grid-cols-[16rem_1fr]">
                <div class="flex flex-col gap-3">
                    <h2 class="text-base font-semibold text-ink">{{ t('Main image') }}</h2>
                    <ImageUpload
                        v-model="form.main_image"
                        v-model:removed="form.remove_main_image"
                        :current="product?.main_image ?? null"
                        :error="form.errors.main_image"
                    />
                </div>
                <div class="flex min-w-0 flex-col gap-3">
                    <h2 class="text-base font-semibold text-ink">{{ t('Gallery') }}</h2>
                    <GalleryManager v-model:images="galleryImages" v-model:uploads="form.gallery_uploads" :errors="form.errors" />
                </div>
            </section>

            <details class="rounded-2xl border border-line bg-white p-5 sm:p-6">
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
                <FormField :label="t('Category')" for="category" :error="form.errors.category_id" required>
                    <CategoryTreeSelect id="category" v-model="form.category_id" :nodes="categories" :invalid="!!form.errors.category_id" />
                </FormField>

                <FormField :label="t('Code (SKU)')" for="sku" :error="form.errors.sku">
                    <TextInput id="sku" v-model="form.sku" dir="ltr" class="font-mono uppercase" maxlength="64" :invalid="!!form.errors.sku" />
                </FormField>

                <FormField :label="t('Display order')" for="sort-order" :error="form.errors.sort_order" :hint="t('Lower numbers are shown first.')">
                    <TextInput id="sort-order" v-model.number="form.sort_order" type="number" min="0" />
                </FormField>

                <ToggleSwitch v-model="form.status" :label="t('Visible on website')" />
                <ToggleSwitch v-model="form.featured" :label="t('Featured')" :description="t('Shown on the home page.')" />
            </section>

            <div class="flex flex-col gap-3 lg:sticky lg:top-24">
                <div class="flex gap-3">
                    <AppButton type="submit" size="lg" class="grow" :loading="form.processing">
                        {{ product ? t('Save changes') : t('Create product') }}
                    </AppButton>
                    <AppButton variant="secondary" size="lg" :href="route('admin.products.index')">{{ t('Cancel') }}</AppButton>
                </div>

                <div v-if="product" class="flex items-center justify-between px-1 text-sm">
                    <a :href="product.url" target="_blank" rel="noopener" class="inline-flex items-center gap-1.5 text-muted hover:text-ink">
                        <Icon name="globe" :size="16" />
                        {{ t('View on website') }}
                    </a>
                    <button v-if="$page.props.auth.user.can['products.delete']" type="button" class="inline-flex items-center gap-1.5 text-danger hover:underline" @click="emit('delete')">
                        <Icon name="trash" :size="16" />
                        {{ t('Delete') }}
                    </button>
                </div>
            </div>
        </aside>
    </form>
</template>
