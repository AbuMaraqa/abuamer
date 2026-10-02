<script setup>
import LanguageSwitcher from '../components/common/LanguageSwitcher.vue';
import SiteLogo from '../components/layout/SiteLogo.vue';
</script>

<template>
    <div class="flex min-h-svh flex-col bg-sand-100">
        <header class="flex items-center justify-between px-6 py-5">
            <SiteLogo size="sm" />
            <LanguageSwitcher class="text-muted" />
        </header>

        <main class="flex grow items-center justify-center px-4 pb-16">
            <div class="w-full max-w-md rounded-2xl border border-line bg-white p-8 shadow-xl shadow-ink/5 sm:p-10">
                <slot />
            </div>
        </main>
    </div>
</template>
