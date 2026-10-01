<script setup>
import { Link } from '@inertiajs/vue3';
import LanguageSwitcher from '../components/common/LanguageSwitcher.vue';
</script>

<template>
    <div class="flex min-h-svh flex-col bg-sand-100">
        <header class="flex items-center justify-between px-6 py-5">
            <Link :href="route('home')" class="font-display text-2xl text-ink">{{ $page.props.appName }}</Link>
            <LanguageSwitcher class="text-muted" />
        </header>

        <main class="flex grow items-center justify-center px-4 pb-16">
            <div class="w-full max-w-md rounded-2xl border border-line bg-white p-8 shadow-xl shadow-ink/5 sm:p-10">
                <slot />
            </div>
        </main>
    </div>
</template>
