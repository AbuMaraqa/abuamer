<script setup>
import { useForm } from '@inertiajs/vue3';
import { route } from 'ziggy-js';
import { useTranslations } from '../../composables/useTranslations';
import AppButton from '../common/AppButton.vue';
import Icon from '../common/Icon.vue';
import FormField from '../form/FormField.vue';
import TextInput from '../form/TextInput.vue';

/**
 * Create or edit a role: its name and the permissions it grants, by control panel area.
 */
const props = defineProps({
    role: { type: Object, default: null },
    permissions: { type: Array, required: true },
});

const { t } = useTranslations();

const form = useForm({
    name: props.role?.name ?? '',
    permissions: [...(props.role?.permissions ?? [])],
});

const allPermissions = props.permissions.flatMap((group) => group.permissions);

function isChecked(name) {
    return form.permissions.includes(name);
}

/**
 * Checking a permission also checks the one it needs (adding products needs the product
 * list); unchecking a permission also unchecks the ones that need it.
 */
function toggle(permission, checked) {
    if (checked) {
        form.permissions = [...new Set([...form.permissions, permission.name, ...(permission.requires ? [permission.requires] : [])])];
    } else {
        const dependents = allPermissions.filter((other) => other.requires === permission.name).map((other) => other.name);
        form.permissions = form.permissions.filter((name) => name !== permission.name && !dependents.includes(name));
    }
}

function groupState(group) {
    const checked = group.permissions.filter((permission) => isChecked(permission.name)).length;

    return checked === 0 ? 'none' : checked === group.permissions.length ? 'all' : 'some';
}

function toggleGroup(group) {
    const names = group.permissions.map((permission) => permission.name);

    form.permissions = groupState(group) === 'all' ? form.permissions.filter((name) => !names.includes(name)) : [...new Set([...form.permissions, ...names])];
}

function submit() {
    if (props.role) {
        form.put(route('admin.roles.update', props.role.id), { preserveScroll: true });
    } else {
        form.post(route('admin.roles.store'), { preserveScroll: true });
    }
}
</script>

<template>
    <form class="form-with-actions grid gap-6 lg:grid-cols-[1fr_20rem]" @submit.prevent="submit">
        <div class="flex min-w-0 flex-col gap-6">
            <section class="rounded-2xl border border-line bg-white p-5 sm:p-6">
                <FormField
                    :label="t('Role name')"
                    for="role-name"
                    :error="form.errors.name"
                    :hint="role?.is_built_in ? t('Built-in roles keep their name.') : t('E.g. “Sales staff” or “Showroom manager”.')"
                    required
                >
                    <TextInput id="role-name" v-model="form.name" maxlength="60" required :disabled="role?.is_built_in" :invalid="!!form.errors.name" />
                </FormField>
            </section>

            <section class="rounded-2xl border border-line bg-white p-5 sm:p-6">
                <h2 class="text-base font-semibold text-ink">{{ t('Permissions') }}</h2>
                <p class="mt-1 mb-6 text-xs text-muted">{{ t('Users with this role see only the parts of the control panel they are allowed to use. Managing users and roles is reserved to administrators.') }}</p>

                <div class="grid gap-5 md:grid-cols-2">
                    <fieldset v-for="group in permissions" :key="group.group" class="rounded-xl border border-line p-4">
                        <legend class="sr-only">{{ group.group }}</legend>
                        <div class="mb-3 flex items-center justify-between gap-3 border-b border-line pb-3">
                            <p class="text-sm font-semibold text-ink">{{ group.group }}</p>
                            <button type="button" class="text-xs text-brass-700 hover:underline" @click="toggleGroup(group)">
                                {{ groupState(group) === 'all' ? t('Clear all') : t('Select all') }}
                            </button>
                        </div>
                        <div class="flex flex-col gap-2.5">
                            <label v-for="permission in group.permissions" :key="permission.name" class="flex cursor-pointer items-start gap-2.5 text-sm text-ink-soft">
                                <input
                                    type="checkbox"
                                    class="mt-0.5 size-4 shrink-0 accent-brass-500"
                                    :checked="isChecked(permission.name)"
                                    @change="toggle(permission, $event.target.checked)"
                                />
                                <span>{{ permission.label }}</span>
                            </label>
                        </div>
                    </fieldset>
                </div>
                <p v-if="form.errors.permissions" class="mt-3 text-sm text-danger">{{ form.errors.permissions }}</p>
            </section>
        </div>

        <aside class="flex flex-col gap-6">
            <section class="flex flex-col gap-2 rounded-2xl border border-line bg-white p-5">
                <p class="text-sm text-muted">{{ t('Permissions granted') }}</p>
                <p class="font-display text-4xl text-ink tabular-nums lining-nums">
                    {{ form.permissions.length }}<span class="text-xl text-muted"> / {{ allPermissions.length }}</span>
                </p>
                <p v-if="role" class="flex items-center gap-1.5 text-xs text-muted">
                    <Icon name="users" :size="14" />
                    {{ t(':count users have this role', { count: role.users_count }) }}
                </p>
            </section>

            <div class="form-actions lg:sticky lg:top-24">
                <AppButton type="submit" size="lg" class="grow" :loading="form.processing">
                    {{ role ? t('Save changes') : t('Add role') }}
                </AppButton>
                <AppButton variant="secondary" size="lg" :href="route('admin.roles.index')">{{ t('Cancel') }}</AppButton>
            </div>
        </aside>
    </form>
</template>
