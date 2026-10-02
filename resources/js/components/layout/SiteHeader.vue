<script setup>
import { Link, router, usePage } from '@inertiajs/vue3';
import { computed, onBeforeUnmount, onMounted, ref, watch } from 'vue';
import { route } from 'ziggy-js';
import AppButton from '../common/AppButton.vue';
import Icon from '../common/Icon.vue';
import LanguageSwitcher from '../common/LanguageSwitcher.vue';
import MegaMenu from './MegaMenu.vue';
import MobileNavTree from './MobileNavTree.vue';
import SiteLogo from './SiteLogo.vue';

const page = usePage();

const isScrolled = ref(false);
const isMegaMenuOpen = ref(false);
const isMobileMenuOpen = ref(false);
let closeTimer = null;

/**
 * On the home page the header floats transparently over the hero image until the visitor scrolls.
 */
const isTransparent = computed(() => page.component === 'Home' && !isScrolled.value && !isMegaMenuOpen.value);

const links = computed(() => [
    { label: 'Home', href: route('home'), active: route().current('home') },
    { label: 'About us', href: route('about'), active: route().current('about') },
    { label: 'Contact', href: route('contact'), active: route().current('contact') },
]);

const isProductsActive = computed(() => route().current('products.*'));

function onScroll() {
    isScrolled.value = window.scrollY > 24;
}

function openMegaMenu() {
    clearTimeout(closeTimer);
    isMegaMenuOpen.value = true;
}

function closeMegaMenuSoon() {
    clearTimeout(closeTimer);
    closeTimer = setTimeout(() => (isMegaMenuOpen.value = false), 150);
}

function onKeydown(event) {
    if (event.key === 'Escape') {
        isMegaMenuOpen.value = false;
        isMobileMenuOpen.value = false;
    }
}

watch(isMobileMenuOpen, (open) => {
    document.body.style.overflow = open ? 'hidden' : '';
});

const removeNavigateListener = router.on('navigate', () => {
    isMegaMenuOpen.value = false;
    isMobileMenuOpen.value = false;
});

onMounted(() => {
    onScroll();
    window.addEventListener('scroll', onScroll, { passive: true });
    document.addEventListener('keydown', onKeydown);
});

onBeforeUnmount(() => {
    removeNavigateListener();
    clearTimeout(closeTimer);
    window.removeEventListener('scroll', onScroll);
    document.removeEventListener('keydown', onKeydown);
    document.body.style.overflow = '';
});
</script>

<template>
    <header
        class="fixed inset-x-0 top-0 z-40 transition-colors duration-300"
        :class="isTransparent ? 'bg-transparent text-white' : 'border-b border-line bg-sand-50/90 text-ink backdrop-blur-md'"
        @mouseleave="closeMegaMenuSoon"
    >
        <div class="mx-auto flex h-20 max-w-7xl items-center justify-between gap-6 px-4 sm:px-6 lg:px-8">
            <SiteLogo :inverted="isTransparent" />

            <nav class="hidden items-center gap-1 lg:flex" :aria-label="$t('Main navigation')">
                <Link :href="links[0].href" class="nav-link" :class="{ 'nav-link-active': links[0].active }">{{ $t(links[0].label) }}</Link>

                <button
                    type="button"
                    class="nav-link inline-flex items-center gap-1"
                    :class="{ 'nav-link-active': isProductsActive }"
                    aria-haspopup="true"
                    :aria-expanded="isMegaMenuOpen"
                    @mouseenter="openMegaMenu"
                    @click="isMegaMenuOpen = !isMegaMenuOpen"
                >
                    {{ $t('Products') }}
                    <Icon name="chevron-down" :size="16" class="transition-transform" :class="{ 'rotate-180': isMegaMenuOpen }" />
                </button>

                <Link v-for="link in links.slice(1)" :key="link.href" :href="link.href" class="nav-link" :class="{ 'nav-link-active': link.active }">
                    {{ $t(link.label) }}
                </Link>
            </nav>

            <div class="flex items-center gap-4">
                <div class="hidden sm:block">
                    <LanguageSwitcher class="text-sm font-medium" />
                </div>
                <AppButton :href="route('contact')" :variant="isTransparent ? 'secondary' : 'primary'" class="hidden md:inline-flex">
                    {{ $t('Request a quote') }}
                </AppButton>
                <button type="button" class="flex size-11 items-center justify-center lg:hidden" :aria-label="$t('Open menu')" :aria-expanded="isMobileMenuOpen" @click="isMobileMenuOpen = true">
                    <Icon name="menu" :size="24" />
                </button>
            </div>
        </div>

        <Transition
            enter-active-class="transition duration-200 ease-elegant"
            enter-from-class="-translate-y-2 opacity-0"
            leave-active-class="transition duration-150"
            leave-to-class="opacity-0"
        >
            <div v-if="isMegaMenuOpen" class="absolute inset-x-0 top-full hidden lg:block" @mouseenter="openMegaMenu">
                <MegaMenu :collections="$page.props.site.navigation" @navigate="isMegaMenuOpen = false" />
            </div>
        </Transition>
    </header>

    <!-- Mobile navigation drawer -->
    <Teleport to="body">
        <Transition enter-active-class="transition-opacity duration-200" enter-from-class="opacity-0" leave-active-class="transition-opacity duration-200" leave-to-class="opacity-0">
            <div v-if="isMobileMenuOpen" class="fixed inset-0 z-50 bg-ink/50 lg:hidden" @click="isMobileMenuOpen = false" />
        </Transition>
        <Transition
            enter-active-class="transition-transform duration-300 ease-elegant"
            :enter-from-class="$page.props.locale.direction === 'rtl' ? 'translate-x-full' : '-translate-x-full'"
            leave-active-class="transition-transform duration-200"
            :leave-to-class="$page.props.locale.direction === 'rtl' ? 'translate-x-full' : '-translate-x-full'"
        >
            <aside v-if="isMobileMenuOpen" class="fixed inset-y-0 start-0 z-50 flex w-[min(22rem,88vw)] flex-col bg-sand-50 lg:hidden" :aria-label="$t('Main navigation')">
                <div class="flex h-20 items-center justify-between border-b border-line px-5">
                    <SiteLogo />
                    <button type="button" class="flex size-11 items-center justify-center text-ink" :aria-label="$t('Close menu')" @click="isMobileMenuOpen = false">
                        <Icon name="x" :size="22" />
                    </button>
                </div>

                <nav class="flex grow flex-col gap-1 overflow-y-auto px-5 py-6">
                    <Link :href="route('home')" class="py-3 font-display text-2xl text-ink">{{ $t('Home') }}</Link>
                    <Link :href="route('products.index')" class="py-3 font-display text-2xl text-ink">{{ $t('Products') }}</Link>
                    <MobileNavTree :nodes="$page.props.site.navigation" class="mb-3" />
                    <Link :href="route('about')" class="py-3 font-display text-2xl text-ink">{{ $t('About us') }}</Link>
                    <Link :href="route('contact')" class="py-3 font-display text-2xl text-ink">{{ $t('Contact') }}</Link>
                </nav>

                <div class="flex items-center justify-between gap-4 border-t border-line px-5 py-5">
                    <LanguageSwitcher class="text-sm font-medium text-ink" />
                    <AppButton :href="route('contact')" size="sm">{{ $t('Request a quote') }}</AppButton>
                </div>
            </aside>
        </Transition>
    </Teleport>
</template>
