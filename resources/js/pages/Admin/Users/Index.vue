<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import { onBeforeUnmount, ref, watch } from 'vue';
import { route } from 'ziggy-js';
import PageHeader from '../../../components/admin/PageHeader.vue';
import AppButton from '../../../components/common/AppButton.vue';
import Icon from '../../../components/common/Icon.vue';
import ModalDialog from '../../../components/common/ModalDialog.vue';
import PaginationLinks from '../../../components/common/PaginationLinks.vue';
import { useTranslations } from '../../../composables/useTranslations';

const props = defineProps({
    users: { type: Object, required: true },
    filters: { type: Object, required: true },
});

const { t } = useTranslations();

/* Search (server-side) ----------------------------------------------------- */

const term = ref(props.filters.q ?? '');
let timer = null;

watch(term, (value) => {
    clearTimeout(timer);
    timer = setTimeout(() => {
        router.get(route('admin.users.index'), { q: value || undefined }, { preserveState: true, preserveScroll: true, replace: true, only: ['users', 'filters'] });
    }, 350);
});

onBeforeUnmount(() => clearTimeout(timer));

/* Display helpers ---------------------------------------------------------- */

function initials(name) {
    return name
        .split(/\s+/)
        .filter(Boolean)
        .slice(0, 2)
        .map((word) => word[0])
        .join('')
        .toUpperCase();
}

function formatDate(iso) {
    return new Intl.DateTimeFormat(document.documentElement.lang, { dateStyle: 'medium', timeStyle: 'short' }).format(new Date(iso));
}

/* Deleting ----------------------------------------------------------------- */

const deleting = ref(null);
const isDeleting = ref(false);
const deleteError = ref(null);

function confirmDelete(user) {
    deleteError.value = null;
    deleting.value = user;
}

function destroy() {
    isDeleting.value = true;

    router.delete(route('admin.users.destroy', deleting.value.id), {
        preserveScroll: true,
        onSuccess: () => (deleting.value = null),
        onError: (errors) => (deleteError.value = errors.user ?? null),
        onFinish: () => (isDeleting.value = false),
    });
}
</script>

<template>
    <Head :title="t('Users')" />

    <PageHeader :title="t('Users')" :eyebrow="t('Administration')" :description="t('Staff accounts with access to the control panel. Each user’s role decides what they can see and change.')">
        <template #actions>
            <AppButton variant="secondary" :href="route('admin.roles.index')">
                <Icon name="shield" :size="16" />
                {{ t('Roles & permissions') }}
            </AppButton>
            <AppButton :href="route('admin.users.create')">
                <Icon name="plus" :size="16" />
                {{ t('Add user') }}
            </AppButton>
        </template>
    </PageHeader>

    <div class="relative mb-5 max-w-md">
        <Icon name="search" :size="18" class="pointer-events-none absolute start-3.5 top-1/2 -translate-y-1/2 text-muted" />
        <input
            v-model="term"
            type="search"
            :placeholder="t('Search by name or email…')"
            :aria-label="t('Search users')"
            class="w-full rounded-lg border border-line bg-white py-2.5 ps-11 pe-3 text-base placeholder:text-muted/80 sm:text-sm focus:border-brass-500 focus:ring-2 focus:ring-brass-500/20 focus:outline-none"
        />
    </div>

    <div class="overflow-hidden rounded-2xl border border-line bg-white">
        <table v-if="users.data.length > 0" class="w-full text-sm">
            <thead class="hidden border-b border-line bg-sand-50 text-xs text-muted md:table-header-group">
                <tr>
                    <th class="px-4 py-3 text-start font-medium">{{ t('User') }}</th>
                    <th class="px-4 py-3 text-start font-medium">{{ t('Role') }}</th>
                    <th class="px-4 py-3 text-start font-medium">{{ t('Status') }}</th>
                    <th class="px-4 py-3 text-start font-medium">{{ t('Last sign-in') }}</th>
                    <th class="w-24 px-4 py-3" />
                </tr>
            </thead>
            <tbody class="divide-y divide-line">
                <tr v-for="user in users.data" :key="user.id" class="flex flex-wrap items-center gap-x-3 gap-y-2 p-4 md:table-row md:p-0">
                    <td class="min-w-0 grow md:px-4 md:py-3">
                        <div class="flex items-center gap-3">
                            <span
                                class="flex size-10 shrink-0 items-center justify-center rounded-full text-sm font-semibold"
                                :class="user.is_active ? 'bg-ink text-white' : 'bg-sand-200 text-muted'"
                                aria-hidden="true"
                            >
                                {{ initials(user.name) }}
                            </span>
                            <div class="min-w-0">
                                <p class="flex items-center gap-2">
                                    <Link :href="route('admin.users.edit', user.id)" class="truncate font-medium text-ink hover:text-brass-700">{{ user.name }}</Link>
                                    <span v-if="user.is_current" class="shrink-0 rounded-full bg-brass-300/25 px-2 py-0.5 text-[11px] text-brass-700">{{ t('You') }}</span>
                                </p>
                                <p class="truncate text-xs text-muted" dir="ltr">{{ user.email }}</p>
                            </div>
                        </div>
                    </td>
                    <td class="md:px-4 md:py-3">
                        <span
                            v-if="user.role"
                            class="inline-flex items-center gap-1 rounded-full px-2.5 py-0.5 text-xs"
                            :class="user.role.is_admin ? 'bg-ink text-white' : 'bg-sand-100 text-ink-soft'"
                        >
                            <Icon v-if="user.role.is_admin" name="shield" :size="12" />
                            {{ user.role.label }}
                        </span>
                        <span v-else class="text-xs text-danger">{{ t('No role') }}</span>
                    </td>
                    <td class="md:px-4 md:py-3">
                        <span class="rounded-full px-2.5 py-0.5 text-xs" :class="user.is_active ? 'bg-success/10 text-success' : 'bg-sand-200 text-muted'">
                            {{ user.is_active ? t('Active') : t('Deactivated') }}
                        </span>
                    </td>
                    <td class="w-full text-xs text-muted md:w-auto md:px-4 md:py-3">
                        <time v-if="user.last_login_at" :datetime="user.last_login_at">{{ formatDate(user.last_login_at) }}</time>
                        <template v-else>{{ t('Never') }}</template>
                    </td>
                    <td class="ms-auto md:px-4 md:py-3">
                        <div class="flex items-center justify-end gap-1">
                            <Link :href="route('admin.users.edit', user.id)" class="flex size-9 items-center justify-center rounded-lg text-muted hover:bg-sand-100 hover:text-ink" :aria-label="t('Edit :name', { name: user.name })">
                                <Icon name="edit" :size="16" />
                            </Link>
                            <button
                                v-if="!user.is_current"
                                type="button"
                                class="flex size-9 items-center justify-center rounded-lg text-muted hover:bg-danger/5 hover:text-danger"
                                :aria-label="t('Delete :name', { name: user.name })"
                                @click="confirmDelete(user)"
                            >
                                <Icon name="trash" :size="16" />
                            </button>
                        </div>
                    </td>
                </tr>
            </tbody>
        </table>

        <div v-else class="flex flex-col items-center gap-3 px-4 py-16 text-center">
            <Icon name="users" :size="36" class="text-sand-300" />
            <p class="text-sm text-muted">{{ t('No users match this search.') }}</p>
        </div>
    </div>

    <PaginationLinks class="mt-8" :meta="users.meta" :links="users.links" />

    <ModalDialog :show="deleting !== null" :title="t('Delete “:name”', { name: deleting?.name ?? '' })" max-width="sm" @close="deleting = null">
        <p class="text-sm text-ink-soft">{{ t('They will no longer be able to sign in. To keep their account for later, deactivate it instead.') }}</p>
        <p v-if="deleteError" class="mt-3 text-sm text-danger" role="alert">{{ deleteError }}</p>
        <template #footer>
            <AppButton variant="secondary" @click="deleting = null">{{ t('Cancel') }}</AppButton>
            <AppButton variant="danger" :loading="isDeleting" @click="destroy">{{ t('Delete') }}</AppButton>
        </template>
    </ModalDialog>
</template>
