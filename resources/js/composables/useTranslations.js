import { usePage } from '@inertiajs/vue3';

/**
 * Translate an interface string using the JSON translations shared by Laravel
 * (lang/{locale}.json). Keys are the English source strings, so a missing
 * translation falls back to readable English.
 *
 * Placeholders follow Laravel's ":name" convention.
 */
export function translate(key, replacements = {}) {
    const translations = usePage().props.translations ?? {};
    let value = translations[key] ?? key;

    for (const [name, replacement] of Object.entries(replacements)) {
        value = value.replaceAll(`:${name}`, String(replacement));
    }

    return value;
}

export function useTranslations() {
    return { t: translate };
}

export const translationsPlugin = {
    install(app) {
        app.config.globalProperties.$t = translate;
    },
};
