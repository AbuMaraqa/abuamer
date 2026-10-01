<script setup>
import { Link } from '@inertiajs/vue3';
import FlashToaster from '../components/common/FlashToaster.vue';
import LanguageSwitcher from '../components/common/LanguageSwitcher.vue';
</script>

<template>
    <div class="flex min-h-svh flex-col">
        <header class="border-b border-line bg-sand-50">
            <div class="mx-auto flex max-w-7xl items-center justify-between px-4 py-5 sm:px-6 lg:px-8">
                <Link :href="route('home')" class="font-display text-2xl text-ink">{{ $page.props.appName }}</Link>
                <LanguageSwitcher class="text-muted" />
            </div>
        </header>

        <main class="grow">
            <slot />
        </main>

        <FlashToaster />
    </div>
</template>
