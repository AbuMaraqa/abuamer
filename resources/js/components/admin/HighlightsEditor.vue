<script setup>
import { usePage } from '@inertiajs/vue3';
import { VueDraggable } from 'vue-draggable-plus';
import { useTranslations } from '../../composables/useTranslations';
import Icon from '../common/Icon.vue';
import TextareaInput from '../form/TextareaInput.vue';
import TextInput from '../form/TextInput.vue';

/**
 * Ordered, translatable company highlights: values, reasons to choose us (with an icon),
 * or statistics (with a figure such as "25+").
 */
const props = defineProps({
    kind: { type: String, required: true },
    field: { type: String, required: true },
    icons: { type: Array, default: () => [] },
    errors: { type: Object, default: () => ({}) },
    addLabel: { type: String, required: true },
});

const items = defineModel({ type: Array, required: true });

const { t } = useTranslations();
const locales = usePage().props.locale.supported;
let nextKey = 0;

function add() {
    items.value.push({
        key: `new-${++nextKey}`,
        id: null,
        icon: props.kind === 'feature' ? props.icons[0] : null,
        value: '',
        ...Object.fromEntries(locales.map(({ code }) => [code, { title: '', description: '' }])),
    });
}

function error(index, path) {
    return props.errors[`${props.field}.${index}.${path}`];
}
</script>

<template>
    <div class="flex flex-col gap-3">
        <VueDraggable v-if="items.length > 0" v-model="items" handle=".highlight-handle" :animation="150" tag="ol" class="flex flex-col gap-3">
            <li v-for="(item, index) in items" :key="item.key ?? item.id" class="flex gap-2 rounded-xl border border-line bg-sand-50 p-3 sm:p-4">
                <button type="button" class="highlight-handle flex w-7 shrink-0 cursor-grab items-start justify-center pt-2.5 text-sand-400 hover:text-ink" :aria-label="t('Drag to reorder')">
                    <Icon name="grip-vertical" :size="16" />
                </button>

                <div class="flex min-w-0 grow flex-col gap-3">
                    <div v-if="kind === 'feature'" class="flex flex-wrap gap-1.5" role="radiogroup" :aria-label="t('Icon')">
                        <button
                            v-for="icon in icons"
                            :key="icon"
                            type="button"
                            role="radio"
                            :aria-checked="item.icon === icon"
                            :aria-label="icon"
                            class="flex size-9 items-center justify-center rounded-lg border transition-colors"
                            :class="item.icon === icon ? 'border-brass-500 bg-brass-300/20 text-brass-700' : 'border-line bg-white text-muted hover:text-ink'"
                            @click="item.icon = icon"
                        >
                            <Icon :name="icon" :size="18" />
                        </button>
                    </div>

                    <div v-if="kind === 'statistic'" class="max-w-40">
                        <TextInput v-model="item.value" dir="ltr" :placeholder="t('Figure, e.g. 25+')" :aria-label="t('Figure')" :invalid="!!error(index, 'value')" />
                        <p v-if="error(index, 'value')" class="mt-1 text-xs text-danger">{{ error(index, 'value') }}</p>
                    </div>

                    <div class="grid gap-3 md:grid-cols-2">
                        <div v-for="locale in locales" :key="locale.code" :dir="locale.code === 'ar' ? 'rtl' : 'ltr'" :lang="locale.code" class="flex flex-col gap-2">
                            <TextInput
                                v-model="item[locale.code].title"
                                :placeholder="`${kind === 'statistic' ? t('Label') : t('Title')} (${locale.native})`"
                                :aria-label="`${t('Title')} (${locale.native})`"
                                :invalid="!!error(index, `${locale.code}.title`)"
                            />
                            <p v-if="error(index, `${locale.code}.title`)" class="text-xs text-danger">{{ error(index, `${locale.code}.title`) }}</p>
                            <TextareaInput
                                v-if="kind !== 'statistic'"
                                v-model="item[locale.code].description"
                                rows="2"
                                :placeholder="`${t('Description')} (${locale.native})`"
                                :aria-label="`${t('Description')} (${locale.native})`"
                            />
                        </div>
                    </div>
                </div>

                <button type="button" class="flex w-8 shrink-0 items-start justify-center pt-2.5 text-muted hover:text-danger" :aria-label="t('Remove')" @click="items.splice(index, 1)">
                    <Icon name="trash" :size="16" />
                </button>
            </li>
        </VueDraggable>

        <button type="button" class="inline-flex items-center gap-1.5 self-start rounded-lg border border-dashed border-sand-400 px-3 py-2 text-sm text-ink-soft hover:border-ink hover:text-ink" @click="add">
            <Icon name="plus" :size="16" />
            {{ addLabel }}
        </button>
    </div>
</template>
