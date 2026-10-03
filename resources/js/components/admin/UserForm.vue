<script setup>
import { useForm } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import { route } from 'ziggy-js';
import { useTranslations } from '../../composables/useTranslations';
import AppButton from '../common/AppButton.vue';
import Icon from '../common/Icon.vue';
import FormField from '../form/FormField.vue';
import TextInput from '../form/TextInput.vue';
import ToggleSwitch from '../form/ToggleSwitch.vue';

/**
 * Create or edit a staff account: identity, password, role and access.
 */
const props = defineProps({
    user: { type: Object, default: null },
    roles: { type: Array, required: true },
    // Assignable permissions by area, to show what the chosen role allows.
    permissions: { type: Array, required: true },
});

const { t } = useTranslations();

const form = useForm({
    name: props.user?.name ?? '',
    email: props.user?.email ?? '',
    password: '',
    password_confirmation: '',
    role_id: props.user?.role?.id ?? props.roles.find((role) => !role.is_admin)?.id ?? null,
    is_active: props.user?.is_active ?? true,
});

const isSelf = computed(() => props.user?.is_current ?? false);
const isPasswordShown = ref(false);
const selectedRole = computed(() => props.roles.find((role) => role.id === form.role_id) ?? null);

function allows(permission) {
    return selectedRole.value?.is_admin || (selectedRole.value?.permissions ?? []).includes(permission);
}

/**
 * A random password of letters and digits, shown so it can be passed on to the new user.
 */
function generatePassword() {
    // Without look-alike characters (l, 1, O, 0), so it can be read out or copied by hand.
    const alphabet = 'abcdefghjkmnpqrstuvwxyzABCDEFGHJKMNPQRSTUVWXYZ23456789';
    let password = '';

    // The server requires a letter and a digit.
    while (!/[a-z]/i.test(password) || !/\d/.test(password)) {
        password = Array.from(crypto.getRandomValues(new Uint32Array(14)), (value) => alphabet[value % alphabet.length]).join('');
    }

    form.password = password;
    form.password_confirmation = password;
    isPasswordShown.value = true;
}

function submit() {
    if (props.user) {
        form.put(route('admin.users.update', props.user.id), { preserveScroll: true });
    } else {
        form.post(route('admin.users.store'), { preserveScroll: true });
    }
}
</script>

<template>
    <form class="form-with-actions grid gap-6 lg:grid-cols-[1fr_20rem]" @submit.prevent="submit">
        <div class="flex min-w-0 flex-col gap-6">
            <section class="rounded-2xl border border-line bg-white p-5 sm:p-6">
                <h2 class="mb-5 text-base font-semibold text-ink">{{ t('Account') }}</h2>

                <div class="grid gap-5 md:grid-cols-2">
                    <FormField :label="t('Name')" for="user-name" :error="form.errors.name" required>
                        <TextInput id="user-name" v-model="form.name" maxlength="255" autocomplete="off" required :invalid="!!form.errors.name" />
                    </FormField>

                    <FormField :label="t('Email')" for="user-email" :error="form.errors.email" :hint="t('Used to sign in.')" required>
                        <TextInput id="user-email" v-model="form.email" type="email" dir="ltr" maxlength="255" autocomplete="off" required :invalid="!!form.errors.email" />
                    </FormField>

                    <FormField
                        :label="user ? t('New password') : t('Password')"
                        for="user-password"
                        :error="form.errors.password"
                        :hint="user ? t('Leave empty to keep the current password.') : t('At least 8 characters, with letters and numbers.')"
                        :required="!user"
                    >
                        <div class="relative">
                            <TextInput
                                id="user-password"
                                v-model="form.password"
                                :type="isPasswordShown ? 'text' : 'password'"
                                dir="ltr"
                                autocomplete="new-password"
                                class="pe-11!"
                                :required="!user"
                                :invalid="!!form.errors.password"
                            />
                            <button
                                type="button"
                                class="absolute end-1 top-1/2 flex size-9 -translate-y-1/2 items-center justify-center rounded-lg text-muted hover:text-ink"
                                :aria-label="isPasswordShown ? t('Hide password') : t('Show password')"
                                @click="isPasswordShown = !isPasswordShown"
                            >
                                <Icon :name="isPasswordShown ? 'eye-off' : 'eye'" :size="18" />
                            </button>
                        </div>
                    </FormField>

                    <FormField :label="t('Confirm password')" for="user-password-confirmation" :required="!user || !!form.password">
                        <TextInput
                            id="user-password-confirmation"
                            v-model="form.password_confirmation"
                            :type="isPasswordShown ? 'text' : 'password'"
                            dir="ltr"
                            autocomplete="new-password"
                            :required="!user || !!form.password"
                        />
                    </FormField>
                </div>

                <button type="button" class="mt-4 inline-flex items-center gap-1.5 text-sm text-brass-700 hover:underline" @click="generatePassword">
                    <Icon name="sparkles" :size="16" />
                    {{ t('Generate a strong password') }}
                </button>
            </section>

            <section class="rounded-2xl border border-line bg-white p-5 sm:p-6">
                <div class="mb-5 flex flex-wrap items-end justify-between gap-3">
                    <div>
                        <h2 class="text-base font-semibold text-ink">{{ t('Role') }}</h2>
                        <p class="mt-1 text-xs text-muted">{{ t('The role decides which parts of the control panel this user can see and change.') }}</p>
                    </div>
                    <a :href="route('admin.roles.index')" class="text-xs text-brass-700 hover:underline">{{ t('Manage roles') }}</a>
                </div>

                <fieldset :disabled="isSelf" class="grid gap-3 sm:grid-cols-2 xl:grid-cols-3">
                    <legend class="sr-only">{{ t('Role') }}</legend>
                    <label
                        v-for="role in roles"
                        :key="role.id"
                        class="flex cursor-pointer flex-col gap-1.5 rounded-xl border p-4 transition-colors has-[:disabled]:cursor-not-allowed has-[:focus-visible]:outline-2 has-[:focus-visible]:outline-offset-2 has-[:focus-visible]:outline-brass-500"
                        :class="form.role_id === role.id ? 'border-brass-500 bg-brass-300/10' : 'border-line hover:border-sand-400'"
                    >
                        <span class="flex items-center justify-between gap-3">
                            <span class="flex items-center gap-2 text-sm font-medium text-ink">
                                <Icon v-if="role.is_admin" name="shield" :size="16" class="text-brass-600" />
                                {{ role.label }}
                            </span>
                            <input v-model="form.role_id" type="radio" name="role" :value="role.id" class="size-4 accent-brass-500" />
                        </span>
                        <span class="text-xs text-muted">
                            {{ role.is_admin ? t('Full access, including users and settings') : t(':count permissions', { count: role.permissions.length }) }}
                        </span>
                    </label>
                </fieldset>
                <p v-if="form.errors.role_id" class="mt-3 text-sm text-danger" role="alert">{{ form.errors.role_id }}</p>
                <p v-else-if="isSelf" class="mt-3 text-xs text-muted">{{ t('You cannot change your own role.') }}</p>

                <div v-if="selectedRole" class="mt-6 border-t border-line pt-5">
                    <p class="mb-4 text-sm font-medium text-ink-soft">{{ t('What :role can do', { role: selectedRole.label }) }}</p>
                    <div class="grid gap-x-8 gap-y-5 md:grid-cols-2">
                        <div v-for="group in permissions" :key="group.group">
                            <p class="mb-2 text-xs font-semibold text-ink">{{ group.group }}</p>
                            <ul class="flex flex-col gap-1.5">
                                <li v-for="permission in group.permissions" :key="permission.name" class="flex items-start gap-2 text-xs" :class="allows(permission.name) ? 'text-ink-soft' : 'text-muted/60 line-through'">
                                    <Icon :name="allows(permission.name) ? 'check' : 'x'" :size="14" class="mt-px shrink-0" :class="allows(permission.name) ? 'text-success' : ''" />
                                    {{ permission.label }}
                                </li>
                            </ul>
                        </div>
                        <div v-if="selectedRole.is_admin">
                            <p class="mb-2 text-xs font-semibold text-ink">{{ t('Administration') }}</p>
                            <p class="flex items-start gap-2 text-xs text-ink-soft">
                                <Icon name="check" :size="14" class="mt-px shrink-0 text-success" />
                                {{ t('Manage users and roles') }}
                            </p>
                        </div>
                    </div>
                </div>
            </section>
        </div>

        <aside class="flex flex-col gap-6">
            <section class="flex flex-col gap-3 rounded-2xl border border-line bg-white p-5">
                <ToggleSwitch v-model="form.is_active" :label="t('Active account')" :description="t('Deactivated users cannot sign in, but their account is kept.')" :disabled="isSelf" />
                <p v-if="form.errors.is_active" class="text-sm text-danger" role="alert">{{ form.errors.is_active }}</p>
            </section>

            <div class="form-actions lg:sticky lg:top-24">
                <AppButton type="submit" size="lg" class="grow" :loading="form.processing">
                    {{ user ? t('Save changes') : t('Add user') }}
                </AppButton>
                <AppButton variant="secondary" size="lg" :href="route('admin.users.index')">{{ t('Cancel') }}</AppButton>
            </div>
        </aside>
    </form>
</template>
