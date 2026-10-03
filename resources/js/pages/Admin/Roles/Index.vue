<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import { route } from 'ziggy-js';
import PageHeader from '../../../components/admin/PageHeader.vue';
import AppButton from '../../../components/common/AppButton.vue';
import Icon from '../../../components/common/Icon.vue';
import ModalDialog from '../../../components/common/ModalDialog.vue';
import { useTranslations } from '../../../composables/useTranslations';

const props = defineProps({
    roles: { type: Array, required: true },
    permissions: { type: Array, required: true },
});

const { t } = useTranslations();
const assignableCount = props.permissions.reduce((count, group) => count + group.permissions.length, 0);

const deleting = ref(null);
const isDeleting = ref(false);
const deleteError = ref(null);

function confirmDelete(role) {
    deleteError.value = null;
    deleting.value = role;
}

function destroy() {
    isDeleting.value = true;

    router.delete(route('admin.roles.destroy', deleting.value.id), {
        preserveScroll: true,
        onSuccess: () => (deleting.value = null),
        onError: (errors) => (deleteError.value = errors.role ?? null),
        onFinish: () => (isDeleting.value = false),
    });
}
</script>

<template>
    <Head :title="t('Roles & permissions')" />

    <PageHeader :title="t('Roles & permissions')" :eyebrow="t('Administration')" :description="t('A role is a set of permissions, such as “Sales staff”. Give each user the role that matches their work.')">
        <template #actions>
            <AppButton variant="secondary" :href="route('admin.users.index')">
                <Icon name="users" :size="16" />
                {{ t('Users') }}
            </AppButton>
            <AppButton :href="route('admin.roles.create')">
                <Icon name="plus" :size="16" />
                {{ t('Add role') }}
            </AppButton>
        </template>
    </PageHeader>

    <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
        <article
            v-for="role in roles"
            :key="role.id"
            class="flex flex-col gap-5 rounded-2xl border p-5 sm:p-6"
            :class="role.is_admin ? 'border-ink bg-ink text-white' : 'border-line bg-white'"
        >
            <div class="flex items-start justify-between gap-3">
                <div class="flex items-center gap-3">
                    <span class="flex size-11 shrink-0 items-center justify-center rounded-full" :class="role.is_admin ? 'bg-white/10 text-brass-300' : 'bg-sand-100 text-brass-700'">
                        <Icon :name="role.is_admin ? 'shield' : 'users'" :size="20" />
                    </span>
                    <div>
                        <h2 class="font-display text-2xl" :class="role.is_admin ? 'text-white' : 'text-ink'">{{ role.label }}</h2>
                        <p class="text-xs" :class="role.is_admin ? 'text-sand-300' : 'text-muted'">
                            {{ t(':count users', { count: role.users_count }) }}
                            <template v-if="role.is_built_in"> · {{ t('Built-in') }}</template>
                        </p>
                    </div>
                </div>
            </div>

            <div class="grow">
                <template v-if="role.is_admin">
                    <p class="text-sm text-sand-300">{{ t('Full access to everything, including users, roles and settings. This role cannot be changed.') }}</p>
                </template>
                <template v-else>
                    <div class="mb-2 flex items-center justify-between text-xs text-muted">
                        <span>{{ t(':count of :total permissions', { count: role.permissions.length, total: assignableCount }) }}</span>
                    </div>
                    <div class="h-1.5 overflow-hidden rounded-full bg-sand-100">
                        <div class="h-full rounded-full bg-brass-500" :style="{ width: `${(role.permissions.length / assignableCount) * 100}%` }" />
                    </div>
                    <ul class="mt-4 flex flex-wrap gap-1.5">
                        <li
                            v-for="group in permissions.filter((group) => group.permissions.some((permission) => role.permissions.includes(permission.name)))"
                            :key="group.group"
                            class="rounded-full bg-sand-100 px-2.5 py-1 text-xs text-ink-soft"
                        >
                            {{ group.group }}
                        </li>
                    </ul>
                </template>
            </div>

            <div v-if="!role.is_admin" class="flex items-center justify-between gap-3 border-t border-line pt-4">
                <Link :href="route('admin.roles.edit', role.id)" class="inline-flex items-center gap-1.5 text-sm font-medium text-ink hover:text-brass-700">
                    <Icon name="edit" :size="16" />
                    {{ t('Edit permissions') }}
                </Link>
                <button
                    v-if="!role.is_built_in"
                    type="button"
                    class="flex size-9 items-center justify-center rounded-lg text-muted hover:bg-danger/5 hover:text-danger"
                    :aria-label="t('Delete :name', { name: role.label })"
                    @click="confirmDelete(role)"
                >
                    <Icon name="trash" :size="16" />
                </button>
            </div>
        </article>
    </div>

    <ModalDialog :show="deleting !== null" :title="t('Delete “:name”', { name: deleting?.label ?? '' })" max-width="sm" @close="deleting = null">
        <p class="text-sm text-ink-soft">{{ t('This action cannot be undone.') }}</p>
        <p v-if="deleteError" class="mt-3 text-sm text-danger" role="alert">{{ deleteError }}</p>
        <template #footer>
            <AppButton variant="secondary" @click="deleting = null">{{ t('Cancel') }}</AppButton>
            <AppButton variant="danger" :loading="isDeleting" @click="destroy">{{ t('Delete') }}</AppButton>
        </template>
    </ModalDialog>
</template>
