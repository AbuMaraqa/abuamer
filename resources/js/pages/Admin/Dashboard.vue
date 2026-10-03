<script setup>
import { Head, Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import { route } from 'ziggy-js';
import PageHeader from '../../components/admin/PageHeader.vue';
import Icon from '../../components/common/Icon.vue';
import { useTranslations } from '../../composables/useTranslations';

const props = defineProps({
    stats: { type: Object, required: true },
});

const { t } = useTranslations();
const can = usePage().props.auth.user.can;

const cards = computed(() => [
    { label: t('Categories'), value: props.stats.categories, icon: 'folder-tree', href: can['categories.view'] ? route('admin.categories.index') : null },
    { label: t('Products'), value: props.stats.products, icon: 'box', href: can['products.view'] ? route('admin.products.index') : null },
    { label: t('Hidden products'), value: props.stats.hiddenProducts, icon: 'eye-off', href: can['products.view'] ? route('admin.products.index', { status: 'inactive' }) : null },
    { label: t('Unread messages'), value: props.stats.unreadMessages, icon: 'mail', href: can['messages.manage'] ? route('admin.messages.index') : null },
]);

const shortcuts = computed(() =>
    [
        can['products.create'] && { label: t('Add product'), icon: 'plus', href: route('admin.products.create') },
        can['categories.create'] && { label: t('Add category'), icon: 'folder', href: route('admin.categories.create') },
        can['brands.manage'] && { label: t('Add brand'), icon: 'tag', href: route('admin.brands.create') },
        can['company.manage'] && { label: t('Edit company content'), icon: 'building', href: route('admin.company.edit') },
        can['settings.manage'] && { label: t('Contact details and SEO'), icon: 'settings', href: route('admin.settings.edit') },
    ].filter(Boolean),
);
</script>

<template>
    <Head :title="t('Dashboard')" />

    <PageHeader :title="t('Welcome, :name', { name: $page.props.auth.user.name })" :eyebrow="t('Control panel')" />

    <!-- Two compact tiles per row on phones (icon above the figure); a row of four on wide screens. -->
    <div class="grid grid-cols-2 gap-3 sm:gap-4 xl:grid-cols-4">
        <component
            :is="card.href ? Link : 'div'"
            v-for="card in cards"
            :key="card.label"
            :href="card.href ?? undefined"
            class="flex flex-col-reverse items-start gap-3 rounded-2xl border border-line bg-white p-4 transition-colors sm:flex-row sm:items-center sm:justify-between sm:gap-4 sm:p-6"
            :class="{ 'hover:border-sand-400': card.href }"
        >
            <div class="min-w-0">
                <p class="text-sm leading-snug text-muted">{{ card.label }}</p>
                <p class="mt-1 font-display text-3xl text-ink tabular-nums lining-nums sm:mt-2 sm:text-4xl">{{ card.value }}</p>
            </div>
            <span class="flex size-10 shrink-0 items-center justify-center rounded-full bg-sand-100 text-brass-700 sm:size-12">
                <Icon :name="card.icon" :size="20" />
            </span>
        </component>
    </div>

    <section v-if="shortcuts.length > 0" class="mt-10">
        <h2 class="mb-4 text-sm font-semibold text-ink">{{ t('Shortcuts') }}</h2>
        <div class="grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
            <Link
                v-for="shortcut in shortcuts"
                :key="shortcut.href"
                :href="shortcut.href"
                class="flex items-center gap-3 rounded-xl border border-line bg-white px-4 py-3.5 text-sm text-ink-soft transition-colors hover:border-ink hover:text-ink"
            >
                <Icon :name="shortcut.icon" :size="18" class="text-brass-600" />
                {{ shortcut.label }}
            </Link>
        </div>
    </section>
</template>
