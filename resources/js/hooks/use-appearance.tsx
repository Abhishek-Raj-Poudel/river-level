import { useCallback, useEffect, useState } from 'react';

export type Appearance = 'light' | 'dark' | 'system';

const applyTheme = (appearance: Appearance) => {
    const isDark = appearance === 'dark';

    document.documentElement.classList.toggle('dark', isDark);
    document.documentElement.style.colorScheme = isDark ? 'dark' : 'light';
};

export function initializeTheme() {
    applyTheme('light');
}

export function useAppearance() {
    const [appearance] = useState<Appearance>('light');

    const updateAppearance = useCallback(() => {
        // Theme switching disabled - always light mode
    }, []);

    useEffect(() => {
        applyTheme('light');
    }, []);

    return { appearance, updateAppearance } as const;
}
