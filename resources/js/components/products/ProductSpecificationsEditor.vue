<script setup>
import { usePage } from '@inertiajs/vue3';
import { VueDraggable } from 'vue-draggable-plus';
import { useTranslations } from '../../composables/useTranslations';
import Icon from '../common/Icon.vue';
import TextInput from '../form/TextInput.vue';

/**
 * Ordered, translatable label/value pairs such as "Size: 60 × 120 cm".
 */
const props = defineProps({
    errors: { type: Object, default: () => ({}) },
});

const rows = defineModel({ type: Array, required: true });

const { t } = useTranslations();
const locales = usePage().props.locale.supported;
let nextKey = 0;

/**
 * Common attributes offered as one-click starting points, grouped by product line.
 */
const presetGroups = [
    {
        label: 'General',
        presets: [
            { en: 'Material', ar: 'المادة', he: 'חומר' },
            { en: 'Color', ar: 'اللون', he: 'צבע' },
            { en: 'Finish', ar: 'التشطيب', he: 'גימור' },
            { en: 'Country of origin', ar: 'بلد المنشأ', he: 'ארץ ייצור' },
            { en: 'Warranty', ar: 'الضمان', he: 'אחריות' },
        ],
    },
    {
        label: 'Tiles & marble',
        presets: [
            { en: 'Size', ar: 'المقاس', he: 'מידה' },
            { en: 'Thickness', ar: 'السماكة', he: 'עובי' },
            { en: 'Usage', ar: 'الاستخدام', he: 'שימוש' },
            { en: 'Slip resistance', ar: 'مقاومة الانزلاق', he: 'עמידות להחלקה' },
        ],
    },
    {
        label: 'Sanitary ware',
        presets: [
            { en: 'Dimensions', ar: 'الأبعاد', he: 'מידות' },
            { en: 'Installation', ar: 'طريقة التركيب', he: 'התקנה' },
            { en: 'Flush system', ar: 'نظام الدفق', he: 'מערכת הדחה' },
            { en: 'Outlet', ar: 'مخرج التصريف', he: 'יציאת ניקוז' },
            { en: 'Flow rate', ar: 'معدل التدفق', he: 'ספיקה' },
            { en: 'Cartridge', ar: 'الخرطوشة', he: 'מחסנית' },
        ],
    },
];

/**
 * Presets fill the names in the required languages only: a name in an optional language
 * would make its value required as well.
 */
function add(preset = null) {
    rows.value.push({
        key: `new-${++nextKey}`,
        id: null,
        ...Object.fromEntries(locales.map(({ code, required }) => [code, { label: (required && preset?.[code]) || '', value: '' }])),
    });
}

function remove(index) {
    rows.value.splice(index, 1);
}

function errorFor(index, locale, field) {
    return props.errors[`specifications.${index}.${locale}.${field}`];
}
</script>

<template>
    <div class="flex flex-col gap-4">
        <VueDraggable v-if="rows.length > 0" v-model="rows" handle=".specification-handle" :animation="150" tag="ol" class="flex flex-col gap-3">
            <li v-for="(row, index) in rows" :key="row.key ?? row.id" class="flex gap-2 rounded-xl border border-line bg-sand-50 p-3">
                <button type="button" class="specification-handle flex w-7 shrink-0 cursor-grab items-start justify-center pt-2.5 text-sand-400 hover:text-ink" :aria-label="t('Drag to reorder')">
                    <Icon name="grip-vertical" :size="16" />
                </button>

                <div class="grid min-w-0 grow gap-3 md:grid-cols-2">
                    <div v-for="locale in locales" :key="locale.code" :dir="locale.direction" :lang="locale.code" class="grid grid-cols-[2fr_3fr] gap-2">
                        <div>
                            <TextInput
                                v-model="row[locale.code].label"
                                :placeholder="`${t('Specification')} (${locale.native})`"
                                :aria-label="`${t('Specification name')} (${locale.native})`"
                                :invalid="!!errorFor(index, locale.code, 'label')"
                            />
                            <p v-if="errorFor(index, locale.code, 'label')" class="mt-1 text-xs text-danger">{{ errorFor(index, locale.code, 'label') }}</p>
                        </div>
                        <div>
                            <TextInput
                                v-model="row[locale.code].value"
                                :placeholder="t('Value')"
                                :aria-label="`${t('Specification value')} (${locale.native})`"
                                :invalid="!!errorFor(index, locale.code, 'value')"
                            />
                            <p v-if="errorFor(index, locale.code, 'value')" class="mt-1 text-xs text-danger">{{ errorFor(index, locale.code, 'value') }}</p>
                        </div>
                    </div>
                </div>

                <button type="button" class="flex w-8 shrink-0 items-start justify-center pt-2.5 text-muted hover:text-danger" :aria-label="t('Remove specification')" @click="remove(index)">
                    <Icon name="trash" :size="16" />
                </button>
            </li>
        </VueDraggable>

        <div class="flex flex-col gap-3">
            <button type="button" class="inline-flex items-center gap-1.5 self-start rounded-lg border border-dashed border-sand-400 px-3 py-2 text-sm text-ink-soft hover:border-ink hover:text-ink" @click="add()">
                <Icon name="plus" :size="16" />
                {{ t('Add specification') }}
            </button>
            <div class="flex flex-col gap-2 rounded-xl bg-sand-50 p-3">
                <p class="text-xs text-muted">{{ t('Quick add:') }}</p>
                <div v-for="group in presetGroups" :key="group.label" class="flex flex-wrap items-center gap-1.5">
                    <span class="w-full text-[11px] font-medium text-ink-soft sm:w-28 sm:shrink-0">{{ t(group.label) }}</span>
                    <button
                        v-for="preset in group.presets"
                        :key="preset.en"
                        type="button"
                        class="rounded-full border border-line bg-white px-3 py-1 text-xs text-ink-soft hover:border-sand-400 hover:text-ink"
                        @click="add(preset)"
                    >
                        {{ preset[$page.props.locale.current] ?? preset.en }}
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>
