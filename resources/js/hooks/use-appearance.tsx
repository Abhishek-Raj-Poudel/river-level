import { useCallback, useEffect, useState } from 'react';

export type Appearance = 'light' | 'dark' | 'system';

const STORAGE_KEY = 'appearance';

const resolveAppearance = (appearance: Appearance): 'light' | 'dark' => {
    if (appearance === 'system' && typeof window !== 'undefined') {
        return window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
    }

    return appearance === 'dark' ? 'dark' : 'light';
};

const applyTheme = (appearance: Appearance) => {
    if (typeof document === 'undefined') {
        return;
    }

    const resolved = resolveAppearance(appearance);
    document.documentElement.classList.toggle('dark', resolved === 'dark');
    document.documentElement.style.colorScheme = resolved;
};

export function initializeTheme() {
    if (typeof window === 'undefined') {
        return;
    }

    const stored = window.localStorage.getItem(STORAGE_KEY) as Appearance | null;
    applyTheme(stored ?? 'light');
}

export function useAppearance() {
    const [appearance, setAppearance] = useState<Appearance>(() => {
        if (typeof window === 'undefined') {
            return 'light';
        }

        return (window.localStorage.getItem(STORAGE_KEY) as Appearance | null) ?? 'light';
    });

    const updateAppearance = useCallback((value: Appearance) => {
        setAppearance(value);

        if (typeof window !== 'undefined') {
            window.localStorage.setItem(STORAGE_KEY, value);
        }

        applyTheme(value);
    }, []);

    useEffect(() => {
        applyTheme(appearance);
    }, [appearance]);

    return { appearance, updateAppearance } as const;
}
