import { Badge } from '@/components/ui/badge';
import { Card } from '@/components/ui/card';
import { River, getStatusColor, getStatusText } from '@/data/rivers';
import { cn } from '@/lib/utils';
import { Link } from '@inertiajs/react';
import { ArrowRight, Droplets, Thermometer, TrendingUp } from 'lucide-react';

interface RiverCardProps {
    river: River;
    onClick: () => void;
}

export const RiverCard = ({ river }: RiverCardProps) => {
    return (
        <Link href={`/river/${river.uid}`}>
        <Card className="group bg-gradient-card relative cursor-pointer overflow-hidden border-border/20 transition-all duration-300 hover:scale-[1.02] hover:border-primary/30 hover:shadow-glow">
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
                            river.water_level_status === 'low' && 'border-water-low/30 bg-water-low/20 text-neutral-700',
                            river.water_level_status === 'normal' && 'border-water-normal/30 bg-water-normal/20 text-water-normal',
                            river.water_level_status === 'high' && 'border-water-high/30 bg-water-high/20 text-water-high',
                            river.water_level_status === 'critical' && 'border-water-critical/30 bg-water-critical/20 text-water-critical',
                        )}
                    >
                        {getStatusText(river.water_level_status)}
                    </Badge>
                </div>

                {/* Stats Grid */}
                <div className="mb-4 grid grid-cols-3 gap-4">
                    <div className="text-center">
                        <div className="mb-1 flex items-center justify-center">
                            <Droplets className="h-4 w-4 text-primary" />
                        </div>
                        <div className="text-lg font-semibold text-foreground">{river.water_level_current}m</div>
                        <div className="text-xs text-muted-foreground">Water Level</div>
                    </div>

                    <div className="text-center">
                        <div className="mb-1 flex items-center justify-center">
                            <TrendingUp className="h-4 w-4 text-accent" />
                        </div>
                        <div className="text-lg font-semibold text-foreground">{river.flow_rate_current.toLocaleString()}</div>
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
                        <span>{((river.water_level_current / river.water_level_normal) * 100).toFixed(0)}%</span>
                    </div>
                    <div className="h-2 w-full rounded-full bg-neutral-200">
                        <div
                            className={cn('h-2 rounded-full transition-all duration-500', `bg-${getStatusColor(river.water_level_status)}`)}
                            style={{
                                width: `${Math.min(100, (river.water_level_current / river.water_level_normal) * 100)}%`,
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
    );
};
