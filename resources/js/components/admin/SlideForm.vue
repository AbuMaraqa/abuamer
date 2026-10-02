<script setup>
import { useForm, usePage } from '@inertiajs/vue3';
import { computed, onBeforeUnmount, ref, watch } from 'vue';
import { route } from 'ziggy-js';
import { useTranslations } from '../../composables/useTranslations';
import CategoryTreeSelect from '../category/CategoryTreeSelect.vue';
import AppButton from '../common/AppButton.vue';
import FormField from '../form/FormField.vue';
import ImageUpload from '../form/ImageUpload.vue';
import TextareaInput from '../form/TextareaInput.vue';
import TextInput from '../form/TextInput.vue';
import ToggleSwitch from '../form/ToggleSwitch.vue';

/**
 * Create or edit a slide, with a live preview in the control panel's language.
 */
const props = defineProps({
    slide: { type: Object, default: null },
    categories: { type: Array, required: true },
});

const { t } = useTranslations();
const page = usePage();
const locales = page.props.locale.supported;
const translatedFields = ['eyebrow', 'title', 'text', 'button_label', 'button_url'];

const form = useForm({
    status: props.slide?.status ?? true,
    link_type: props.slide?.link_type ?? 'none',
    category_id: props.slide?.category_id ?? null,
    ...Object.fromEntries(
        locales.map(({ code }) => [code, Object.fromEntries(translatedFields.map((field) => [field, props.slide?.[code]?.[field] ?? '']))]),
    ),
    image: null,
    mobile_image: null,
    remove_mobile_image: false,
});

const linkTypes = computed(() => [
    { value: 'none', label: t('No button') },
    { value: 'category', label: t('A category') },
    { value: 'custom', label: t('A custom link') },
]);

/* Live preview ------------------------------------------------------------- */

const localPreview = ref(null);

watch(
    () => form.image,
    (file) => {
        if (localPreview.value) {
            URL.revokeObjectURL(localPreview.value);
        }

        localPreview.value = file ? URL.createObjectURL(file) : null;
    },
);

const previewImage = computed(() => localPreview.value ?? props.slide?.image?.large ?? null);
const preview = computed(() => form[page.props.locale.current]);

watch(
    () => form.link_type,
    (type) => {
        if (type !== 'category') {
            form.category_id = null;
        }
    },
);

onBeforeUnmount(() => localPreview.value && URL.revokeObjectURL(localPreview.value));

function submit() {
    const options = { forceFormData: true, preserveScroll: true };

    if (props.slide) {
        form.transform((data) => ({ ...data, _method: 'put' })).post(route('admin.slides.update', props.slide.id), options);
    } else {
        form.post(route('admin.slides.store'), options);
    }
}
</script>

<template>
    <form class="grid gap-6 xl:grid-cols-[1fr_24rem]" @submit.prevent="submit">
        <div class="flex min-w-0 flex-col gap-6">
            <section class="rounded-2xl border border-line bg-white p-5 sm:p-6">
                <h2 class="mb-5 text-base font-semibold text-ink">{{ t('Content') }}</h2>

                <div class="grid gap-6 md:grid-cols-2">
                    <div v-for="locale in locales" :key="locale.code" :dir="locale.code === 'ar' ? 'rtl' : 'ltr'" :lang="locale.code" class="flex flex-col gap-4">
                        <p class="text-xs font-semibold text-brass-600">{{ locale.native }}</p>

                        <FormField :label="t('Small heading')" :for="`eyebrow-${locale.code}`" :error="form.errors[`${locale.code}.eyebrow`]" :hint="t('Optional, e.g. “New collection”.')">
                            <TextInput :id="`eyebrow-${locale.code}`" v-model="form[locale.code].eyebrow" maxlength="80" />
                        </FormField>

                        <FormField :label="t('Title')" :for="`title-${locale.code}`" :error="form.errors[`${locale.code}.title`]" required>
                            <TextInput :id="`title-${locale.code}`" v-model="form[locale.code].title" maxlength="120" required :invalid="!!form.errors[`${locale.code}.title`]" />
                        </FormField>

                        <FormField :label="t('Text')" :for="`text-${locale.code}`" :error="form.errors[`${locale.code}.text`]">
                            <TextareaInput :id="`text-${locale.code}`" v-model="form[locale.code].text" rows="3" maxlength="300" />
                        </FormField>

                        <FormField v-if="form.link_type !== 'none'" :label="t('Button text')" :for="`button-${locale.code}`" :error="form.errors[`${locale.code}.button_label`]" required>
                            <TextInput :id="`button-${locale.code}`" v-model="form[locale.code].button_label" maxlength="40" :invalid="!!form.errors[`${locale.code}.button_label`]" />
                        </FormField>

                        <FormField
                            v-if="form.link_type === 'custom'"
                            :label="t('Button link')"
                            :for="`url-${locale.code}`"
                            :error="form.errors[`${locale.code}.button_url`]"
                            :hint="t('https://… or a page of this website such as /:locale/about', { locale: locale.code })"
                            required
                        >
                            <TextInput :id="`url-${locale.code}`" v-model="form[locale.code].button_url" dir="ltr" :invalid="!!form.errors[`${locale.code}.button_url`]" />
                        </FormField>
                    </div>
                </div>
            </section>

            <section class="grid gap-6 rounded-2xl border border-line bg-white p-5 sm:p-6 md:grid-cols-[1.6fr_1fr]">
                <div class="flex flex-col gap-3">
                    <div>
                        <h2 class="text-base font-semibold text-ink">{{ t('Slide image') }} <span class="text-danger">*</span></h2>
                        <p class="mt-1 text-xs text-muted">{{ t('A wide, high-quality photograph, at least 1600 × 800 pixels. Keep the important part away from the text side.') }}</p>
                    </div>
                    <ImageUpload v-model="form.image" :current="slide?.image ?? null" :error="form.errors.image" />
                </div>
                <div class="flex flex-col gap-3">
                    <div>
                        <h2 class="text-base font-semibold text-ink">{{ t('Mobile image') }}</h2>
                        <p class="mt-1 text-xs text-muted">{{ t('Optional portrait image for phones, at least 750 × 1000 pixels.') }}</p>
                    </div>
                    <ImageUpload v-model="form.mobile_image" v-model:removed="form.remove_mobile_image" :current="slide?.mobile_image ?? null" :error="form.errors.mobile_image" />
                </div>
            </section>
        </div>

        <aside class="flex flex-col gap-6">
            <section class="flex flex-col gap-3">
                <h2 class="text-sm font-medium text-ink-soft">{{ t('Preview') }}</h2>
                <div class="relative aspect-video overflow-hidden rounded-2xl bg-ink text-white" :dir="page.props.locale.direction">
                    <img v-if="previewImage" :src="previewImage" alt="" class="absolute inset-0 h-full w-full object-cover" />
                    <div class="absolute inset-0 bg-gradient-to-t from-ink/90 via-ink/25 to-ink/40" />
                    <div class="absolute inset-0 from-ink/55 via-ink/10 to-transparent ltr:bg-gradient-to-r rtl:bg-gradient-to-l" />
                    <div class="absolute inset-x-0 bottom-0 flex flex-col gap-1.5 p-5">
                        <p v-if="preview.eyebrow" class="text-[10px] text-brass-300 ltr:tracking-[0.2em] ltr:uppercase">{{ preview.eyebrow }}</p>
                        <p class="font-display text-xl leading-tight">{{ preview.title || t('Slide title') }}</p>
                        <p v-if="preview.text" class="line-clamp-2 text-[11px] text-white/80">{{ preview.text }}</p>
                        <span v-if="form.link_type !== 'none' && preview.button_label" class="mt-1.5 self-start rounded bg-white px-2.5 py-1 text-[10px] font-medium text-ink">
                            {{ preview.button_label }}
                        </span>
                    </div>
                </div>
            </section>

            <section class="flex flex-col gap-5 rounded-2xl border border-line bg-white p-5">
                <fieldset class="flex flex-col gap-2">
                    <legend class="mb-1 text-sm font-medium text-ink-soft">{{ t('The button leads to') }}</legend>
                    <label v-for="type in linkTypes" :key="type.value" class="flex items-center gap-2 text-sm text-ink-soft">
                        <input v-model="form.link_type" type="radio" name="link_type" :value="type.value" class="accent-brass-500" />
                        {{ type.label }}
                    </label>
                </fieldset>

                <FormField v-if="form.link_type === 'category'" :label="t('Category')" for="slide-category" :error="form.errors.category_id" required>
                    <CategoryTreeSelect id="slide-category" v-model="form.category_id" :nodes="categories" :invalid="!!form.errors.category_id" />
                </FormField>

                <ToggleSwitch v-model="form.status" :label="t('Visible on website')" />
            </section>

            <div class="flex gap-3 xl:sticky xl:top-24">
                <AppButton type="submit" size="lg" class="grow" :loading="form.processing">
                    {{ slide ? t('Save changes') : t('Add slide') }}
                </AppButton>
                <AppButton variant="secondary" size="lg" :href="route('admin.slides.index')">{{ t('Cancel') }}</AppButton>
            </div>
        </aside>
    </form>
</template>
