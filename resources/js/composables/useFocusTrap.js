import { nextTick, onBeforeUnmount, watch } from 'vue';

const FOCUSABLE = 'a[href], button:not([disabled]), input:not([disabled]):not([type="hidden"]), select:not([disabled]), textarea:not([disabled]), [tabindex]:not([tabindex="-1"])';

/**
 * Keyboard focus for an overlay (dialog, drawer, lightbox): when it opens, focus moves
 * into it and Tab cycles through its controls only; when it closes, focus returns to
 * the control that opened it, so keyboard users never lose their place on the page.
 *
 * @param {import('vue').Ref<HTMLElement|null>} container  The overlay's root element.
 * @param {() => boolean} isOpen  A getter for the open state.
 * @param {{ initialFocus?: () => HTMLElement|null|undefined }} options  Defaults to the first focusable element.
 */
export function useFocusTrap(container, isOpen, { initialFocus } = {}) {
    let opener = null;

    function focusableElements() {
        return Array.from(container.value?.querySelectorAll(FOCUSABLE) ?? []).filter((element) => element.getClientRects().length > 0);
    }

    function onKeydown(event) {
        if (event.key !== 'Tab' || !container.value) {
            return;
        }

        const elements = focusableElements();

        if (elements.length === 0) {
            event.preventDefault();

            return;
        }

        const first = elements[0];
        const last = elements[elements.length - 1];
        const isInside = container.value.contains(document.activeElement);

        if (event.shiftKey && (!isInside || document.activeElement === first)) {
            event.preventDefault();
            last.focus();
        } else if (!event.shiftKey && (!isInside || document.activeElement === last)) {
            event.preventDefault();
            first.focus();
        }
    }

    function release() {
        document.removeEventListener('keydown', onKeydown);

        // The opener may be gone, e.g. the delete button of a row that was just deleted.
        if (opener?.isConnected) {
            opener.focus({ preventScroll: true });
        }

        opener = null;
    }

    watch(isOpen, async (open) => {
        if (!open) {
            release();

            return;
        }

        opener = document.activeElement;
        document.addEventListener('keydown', onKeydown);

        await nextTick();
        (initialFocus?.() ?? focusableElements()[0])?.focus();
    });

    onBeforeUnmount(() => document.removeEventListener('keydown', onKeydown));
}
