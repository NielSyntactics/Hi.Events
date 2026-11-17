const storageKey = (orderShortId: string) => 'checkout_session_' + orderShortId;

export const saveCheckoutSession = (orderShortId: string, sessionIdentifier?: string | null) => {
    if (typeof window === 'undefined' || !sessionIdentifier) {
        return;
    }

    try {
        window.sessionStorage.setItem(storageKey(orderShortId), sessionIdentifier);
    } catch {
        // storage unavailable (e.g. blocked by the browser)
    }
};

export const getCheckoutSession = (orderShortId: string): string | null => {
    if (typeof window === 'undefined') {
        return null;
    }

    try {
        return window.sessionStorage.getItem(storageKey(orderShortId));
    } catch {
        return null;
    }
};
