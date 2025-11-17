import { useCallback, useEffect, useState } from 'react';

export type Appearance = 'light' | 'dark' | 'system';

const applyTheme = (appearance: Appearance) => {
    const isDark = appearance === 'dark';

    document.documentElement.classList.toggle('dark', isDark);
    document.documentElement.style.colorScheme = isDark ? 'dark' : 'light';
};

export function initializeTheme() {
    applyTheme('dark');
}

export function useAppearance() {
    const [appearance] = useState<Appearance>('dark');

    const updateAppearance = useCallback(() => {
        // Theme switching disabled - always dark mode
    }, []);

    useEffect(() => {
        applyTheme('dark');
    }, []);

    return { appearance, updateAppearance } as const;
}
