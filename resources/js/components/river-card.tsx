import { LevelBar } from '@/components/level-bar';
import { Badge } from '@/components/ui/badge';
import { Card } from '@/components/ui/card';
import { useReveal } from '@/hooks/use-reveal';
import { statusBadgeClass, statusLabel } from '@/lib/status';
import { cn } from '@/lib/utils';
import { River } from '@/types';
import { Link } from '@inertiajs/react';
import { ArrowRight, Drop, Mountains, Thermometer } from '@phosphor-icons/react';
import type { CSSProperties } from 'react';

interface RiverCardProps {
    river: River;
    index?: number;
}

export const RiverCard = ({ river, index = 0 }: RiverCardProps) => {
    const ref = useReveal<HTMLDivElement>();

    return (
        <div ref={ref} className="reveal" style={{ '--index': index } as CSSProperties}>
            <Link href={`/river/${river.slug}`} className="block h-full">
                <Card className="group h-full gap-0 border-border bg-card py-0 shadow-none transition-all duration-200 hover:border-foreground/15 hover:shadow-hover">
                    <div className="p-6">
                        <div className="mb-5 flex items-start justify-between gap-3">
                            <div className="min-w-0">
                                <h3 className="truncate font-serif text-xl tracking-[-0.01em] text-foreground">{river.station_name || river.name}</h3>
                                <p className="mt-1 text-sm text-muted-foreground">
                                    {river.district ? `${river.district}, ` : ''}
                                    {river.country}
                                </p>
                            </div>
                            <Badge
                                className={cn(
                                    'shrink-0 border-0 text-[11px] font-medium tracking-[0.05em] uppercase',
                                    statusBadgeClass(river.status),
                                )}
                            >
                                {statusLabel(river.status).replace(' Level', '')}
                            </Badge>
                        </div>

                        <div className="mb-5 grid grid-cols-3 gap-4">
                            <div>
                                <Drop size={16} weight="bold" className="mb-1.5 text-water-low" />
                                <div className="font-mono text-lg text-foreground">{river.current_water_level}m</div>
                                <div className="text-xs text-muted-foreground">Level</div>
                            </div>
                            <div>
                                <Thermometer size={16} weight="bold" className="mb-1.5 text-muted-foreground" />
                                <div className="font-mono text-lg text-foreground">{river.temperature}°C</div>
                                <div className="text-xs text-muted-foreground">Temp</div>
                            </div>
                            <div>
                                <Mountains size={16} weight="bold" className="mb-1.5 text-muted-foreground" />
                                <div className="font-mono text-lg text-foreground">
                                    {river.elevation !== null && river.elevation !== undefined ? `${river.elevation}m` : 'N/A'}
                                </div>
                                <div className="text-xs text-muted-foreground">Elevation</div>
                            </div>
                        </div>

                        <div>
                            <div className="mb-1.5 flex justify-between font-mono text-[11px] tracking-[0.08em] text-muted-foreground uppercase">
                                <span>vs normal {river.normal_water_level}m</span>
                                <span>
                                    {river.normal_water_level > 0
                                        ? `${Math.round((river.current_water_level / river.normal_water_level) * 100)}%`
                                        : '—'}
                                </span>
                            </div>
                            <LevelBar current={river.current_water_level} normal={river.normal_water_level} status={river.status} />
                        </div>

                        <div className="mt-5 flex items-center justify-between border-t border-border pt-4">
                            <span className="font-mono text-xs text-muted-foreground">{river.length.toLocaleString()} km length</span>
                            <ArrowRight
                                size={15}
                                weight="bold"
                                className="text-muted-foreground transition-transform duration-200 group-hover:translate-x-0.5 group-hover:text-foreground"
                            />
                        </div>
                    </div>
                </Card>
            </Link>
        </div>
    );
};
