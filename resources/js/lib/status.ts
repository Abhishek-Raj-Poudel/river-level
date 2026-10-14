export type RiverStatus = 'low' | 'normal' | 'high' | 'critical';

const STATUS_LABELS: Record<RiverStatus, string> = {
    low: 'Low Level',
    normal: 'Normal Level',
    high: 'High Level',
    critical: 'Critical Level',
};

const BADGE_CLASSES: Record<RiverStatus, string> = {
    low: 'bg-status-low-bg text-status-low-fg',
    normal: 'bg-status-normal-bg text-status-normal-fg',
    high: 'bg-status-high-bg text-status-high-fg',
    critical: 'bg-status-critical-bg text-status-critical-fg',
};

const DOT_CLASSES: Record<RiverStatus, string> = {
    low: 'bg-water-low',
    normal: 'bg-water-normal',
    high: 'bg-water-high',
    critical: 'bg-water-critical',
};

export function statusLabel(status: RiverStatus): string {
    return STATUS_LABELS[status] ?? STATUS_LABELS.normal;
}

export function statusBadgeClass(status: RiverStatus): string {
    return BADGE_CLASSES[status] ?? BADGE_CLASSES.normal;
}

export function statusDotClass(status: RiverStatus): string {
    return DOT_CLASSES[status] ?? DOT_CLASSES.normal;
}
