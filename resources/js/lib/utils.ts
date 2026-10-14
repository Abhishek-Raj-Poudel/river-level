import { type ClassValue, clsx } from 'clsx';
import { twMerge } from 'tailwind-merge';

export function cn(...inputs: ClassValue[]) {
    return twMerge(clsx(inputs));
}

export function safeFormat(formatter: Intl.DateTimeFormat, value: string | number | Date | null | undefined, fallback = '—'): string {
    const date = value === null || value === undefined ? new Date(NaN) : new Date(value);

    return Number.isNaN(date.getTime()) ? fallback : formatter.format(date);
}

const relativeFormatter = new Intl.RelativeTimeFormat('en', { numeric: 'auto' });

const RELATIVE_UNITS: [Intl.RelativeTimeFormatUnit, number][] = [
    ['year', 1000 * 60 * 60 * 24 * 365],
    ['month', 1000 * 60 * 60 * 24 * 30],
    ['week', 1000 * 60 * 60 * 24 * 7],
    ['day', 1000 * 60 * 60 * 24],
    ['hour', 1000 * 60 * 60],
    ['minute', 1000 * 60],
];

export function formatRelativeTime(value: string | number | Date | null | undefined, now: number = Date.now(), fallback = '—'): string {
    const date = value === null || value === undefined ? new Date(NaN) : new Date(value);

    if (Number.isNaN(date.getTime())) {
        return fallback;
    }

    const diffSeconds = (date.getTime() - now) / 1000;

    if (Math.abs(diffSeconds) < 45) {
        return 'just now';
    }

    for (const [unit, seconds] of RELATIVE_UNITS) {
        if (Math.abs(diffSeconds) >= seconds) {
            return relativeFormatter.format(Math.round(diffSeconds / seconds), unit);
        }
    }

    return relativeFormatter.format(Math.round(diffSeconds), 'second');
}
