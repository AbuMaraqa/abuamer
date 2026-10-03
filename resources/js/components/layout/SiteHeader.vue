<script setup>
import { Link, router, usePage } from '@inertiajs/vue3';
import { computed, onBeforeUnmount, onMounted, ref, watch } from 'vue';
import { route } from 'ziggy-js';
import { useFocusTrap } from '../../composables/useFocusTrap';
import AppButton from '../common/AppButton.vue';
import Icon from '../common/Icon.vue';
import LanguageSwitcher from '../common/LanguageSwitcher.vue';
import SocialIcon from '../common/SocialIcon.vue';
import MegaMenu from './MegaMenu.vue';
import MobileNavTree from './MobileNavTree.vue';
import SiteLogo from './SiteLogo.vue';

const page = usePage();

const isScrolled = ref(false);
const isMegaMenuOpen = ref(false);
const isMobileMenuOpen = ref(false);
const desktopNav = ref(null);
const productsButton = ref(null);
const mobileDrawer = ref(null);
let closeTimer = null;

/**
 * On the home page the header floats transparently over the hero image until the visitor scrolls.
 */
const isTransparent = computed(() => page.component === 'Home' && !isScrolled.value && !isMegaMenuOpen.value);

const links = computed(() => [
    { label: 'Home', href: route('home'), active: route().current('home') },
    page.props.site.hasBrands && { label: 'Brands', href: route('brands.index'), active: route().current('brands.*') },
    { label: 'About us', href: route('about'), active: route().current('about') },
    { label: 'Contact', href: route('contact'), active: route().current('contact') },
].filter(Boolean));

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

/**
 * Keyboard users leaving the products menu (Tab past its last link) close it.
 */
function onNavFocusOut(event) {
    if (!desktopNav.value?.contains(event.relatedTarget)) {
        isMegaMenuOpen.value = false;
    }
}

function onKeydown(event) {
    if (event.key !== 'Escape') {
        return;
    }

    if (isMegaMenuOpen.value && desktopNav.value?.contains(document.activeElement)) {
        productsButton.value?.focus();
    }

    isMegaMenuOpen.value = false;
    isMobileMenuOpen.value = false;
}

watch(isMobileMenuOpen, (open) => {
    document.body.style.overflow = open ? 'hidden' : '';
});

useFocusTrap(mobileDrawer, () => isMobileMenuOpen.value, {
    initialFocus: () => mobileDrawer.value?.querySelector('[data-drawer-close]'),
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
        <div class="mx-auto flex h-16 max-w-7xl items-center justify-between gap-4 px-4 sm:px-6 lg:h-20 lg:gap-6 lg:px-8">
            <SiteLogo :inverted="isTransparent" />

            <nav ref="desktopNav" class="hidden items-center gap-1 lg:flex" :aria-label="$t('Main navigation')" @focusout="onNavFocusOut">
                <Link :href="links[0].href" class="nav-link" :class="{ 'nav-link-active': links[0].active }">{{ $t(links[0].label) }}</Link>

                <button
                    ref="productsButton"
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

                <!-- Placed right after its button so Tab moves into the open menu; positioned against the header. -->
                <Transition
                    enter-active-class="transition duration-200 ease-elegant"
                    enter-from-class="-translate-y-2 opacity-0"
                    leave-active-class="transition duration-150"
                    leave-to-class="opacity-0"
                >
                    <div v-if="isMegaMenuOpen" class="absolute inset-x-0 top-full" @mouseenter="openMegaMenu">
                        <MegaMenu :collections="$page.props.site.navigation" @navigate="isMegaMenuOpen = false" />
                    </div>
                </Transition>

                <Link v-for="link in links.slice(1)" :key="link.href" :href="link.href" class="nav-link" :class="{ 'nav-link-active': link.active }">
                    {{ $t(link.label) }}
                </Link>
            </nav>

            <div class="flex items-center gap-4">
                <div class="hidden sm:block">
                    <LanguageSwitcher class="text-sm font-medium" />
                </div>
                <!-- A wrapper, not classes on AppButton: its own inline-flex would override "hidden". -->
                <div class="hidden md:block">
                    <AppButton :href="route('contact')" :variant="isTransparent ? 'secondary' : 'primary'">
                        {{ $t('Request a quote') }}
                    </AppButton>
                </div>
                <button type="button" class="-me-2.5 flex size-11 items-center justify-center lg:hidden" :aria-label="$t('Open menu')" :aria-expanded="isMobileMenuOpen" @click="isMobileMenuOpen = true">
                    <Icon name="menu" :size="24" />
                </button>
            </div>
        </div>
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
            <aside
                v-if="isMobileMenuOpen"
                ref="mobileDrawer"
                class="fixed inset-y-0 start-0 z-50 flex w-[min(22rem,88vw)] flex-col bg-sand-50 lg:hidden"
                role="dialog"
                aria-modal="true"
                :aria-label="$t('Main navigation')"
            >
                <div class="flex h-16 shrink-0 items-center justify-between gap-4 border-b border-line ps-5 pe-2.5">
                    <SiteLogo size="sm" />
                    <button type="button" data-drawer-close class="flex size-11 shrink-0 items-center justify-center text-ink" :aria-label="$t('Close menu')" @click="isMobileMenuOpen = false">
                        <Icon name="x" :size="22" />
                    </button>
                </div>

                <nav class="flex grow flex-col overflow-y-auto px-5 py-2">
                    <Link :href="links[0].href" class="drawer-link" :class="{ 'drawer-link-active': links[0].active }" :aria-current="links[0].active ? 'page' : undefined">
                        {{ $t(links[0].label) }}
                    </Link>
                    <div class="border-b border-line pb-2">
                        <Link :href="route('products.index')" class="drawer-link border-b-0" :class="{ 'drawer-link-active': isProductsActive }">{{ $t('Products') }}</Link>
                        <MobileNavTree :nodes="$page.props.site.navigation" />
                    </div>
                    <Link
                        v-for="link in links.slice(1)"
                        :key="link.href"
                        :href="link.href"
                        class="drawer-link"
                        :class="{ 'drawer-link-active': link.active }"
                        :aria-current="link.active ? 'page' : undefined"
                    >
                        {{ $t(link.label) }}
                    </Link>
                </nav>

                <div class="flex shrink-0 flex-col gap-4 border-t border-line bg-white px-5 py-5">
                    <AppButton :href="route('contact')" size="lg" class="w-full">{{ $t('Request a quote') }}</AppButton>
                    <div class="flex items-center justify-between gap-4">
                        <LanguageSwitcher class="text-sm font-medium text-ink" />
                        <div class="flex items-center gap-2">
                            <a
                                v-if="$page.props.site.contact.phone"
                                :href="`tel:${$page.props.site.contact.phone}`"
                                class="flex size-11 items-center justify-center rounded-full border border-line text-ink-soft transition-colors hover:border-ink hover:text-ink"
                                :aria-label="$t('Call us')"
                            >
                                <Icon name="phone" :size="18" />
                            </a>
                            <a
                                v-if="$page.props.site.contact.whatsapp"
                                :href="`https://wa.me/${$page.props.site.contact.whatsapp}`"
                                target="_blank"
                                rel="noopener"
                                class="flex size-11 items-center justify-center rounded-full bg-[#25D366] text-white transition-opacity hover:opacity-90"
                                :aria-label="$t('Chat with us on WhatsApp')"
                            >
                                <SocialIcon network="whatsapp" :size="20" />
                            </a>
                        </div>
                    </div>
                </div>
            </aside>
        </Transition>
    </Teleport>
</template>
