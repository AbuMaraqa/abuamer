<script setup>
import { Head, Link } from '@inertiajs/vue3';
import { route } from 'ziggy-js';
import PageHeader from '../../../components/admin/PageHeader.vue';
import Icon from '../../../components/common/Icon.vue';
import PaginationLinks from '../../../components/common/PaginationLinks.vue';
import { useTranslations } from '../../../composables/useTranslations';

defineProps({
    messages: { type: Object, required: true },
});

const { t } = useTranslations();

function formatDate(iso) {
    return new Intl.DateTimeFormat(document.documentElement.lang, { dateStyle: 'medium', timeStyle: 'short' }).format(new Date(iso));
}
</script>

<template>
    <Head :title="t('Messages')" />

    <PageHeader :title="t('Messages')" :eyebrow="t('Contact form')" />

    <div class="overflow-hidden rounded-2xl border border-line bg-white">
        <ul v-if="messages.data.length > 0" class="divide-y divide-line">
            <li v-for="message in messages.data" :key="message.id">
                <Link :href="route('admin.messages.show', message.id)" class="flex items-start gap-4 px-5 py-4 transition-colors hover:bg-sand-50">
                    <span class="mt-2 size-2 shrink-0 rounded-full" :class="message.read ? 'bg-transparent' : 'bg-brass-500'" aria-hidden="true" />
                    <span v-if="!message.read" class="sr-only">{{ t('Unread') }}</span>
                    <div class="min-w-0 grow">
                        <div class="flex flex-wrap items-baseline justify-between gap-x-4 gap-y-1">
                            <p class="truncate text-sm" :class="message.read ? 'text-ink-soft' : 'font-semibold text-ink'">{{ message.name }}</p>
                            <time :datetime="message.created_at" class="shrink-0 text-xs text-muted">{{ formatDate(message.created_at) }}</time>
                        </div>
                        <p v-if="message.subject" class="mt-0.5 truncate text-sm text-ink-soft">{{ message.subject }}</p>
                        <p class="mt-0.5 truncate text-xs text-muted">{{ message.excerpt }}</p>
                    </div>
                </Link>
            </li>
        </ul>

        <div v-else class="flex flex-col items-center gap-3 px-4 py-16 text-center">
            <Icon name="mail" :size="36" class="text-sand-300" />
            <p class="text-sm text-muted">{{ t('No messages yet.') }}</p>
        </div>
    </div>

    <PaginationLinks class="mt-8" :meta="messages" :links="{ prev: messages.prev_page_url, next: messages.next_page_url }" />
</template>
