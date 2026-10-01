<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import { route } from 'ziggy-js';
import AppButton from '../../components/common/AppButton.vue';
import FormField from '../../components/form/FormField.vue';
import TextInput from '../../components/form/TextInput.vue';
import { useTranslations } from '../../composables/useTranslations';

const { t } = useTranslations();

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

function submit() {
    form.post(route('login.store'), {
        onFinish: () => form.reset('password'),
    });
}
</script>

<template>
    <Head :title="t('Log in')" />

    <div class="flex flex-col gap-8">
        <div class="flex flex-col gap-2">
            <p class="eyebrow">{{ t('Control panel') }}</p>
            <h1 class="font-display text-3xl text-ink">{{ t('Welcome back') }}</h1>
        </div>

        <form class="flex flex-col gap-5" @submit.prevent="submit">
            <FormField :label="t('Email')" for="email" :error="form.errors.email">
                <TextInput id="email" v-model="form.email" type="email" autocomplete="username" dir="ltr" required autofocus :invalid="!!form.errors.email" />
            </FormField>

            <FormField :label="t('Password')" for="password" :error="form.errors.password">
                <TextInput id="password" v-model="form.password" type="password" autocomplete="current-password" dir="ltr" required :invalid="!!form.errors.password" />
            </FormField>

            <label class="flex items-center gap-2 text-sm text-ink-soft">
                <input v-model="form.remember" type="checkbox" class="size-4 rounded border-line accent-brass-500" />
                {{ t('Remember me') }}
            </label>

            <AppButton type="submit" size="lg" :loading="form.processing">{{ t('Log in') }}</AppButton>
        </form>
    </div>
</template>
