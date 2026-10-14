import { statusDotClass, type RiverStatus } from '@/lib/status';
import { cn } from '@/lib/utils';

interface LevelBarProps {
    current: number;
    normal: number;
    status: RiverStatus;
    className?: string;
}

export function LevelBar({ current, normal, status, className }: LevelBarProps) {
    const hasBaseline = Number.isFinite(normal) && normal > 0;
    const ratio = hasBaseline ? (current / normal) * 100 : 0;
    const width = Math.min(100, Math.max(0, ratio));

    return (
        <div className={cn('h-1.5 w-full overflow-hidden rounded-sm bg-muted', className)}>
            <div className={cn('h-full rounded-sm transition-all duration-500', statusDotClass(status))} style={{ width: `${width}%` }} />
        </div>
    );
}
