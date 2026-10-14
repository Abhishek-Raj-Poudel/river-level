import { type ClassValue, clsx } from 'clsx';
import { twMerge } from 'tailwind-merge';

export function cn(...inputs: ClassValue[]) {
    return twMerge(clsx(inputs));
}

export function safeFormat(formatter: Intl.DateTimeFormat, value: string | number | Date | null | undefined, fallback = '—'): string {
    const date = value === null || value === undefined ? new Date(NaN) : new Date(value);

    return Number.isNaN(date.getTime()) ? fallback : formatter.format(date);
}
