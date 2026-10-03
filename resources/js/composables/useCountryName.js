import { usePage } from '@inertiajs/vue3';

const displayNames = new Map();

/**
 * The name of a country in the page's language, from its ISO 3166 code ("DE" → "ألمانيا").
 * Brands store only the code, so the name never needs translating by hand.
 */
export function countryName(code, locale = usePage().props.locale.current) {
    if (!code) {
        return null;
    }

    if (!displayNames.has(locale)) {
        displayNames.set(locale, new Intl.DisplayNames([locale], { type: 'region' }));
    }

    try {
        return displayNames.get(locale).of(code) ?? code;
    } catch {
        return code;
    }
}

/**
 * Countries offered when choosing a brand's country of origin: the main manufacturing
 * countries for sanitary ware, mixers and tiles.
 */
export const BRAND_COUNTRIES = [
    'DE', 'IT', 'ES', 'FR', 'PT', 'GB', 'CH', 'AT', 'BE', 'NL', 'DK', 'SE', 'FI', 'PL', 'CZ',
    'TR', 'GR', 'US', 'CA', 'MX', 'BR', 'CN', 'JP', 'KR', 'IN', 'TH', 'VN', 'MY',
    'AE', 'SA', 'EG', 'JO', 'PS', 'IL', 'LB', 'MA', 'TN',
];
