<script setup>
import { Link } from '@inertiajs/vue3';
import { route } from 'ziggy-js';
import Icon from '../common/Icon.vue';
import SocialIcon from '../common/SocialIcon.vue';
import SiteLogo from './SiteLogo.vue';

const year = new Date().getFullYear();
</script>

<template>
    <footer class="bg-ink text-sand-300">
        <!-- On phones the two link lists sit side by side; the brand and contact blocks take the full width. -->
        <div class="mx-auto grid max-w-7xl grid-cols-2 gap-x-6 gap-y-10 px-4 py-14 sm:px-6 md:gap-12 md:py-16 lg:grid-cols-[1.4fr_1fr_1fr_1.2fr] lg:px-8 lg:py-20">
            <div class="col-span-2 flex flex-col gap-5 lg:col-span-1">
                <SiteLogo inverted />
                <p v-if="$page.props.site.tagline" class="max-w-xs text-sm leading-relaxed text-sand-400">{{ $page.props.site.tagline }}</p>
                <div v-if="Object.keys($page.props.site.social).length > 0" class="flex flex-wrap gap-2">
                    <a
                        v-for="(url, network) in $page.props.site.social"
                        :key="network"
                        :href="url"
                        target="_blank"
                        rel="noopener"
                        class="flex size-11 items-center justify-center rounded-full border border-white/15 text-sand-300 transition-colors hover:border-white hover:text-white"
                        :aria-label="network"
                    >
                        <SocialIcon :network="network" :size="16" />
                    </a>
                </div>
            </div>

            <nav :aria-label="$t('Collections')">
                <h2 class="mb-4 text-sm font-semibold text-white sm:mb-5">{{ $t('Collections') }}</h2>
                <ul class="flex flex-col gap-1.5 text-sm sm:gap-2">
                    <li v-for="collection in $page.props.site.navigation.slice(0, 6)" :key="collection.id">
                        <Link :href="collection.url" class="inline-block py-1 transition-colors hover:text-white">{{ collection.name }}</Link>
                    </li>
                </ul>
            </nav>

            <nav :aria-label="$t('Company')">
                <h2 class="mb-4 text-sm font-semibold text-white sm:mb-5">{{ $t('Company') }}</h2>
                <ul class="flex flex-col gap-1.5 text-sm sm:gap-2">
                    <li><Link :href="route('about')" class="inline-block py-1 transition-colors hover:text-white">{{ $t('About us') }}</Link></li>
                    <li><Link :href="route('products.index')" class="inline-block py-1 transition-colors hover:text-white">{{ $t('Products') }}</Link></li>
                    <li><Link :href="route('contact')" class="inline-block py-1 transition-colors hover:text-white">{{ $t('Contact') }}</Link></li>
                </ul>
            </nav>

            <div class="col-span-2 lg:col-span-1">
                <h2 class="mb-4 text-sm font-semibold text-white sm:mb-5">{{ $t('Contact') }}</h2>
                <ul class="flex flex-col gap-3.5 text-sm">
                    <li v-if="$page.props.site.contact.address" class="flex gap-3">
                        <Icon name="map-pin" :size="18" class="mt-0.5 shrink-0 text-brass-400" />
                        <span class="leading-relaxed">{{ $page.props.site.contact.address }}</span>
                    </li>
                    <li v-if="$page.props.site.contact.phone" class="flex gap-3">
                        <Icon name="phone" :size="18" class="shrink-0 text-brass-400" />
                        <a :href="`tel:${$page.props.site.contact.phone}`" dir="ltr" class="transition-colors hover:text-white">{{ $page.props.site.contact.phone }}</a>
                    </li>
                    <li v-if="$page.props.site.contact.email" class="flex gap-3">
                        <Icon name="mail" :size="18" class="shrink-0 text-brass-400" />
                        <a :href="`mailto:${$page.props.site.contact.email}`" class="break-all transition-colors hover:text-white">{{ $page.props.site.contact.email }}</a>
                    </li>
                </ul>
            </div>
        </div>

        <div class="border-t border-white/10">
            <p class="mx-auto max-w-7xl px-4 py-6 text-xs text-sand-400 sm:px-6 lg:px-8">
                © {{ year }} {{ $page.props.site.name }}. {{ $t('All rights reserved.') }}
            </p>
        </div>
    </footer>
</template>
