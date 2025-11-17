import { Badge } from '@/components/ui/badge';
import { Card } from '@/components/ui/card';
import { getStatusColor, getStatusText } from '@/data/rivers';
import { cn } from '@/lib/utils';
import { River } from '@/types';
import { Link } from '@inertiajs/react';
import { ArrowRight, Droplets, Thermometer, TrendingUp } from 'lucide-react';

interface RiverCardProps {
    river: River;
    // onClick: () => void;
}

export const RiverCard = ({ river }: RiverCardProps) => {
    return (
        <>
            {/* <Link href={`/river/${river.uid}`}> */}
            <Link href={`/`}>
                <Card className="group bg-gradient-card relative cursor-pointer overflow-hidden border-border transition-all duration-300 hover:scale-[1.02] hover:border-primary/30 hover:shadow-glow">
                    {/* Background gradient overlay */}
                    <div className="absolute inset-0 bg-gradient-to-br from-primary/5 to-accent/5 opacity-0 transition-opacity duration-300 group-hover:opacity-100" />

                    <div className="relative p-6">
                        {/* Header */}
                        <div className="mb-4 flex items-start justify-between">
                            <div>
                                <h3 className="text-xl font-bold text-foreground transition-colors group-hover:text-primary">{river.name}</h3>
                                <p className="text-sm text-muted-foreground">
                                    {river.country} • {river.continent}
                                </p>
                            </div>
                            <Badge
                                variant="secondary"
                                className={cn(
                                    'text-xs font-medium',
                                    river.status === 'low' && 'border-water-low/30 bg-water-low/20 text-neutral-700',
                                    river.status === 'normal' && 'border-water-normal/30 bg-water-normal/20 text-water-normal',
                                    river.status === 'high' && 'border-water-high/30 bg-water-high/20 text-water-high',
                                    river.status === 'critical' && 'border-water-critical/30 bg-water-critical/20 text-water-critical',
                                )}
                            >
                                {getStatusText(river.status)}
                            </Badge>
                        </div>

                        {/* Stats Grid */}
                        <div className="mb-4 grid grid-cols-3 gap-4">
                            <div className="text-center">
                                <div className="mb-1 flex items-center justify-center">
                                    <Droplets className="h-4 w-4 text-primary" />
                                </div>
                                <div className="text-lg font-semibold text-foreground">{river.current_water_level}m</div>
                                <div className="text-xs text-muted-foreground">Water Level</div>
                            </div>

                            <div className="text-center">
                                <div className="mb-1 flex items-center justify-center">
                                    <TrendingUp className="h-4 w-4 text-accent" />
                                </div>
                                <div className="text-lg font-semibold text-foreground">{river.current_flow_rate.toLocaleString()}</div>
                                <div className="text-xs text-muted-foreground">m³/s Flow</div>
                            </div>

                            <div className="text-center">
                                <div className="mb-1 flex items-center justify-center">
                                    <Thermometer className="h-4 w-4 text-warning" />
                                </div>
                                <div className="text-lg font-semibold text-foreground">{river.temperature}°C</div>
                                <div className="text-xs text-muted-foreground">Temperature</div>
                            </div>
                        </div>

                        {/* Progress Bar */}
                        <div className="mb-4">
                            <div className="mb-1 flex justify-between text-xs text-muted-foreground">
                                <span>Current vs Normal Level</span>
                                <span>{((river.current_water_level / river.normal_water_level) * 100).toFixed(0)}%</span>
                            </div>
                            <div className="h-2 w-full rounded-full bg-neutral-200">
                                <div
                                    className={cn('h-2 rounded-full transition-all duration-500', `bg-${getStatusColor(river.status)}`)}
                                    style={{
                                        width: `${Math.min(100, (river.current_water_level / river.normal_water_level) * 100)}%`,
                                    }}
                                />
                            </div>
                        </div>

                        {/* Footer */}
                        <div className="flex items-center justify-between">
                            <div className="text-xs text-muted-foreground">Length: {river.length.toLocaleString()} km</div>
                            <ArrowRight className="h-4 w-4 text-muted-foreground transition-all group-hover:translate-x-1 group-hover:text-primary" />
                        </div>
                    </div>
                </Card>
            </Link>
        </>
    );
};
