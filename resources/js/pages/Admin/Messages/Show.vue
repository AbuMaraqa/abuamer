<script setup>
import { Head, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import { route } from 'ziggy-js';
import PageHeader from '../../../components/admin/PageHeader.vue';
import AppButton from '../../../components/common/AppButton.vue';
import Icon from '../../../components/common/Icon.vue';
import ModalDialog from '../../../components/common/ModalDialog.vue';
import { useTranslations } from '../../../composables/useTranslations';

const props = defineProps({
    message: { type: Object, required: true },
});

const { t } = useTranslations();
const isConfirmingDelete = ref(false);

const receivedAt = new Intl.DateTimeFormat(document.documentElement.lang, { dateStyle: 'full', timeStyle: 'short' }).format(new Date(props.message.created_at));

function destroy() {
    router.delete(route('admin.messages.destroy', props.message.id));
}
</script>

<template>
    <Head :title="message.subject || message.name" />

    <PageHeader :title="message.subject || t('Message from :name', { name: message.name })" :eyebrow="t('Messages')">
        <template #actions>
            <AppButton variant="secondary" :href="route('admin.messages.index')">{{ t('Back to messages') }}</AppButton>
        </template>
    </PageHeader>

    <div class="grid gap-6 lg:grid-cols-[1fr_18rem]">
        <article class="rounded-2xl border border-line bg-white p-6 sm:p-8">
            <p class="text-xs text-muted">{{ receivedAt }}</p>
            <p class="mt-5 text-base leading-loose whitespace-pre-line text-ink" dir="auto">{{ message.message }}</p>
        </article>

        <aside class="flex flex-col gap-4 rounded-2xl border border-line bg-white p-6 text-sm">
            <div>
                <p class="text-xs text-muted">{{ t('Name') }}</p>
                <p class="mt-0.5 text-ink">{{ message.name }}</p>
            </div>
            <div>
                <p class="text-xs text-muted">{{ t('Email') }}</p>
                <a :href="`mailto:${message.email}`" class="mt-0.5 block break-all text-brass-700 hover:underline">{{ message.email }}</a>
            </div>
            <div v-if="message.phone">
                <p class="text-xs text-muted">{{ t('Phone') }}</p>
                <a :href="`tel:${message.phone}`" dir="ltr" class="mt-0.5 block text-brass-700 hover:underline">{{ message.phone }}</a>
            </div>

            <div class="mt-2 flex flex-col gap-2 border-t border-line pt-4">
                <a
                    :href="`mailto:${message.email}?subject=${encodeURIComponent('Re: ' + (message.subject || ''))}`"
                    class="inline-flex w-full items-center justify-center gap-2 rounded-lg bg-ink px-4 py-2.5 text-sm font-medium text-white transition-colors hover:bg-ink-soft"
                >
                    <Icon name="mail" :size="16" />
                    {{ t('Reply by email') }}
                </a>
                <button type="button" class="inline-flex items-center justify-center gap-2 rounded-lg px-4 py-2.5 text-sm text-danger hover:bg-danger/5" @click="isConfirmingDelete = true">
                    <Icon name="trash" :size="16" />
                    {{ t('Delete') }}
                </button>
            </div>
        </aside>
    </div>

    <ModalDialog :show="isConfirmingDelete" :title="t('Delete this message?')" max-width="sm" @close="isConfirmingDelete = false">
        <p class="text-sm text-ink-soft">{{ t('This action cannot be undone.') }}</p>
        <template #footer>
            <AppButton variant="secondary" @click="isConfirmingDelete = false">{{ t('Cancel') }}</AppButton>
            <AppButton variant="danger" @click="destroy">{{ t('Delete') }}</AppButton>
        </template>
    </ModalDialog>
</template>
