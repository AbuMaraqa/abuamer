<script setup>
import { Link, router, usePage } from '@inertiajs/vue3';
import { computed, onBeforeUnmount, ref } from 'vue';
import { route } from 'ziggy-js';
import { useFocusTrap } from '../composables/useFocusTrap';
import FlashToaster from '../components/common/FlashToaster.vue';
import Icon from '../components/common/Icon.vue';
import LanguageSwitcher from '../components/common/LanguageSwitcher.vue';
import SiteLogo from '../components/layout/SiteLogo.vue';

const page = usePage();
const isSidebarOpen = ref(false);
const sidebar = ref(null);

/**
 * Items whose route is not registered yet, or that the user may not access, are hidden.
 */
const navigation = [
    { label: 'Dashboard', icon: 'layout', routeName: 'admin.dashboard', active: 'admin.dashboard' },
    { label: 'Categories', icon: 'folder-tree', routeName: 'admin.categories.index', active: 'admin.categories.*', permission: 'categories.view' },
    { label: 'Products', icon: 'box', routeName: 'admin.products.index', active: 'admin.products.*', permission: 'products.view' },
    { label: 'Slider', icon: 'image', routeName: 'admin.slides.index', active: 'admin.slides.*', permission: 'company.manage' },
    { label: 'Company', icon: 'building', routeName: 'admin.company.edit', active: 'admin.company.*', permission: 'company.manage' },
    { label: 'Messages', icon: 'mail', routeName: 'admin.messages.index', active: 'admin.messages.*', permission: 'messages.manage', badge: 'unreadMessages' },
    { label: 'Settings', icon: 'settings', routeName: 'admin.settings.edit', active: 'admin.settings.*', permission: 'settings.manage' },
];

const visibleNavigation = computed(() =>
    navigation.filter((item) => route().has(item.routeName) && (!item.permission || page.props.auth.user?.can[item.permission])),
);

const removeNavigateListener = router.on('navigate', () => {
    isSidebarOpen.value = false;
});

onBeforeUnmount(removeNavigateListener);

// The sidebar only opens as a drawer on small screens.
useFocusTrap(sidebar, () => isSidebarOpen.value);
</script>

<template>
    <div class="min-h-svh bg-sand-100 lg:flex">
        <!-- Mobile backdrop -->
        <Transition enter-active-class="transition-opacity duration-200" enter-from-class="opacity-0" leave-active-class="transition-opacity duration-200" leave-to-class="opacity-0">
            <div v-if="isSidebarOpen" class="fixed inset-0 z-30 bg-ink/40 lg:hidden" @click="isSidebarOpen = false" />
        </Transition>

        <!-- When closed on small screens it is also invisible, so Tab skips its links. Closing
             delays the visibility change until the slide-out ends; opening shows it at once. -->
        <aside
            ref="sidebar"
            class="fixed inset-y-0 start-0 z-40 flex w-72 shrink-0 flex-col bg-ink text-sand-200 transition-[translate] duration-300 ease-elegant lg:sticky lg:top-0 lg:h-svh"
            :class="{ 'max-lg:invisible max-lg:transition-[translate,visibility] max-lg:ltr:-translate-x-full max-lg:rtl:translate-x-full': !isSidebarOpen }"
            @keydown.esc="isSidebarOpen = false"
        >
            <div class="flex items-center justify-between px-6 py-6">
                <SiteLogo :href="route('admin.dashboard')" inverted size="sm" />
                <button type="button" class="-me-2 flex size-10 items-center justify-center rounded-lg text-sand-300 transition-colors hover:bg-white/5 hover:text-white lg:hidden" :aria-label="$t('Close menu')" @click="isSidebarOpen = false">
                    <Icon name="x" />
                </button>
            </div>

            <nav class="flex grow flex-col gap-1 px-3" :aria-label="$t('Control panel')">
                <Link
                    v-for="item in visibleNavigation"
                    :key="item.routeName"
                    :href="route(item.routeName)"
                    class="flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm transition-colors"
                    :class="route().current(item.active) ? 'bg-white/10 text-white' : 'text-sand-300 hover:bg-white/5 hover:text-white'"
                    :aria-current="route().current(item.active) ? 'page' : undefined"
                >
                    <Icon :name="item.icon" :size="18" />
                    {{ $t(item.label) }}
                    <span v-if="item.badge && $page.props[item.badge] > 0" class="ms-auto rounded-full bg-brass-500 px-2 py-0.5 text-[11px] font-semibold text-white tabular-nums">
                        {{ $page.props[item.badge] }}
                    </span>
                </Link>
            </nav>

            <div class="border-t border-white/10 p-3">
                <a :href="route('home')" target="_blank" rel="noopener" class="flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm text-sand-300 transition-colors hover:bg-white/5 hover:text-white">
                    <Icon name="globe" :size="18" />
                    {{ $t('View website') }}
                </a>
            </div>
        </aside>

        <div class="flex min-w-0 grow flex-col">
            <header class="sticky top-0 z-20 flex items-center gap-4 border-b border-line bg-white/90 px-4 py-3 backdrop-blur sm:px-6">
                <button type="button" class="-ms-2 flex size-10 items-center justify-center rounded-lg text-ink transition-colors hover:bg-sand-100 lg:hidden" :aria-label="$t('Open menu')" :aria-expanded="isSidebarOpen" @click="isSidebarOpen = true">
                    <Icon name="menu" />
                </button>

                <div class="ms-auto flex items-center gap-5 text-sm">
                    <LanguageSwitcher class="text-muted" />
                    <span class="hidden text-muted sm:inline">{{ $page.props.auth.user?.name }}</span>
                    <Link :href="route('logout')" method="post" as="button" class="inline-flex items-center gap-1.5 text-muted transition-colors hover:text-danger">
                        <Icon name="log-out" :size="18" />
                        <span class="hidden sm:inline">{{ $t('Log out') }}</span>
                    </Link>
                </div>
            </header>

            <main class="mx-auto w-full max-w-7xl grow px-4 py-8 sm:px-6 lg:px-10">
                <slot />
            </main>
        </div>

        <FlashToaster />
    </div>
</template>
