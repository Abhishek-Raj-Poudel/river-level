import { cn, formatRelativeTime, safeFormat } from '@/lib/utils';
import { useEffect, useState } from 'react';

const nepalTimeFormatter = new Intl.DateTimeFormat('en-GB', {
    timeZone: 'Asia/Kathmandu',
    dateStyle: 'medium',
    timeStyle: 'short',
});

interface DataFreshnessProps {
    updatedAt?: string | number | Date | null;
    className?: string;
}

export const DataFreshness = ({ updatedAt, className }: DataFreshnessProps) => {
    const [now, setNow] = useState(() => Date.now());

    useEffect(() => {
        const interval = window.setInterval(() => setNow(Date.now()), 30_000);

        return () => window.clearInterval(interval);
    }, []);

    const date = updatedAt === null || updatedAt === undefined ? null : new Date(updatedAt);

    if (!date || Number.isNaN(date.getTime())) {
        return null;
    }

    const absolute = safeFormat(nepalTimeFormatter, date);

    return (
        <p
            className={cn(
                'flex flex-wrap items-center justify-center gap-x-2 gap-y-1 font-mono text-xs tracking-[0.12em] text-muted-foreground uppercase',
                className,
            )}
        >
            <span aria-hidden className="h-1.5 w-1.5 shrink-0 animate-pulse rounded-full bg-status-normal-fg" />
            <span>Data refreshed {formatRelativeTime(date, now)}</span>
            <span aria-hidden className="text-border">
                ·
            </span>
            <time dateTime={date.toISOString()} title={absolute}>
                {absolute}
            </time>
        </p>
    );
};
