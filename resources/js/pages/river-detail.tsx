import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card } from '@/components/ui/card';
import { getStatusColor, getStatusText } from '@/data/rivers';
import { cn } from '@/lib/utils';
import { River } from '@/types';
import { Head, router } from '@inertiajs/react';
import { Activity, ArrowLeft, Calendar, Droplets, MapPin, Thermometer } from 'lucide-react';
import { Area, AreaChart, CartesianGrid, ResponsiveContainer, Tooltip, XAxis, YAxis } from 'recharts';

interface Props {
    river: River;
}

export default function RiverDetail({ river }: Props) {
    const lastUpdated = new Date(river.last_updated).toLocaleString();

    const handleBack = () => {
        router.visit('/');
    };

    return (
        <>
            <Head title={river.station_name || river.name} />
            <div className="bg-gradient-background min-h-screen">
                <div className="container mx-auto max-w-6xl px-4 py-8">
                    {/* Header */}
                    <div className="mb-8 flex items-center gap-4">
                        <Button variant="ghost" size="sm" onClick={handleBack} className="hover:bg-primary/10 hover:text-primary">
                            <ArrowLeft className="mr-2 h-4 w-4" />
                            Back to Rivers
                        </Button>
                    </div>
                    {/* Title Section */}
                    <div className="mb-8">
                        <div className="mb-4 flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                            <div>
                                <h1 className="mb-2 text-4xl font-bold text-foreground">{river.station_name || river.name}</h1>
                                <div className="flex items-center gap-4 text-muted-foreground">
                                    <div className="flex items-center gap-1">
                                        <MapPin className="h-4 w-4" />
                                        {river.country}, {river.continent}
                                    </div>
                                    <div className="flex items-center gap-1">
                                        <Calendar className="h-4 w-4" />
                                        Updated {lastUpdated}
                                    </div>
                                </div>
                            </div>
                            <Badge
                                variant="secondary"
                                className={cn(
                                    'px-4 py-2 text-sm font-medium',
                                    river.status === 'low' && 'border-water-low/30 bg-water-low/20 text-water-low',
                                    river.status === 'normal' && 'border-water-normal/30 bg-water-normal/20 text-water-normal',
                                    river.status === 'high' && 'border-water-high/30 bg-water-high/20 text-water-high',
                                    river.status === 'critical' && 'border-water-critical/30 bg-water-critical/20 text-water-critical',
                                )}
                            >
                                {getStatusText(river.status)}
                            </Badge>
                        </div>
                        <p className="max-w-3xl text-lg text-muted-foreground">{river.description}</p>
                    </div>

                    {/* Stats Grid */}
                    <div className="mb-8 grid grid-cols-1 gap-6 md:grid-cols-3">
                        <Card className="bg-gradient-card border-border/50 p-6">
                            <div className="mb-3 flex items-center gap-3">
                                <div className="rounded-lg bg-primary/20 p-2">
                                    <Droplets className="h-5 w-5 text-primary" />
                                </div>
                                <h3 className="font-semibold text-foreground">Water Level</h3>
                            </div>
                            <div className="mb-1 text-3xl font-bold text-foreground">{river.current_water_level}m</div>
                            <div className="text-sm text-muted-foreground">Normal: {river.normal_water_level}m</div>
                            <div className="mt-3 h-2 w-full rounded-full bg-muted">
                                <div
                                    className={cn('h-2 rounded-full transition-all duration-500', `bg-${getStatusColor(river.status)}`)}
                                    style={{
                                        width: `${Math.min(100, (river.current_water_level / river.normal_water_level) * 100)}%`,
                                    }}
                                />
                            </div>
                        </Card>

                        <Card className="bg-gradient-card border-border/50 p-6">
                            <div className="mb-3 flex items-center gap-3">
                                <div className="rounded-lg bg-warning/20 p-2">
                                    <Thermometer className="h-5 w-5 text-warning" />
                                </div>
                                <h3 className="font-semibold text-foreground">Temperature</h3>
                            </div>
                            <div className="mb-1 text-3xl font-bold text-foreground">{river.temperature}°C</div>
                            <div className="text-sm text-muted-foreground">Current water temp</div>
                        </Card>

                        <Card className="bg-gradient-card border-border/50 p-6">
                            <div className="mb-3 flex items-center gap-3">
                                <div className="rounded-lg bg-secondary/20 p-2">
                                    <Activity className="h-5 w-5 text-secondary-foreground" />
                                </div>
                                <h3 className="font-semibold text-foreground">River Length</h3>
                            </div>
                            <div className="mb-1 text-3xl font-bold text-foreground">{river.length.toLocaleString()}</div>
                            <div className="text-sm text-muted-foreground">kilometers</div>
                        </Card>
                    </div>

                    {/* Charts */}
                    <Card className="bg-gradient-card border-border/50 p-6">
                        <h3 className="mb-6 text-xl font-bold text-foreground">Weekly Water Levels</h3>
                        <ResponsiveContainer width="100%" height={300}>
                            <AreaChart data={river.weekly_data}>
                                <CartesianGrid strokeDasharray="3 3" stroke="hsl(var(--border))" />
                                <XAxis dataKey="day" stroke="white" tick={{ fill: 'white' }} fontSize={12} />
                                <YAxis stroke="white" tick={{ fill: 'white' }} fontSize={12} />
                                <Tooltip
                                    contentStyle={{
                                        backgroundColor: 'hsl(var(--card))',
                                        border: '1px solid hsl(var(--border))',
                                        borderRadius: '8px',
                                        color: 'hsl(var(--foreground))',
                                    }}
                                />
                                <Area type="monotone" dataKey="level" stroke="#3b82f6" fill="#3b82f680" strokeWidth={2} />
                            </AreaChart>
                        </ResponsiveContainer>
                    </Card>

                    {/* Additional Info */}
                    <Card className="bg-gradient-card mt-8 border-border/50 p-6">
                        <h3 className="mb-4 text-xl font-bold text-foreground">Location Information</h3>
                        <div className="grid grid-cols-1 gap-6 md:grid-cols-2">
                            <div>
                                <h4 className="mb-2 font-semibold text-foreground">Coordinates</h4>
                                <p className="text-muted-foreground">
                                    Latitude: {river.lat}°<br />
                                    Longitude: {river.lng}°
                                </p>
                            </div>
                            <div>
                                <h4 className="mb-2 font-semibold text-foreground">Status Details</h4>
                                <p className="text-muted-foreground">
                                    Current level is {((river.current_water_level / river.normal_water_level) * 100).toFixed(1)}% of normal levels.
                                    {river.status === 'critical' && ' Immediate attention required.'}
                                    {river.status === 'high' && ' Monitoring recommended.'}
                                    {river.status === 'low' && ' Below average levels.'}
                                </p>
                            </div>
                        </div>
                    </Card>
                </div>
            </div>
        </>
    );
}
