import { LevelBar } from '@/components/level-bar';
import { Badge } from '@/components/ui/badge';
import { Card } from '@/components/ui/card';
import { WelcomeNavbar } from '@/components/welcome-navbar';
import { useReveal } from '@/hooks/use-reveal';
import { statusBadgeClass, statusLabel } from '@/lib/status';
import { cn } from '@/lib/utils';
import { River } from '@/types';
import { Link } from '@inertiajs/react';
import { ArrowLeft, ArrowSquareOut, CalendarBlank, Drop, MapPin, Mountains, Pulse, Ruler, Thermometer } from '@phosphor-icons/react';
import { lazy, Suspense } from 'react';
import { Area, AreaChart, CartesianGrid, ResponsiveContainer, Tooltip, XAxis, YAxis } from 'recharts';

const MapContainer = lazy(() => import('react-leaflet').then((mod) => ({ default: mod.MapContainer })));
const TileLayer = lazy(() => import('react-leaflet').then((mod) => ({ default: mod.TileLayer })));
const Marker = lazy(() => import('react-leaflet').then((mod) => ({ default: mod.Marker })));
const Popup = lazy(() => import('react-leaflet').then((mod) => ({ default: mod.Popup })));

interface Props {
    river: River;
}

const dateFormatter = new Intl.DateTimeFormat('en-GB', {
    dateStyle: 'medium',
    timeStyle: 'short',
    timeZone: 'Asia/Kathmandu',
});

const chartDateFormatter = new Intl.DateTimeFormat('en-GB', { day: '2-digit', month: 'short', timeZone: 'Asia/Kathmandu' });

export default function RiverDetail({ river }: Props) {
    const headerRef = useReveal<HTMLDivElement>();
    const statsRef = useReveal<HTMLDivElement>();
    const chartRef = useReveal<HTMLDivElement>();
    const mapRef = useReveal<HTMLDivElement>();
    const infoRef = useReveal<HTMLDivElement>();

    const lastUpdated = dateFormatter.format(new Date(river.last_updated));

    const sortedMeasurements = [...(river.recent_measurements ?? [])].sort(
        (a, b) => new Date(a.measured_at).getTime() - new Date(b.measured_at).getTime(),
    );

    const hasBaseline = Number.isFinite(river.normal_water_level) && river.normal_water_level > 0;
    const normalPercent = hasBaseline ? (river.current_water_level / river.normal_water_level) * 100 : 0;

    return (
        <>
            <div className="flex min-h-screen flex-col bg-background">
                <WelcomeNavbar />
                <div className="relative flex-1">
                    <div aria-hidden className="ambient-spot pointer-events-none absolute inset-x-0 top-0 h-[420px]" />

                    <div className="relative container mx-auto max-w-5xl px-6 py-10">
                        {/* Back */}
                        <Link
                            href="/"
                            className="mb-8 inline-flex items-center gap-1.5 font-mono text-xs tracking-[0.12em] text-muted-foreground uppercase transition-colors hover:text-foreground"
                        >
                            <ArrowLeft size={14} weight="bold" />
                            All stations
                        </Link>

                        {/* Header */}
                        <div ref={headerRef} className="reveal mb-12">
                            <div className="mb-4 flex flex-col gap-4 md:flex-row md:items-start md:justify-between">
                                <div>
                                    <h1 className="font-serif text-4xl leading-[1.08] tracking-[-0.03em] text-foreground md:text-5xl">
                                        {river.station_name || river.name}
                                    </h1>
                                    <div className="mt-3 flex flex-wrap items-center gap-x-5 gap-y-2 text-sm text-muted-foreground">
                                        <span className="flex items-center gap-1.5">
                                            <MapPin size={14} weight="bold" />
                                            {river.district ? `${river.district}, ` : ''}
                                            {river.country}
                                        </span>
                                        <span className="flex items-center gap-1.5">
                                            <CalendarBlank size={14} weight="bold" />
                                            Updated {lastUpdated}
                                        </span>
                                    </div>
                                </div>
                                <Badge
                                    className={cn(
                                        'shrink-0 self-start border-0 px-3 py-1 text-xs font-medium tracking-[0.05em] uppercase',
                                        statusBadgeClass(river.status),
                                    )}
                                >
                                    {statusLabel(river.status)}
                                </Badge>
                            </div>
                            <p className="max-w-3xl text-base leading-relaxed text-muted-foreground">{river.description}</p>
                        </div>

                        {/* Stats */}
                        <div ref={statsRef} className="reveal mb-6 grid grid-cols-1 gap-4 md:grid-cols-3">
                            <Card className="gap-0 border-border bg-card p-6 shadow-none">
                                <div className="mb-4 flex items-center gap-2.5">
                                    <span className="flex items-center justify-center rounded-md bg-status-low-bg p-2 text-status-low-fg">
                                        <Drop size={16} weight="bold" />
                                    </span>
                                    <h3 className="font-mono text-xs tracking-[0.12em] text-muted-foreground uppercase">Water Level</h3>
                                </div>
                                <div className="font-mono text-3xl text-foreground">{river.current_water_level}m</div>
                                <div className="mt-1 text-sm text-muted-foreground">
                                    Normal {river.normal_water_level}m{hasBaseline && ` · ${Math.round(normalPercent)}%`}
                                </div>
                                <LevelBar
                                    current={river.current_water_level}
                                    normal={river.normal_water_level}
                                    status={river.status}
                                    className="mt-4"
                                />
                            </Card>

                            <Card className="gap-0 border-border bg-card p-6 shadow-none">
                                <div className="mb-4 flex items-center gap-2.5">
                                    <span className="flex items-center justify-center rounded-md bg-secondary p-2 text-muted-foreground">
                                        <Thermometer size={16} weight="bold" />
                                    </span>
                                    <h3 className="font-mono text-xs tracking-[0.12em] text-muted-foreground uppercase">Temperature</h3>
                                </div>
                                <div className="font-mono text-3xl text-foreground">{river.temperature}°C</div>
                                <div className="mt-1 text-sm text-muted-foreground">Current water temp</div>
                            </Card>

                            <Card className="gap-0 border-border bg-card p-6 shadow-none">
                                <div className="mb-4 flex items-center gap-2.5">
                                    <span className="flex items-center justify-center rounded-md bg-secondary p-2 text-muted-foreground">
                                        <Mountains size={16} weight="bold" />
                                    </span>
                                    <h3 className="font-mono text-xs tracking-[0.12em] text-muted-foreground uppercase">Elevation</h3>
                                </div>
                                <div className="font-mono text-3xl text-foreground">
                                    {river.elevation !== null && river.elevation !== undefined ? `${river.elevation}m` : 'N/A'}
                                </div>
                                <div className="mt-1 flex items-center gap-1.5 text-sm text-muted-foreground">
                                    <Ruler size={13} weight="bold" />
                                    {river.length.toLocaleString()} km river length
                                </div>
                            </Card>
                        </div>

                        {/* Chart */}
                        <Card ref={chartRef} className="reveal mb-6 gap-0 border-border bg-card p-6 shadow-none">
                            <h3 className="mb-6 font-mono text-xs tracking-[0.12em] text-muted-foreground uppercase">Water Level · Last 7 Days</h3>
                            {sortedMeasurements.length > 0 ? (
                                <ResponsiveContainer width="100%" height={300}>
                                    <AreaChart data={sortedMeasurements}>
                                        <CartesianGrid strokeDasharray="3 3" stroke="var(--border)" vertical={false} />
                                        <XAxis
                                            dataKey="measured_at"
                                            stroke="var(--border)"
                                            tick={{ fill: 'var(--muted-foreground)', fontSize: 11 }}
                                            tickLine={false}
                                            axisLine={false}
                                            tickFormatter={(value) => chartDateFormatter.format(new Date(value))}
                                        />
                                        <YAxis
                                            stroke="var(--border)"
                                            tick={{ fill: 'var(--muted-foreground)', fontSize: 11 }}
                                            tickLine={false}
                                            axisLine={false}
                                            width={40}
                                        />
                                        <Tooltip
                                            cursor={{ stroke: 'var(--border)' }}
                                            contentStyle={{
                                                backgroundColor: 'var(--card)',
                                                border: '1px solid var(--border)',
                                                borderRadius: '8px',
                                                color: 'var(--foreground)',
                                                fontSize: '12px',
                                            }}
                                            labelFormatter={(value) => dateFormatter.format(new Date(value))}
                                        />
                                        <Area
                                            type="monotone"
                                            dataKey="water_level"
                                            stroke="var(--chart-1)"
                                            strokeWidth={1.5}
                                            fill="var(--chart-3)"
                                            fillOpacity={0.14}
                                        />
                                    </AreaChart>
                                </ResponsiveContainer>
                            ) : (
                                <div className="flex h-[300px] items-center justify-center border border-dashed border-border text-sm text-muted-foreground">
                                    No measurements recorded for this station yet.
                                </div>
                            )}
                        </Card>

                        {/* Map */}
                        <Card ref={mapRef} className="reveal mb-6 gap-0 overflow-hidden border-border bg-card p-0 shadow-none">
                            <div className="flex items-center justify-between border-b border-border px-6 py-4">
                                <h3 className="font-mono text-xs tracking-[0.12em] text-muted-foreground uppercase">Station Location</h3>
                                <span className="font-mono text-[11px] text-muted-foreground">
                                    {river.lat}°, {river.lng}°
                                </span>
                            </div>
                            <div className="map-tiles h-96 w-full">
                                <Suspense
                                    fallback={
                                        <div className="flex h-full w-full items-center justify-center bg-secondary">
                                            <div className="text-center">
                                                <MapPin size={28} weight="bold" className="mx-auto mb-2 text-muted-foreground" />
                                                <p className="text-sm text-muted-foreground">Loading map…</p>
                                            </div>
                                        </div>
                                    }
                                >
                                    <MapContainer
                                        center={[river.lat, river.lng]}
                                        zoom={10}
                                        style={{ height: '100%', width: '100%' }}
                                        scrollWheelZoom={false}
                                    >
                                        <TileLayer
                                            attribution='&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
                                            url="https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png"
                                        />
                                        <Marker position={[river.lat, river.lng]}>
                                            <Popup>
                                                <div className="text-center">
                                                    <h4 className="font-semibold">{river.station_name || river.name}</h4>
                                                    <p className="text-sm text-muted-foreground">
                                                        {river.country}, {river.continent}
                                                    </p>
                                                </div>
                                            </Popup>
                                        </Marker>
                                    </MapContainer>
                                </Suspense>
                            </div>
                        </Card>

                        {/* Location information */}
                        <Card ref={infoRef} className="reveal gap-0 border-border bg-card p-6 shadow-none">
                            <h3 className="mb-6 font-mono text-xs tracking-[0.12em] text-muted-foreground uppercase">Station Details</h3>
                            <dl className="grid grid-cols-1 gap-x-8 gap-y-6 md:grid-cols-2">
                                <div>
                                    <dt className="mb-1 text-xs tracking-[0.08em] text-muted-foreground uppercase">Coordinates</dt>
                                    <dd className="font-mono text-sm text-foreground">
                                        {river.lat}°, {river.lng}°
                                    </dd>
                                </div>
                                <div>
                                    <dt className="mb-1 text-xs tracking-[0.08em] text-muted-foreground uppercase">Elevation</dt>
                                    <dd className="text-sm text-foreground">
                                        {river.elevation !== null && river.elevation !== undefined ? `${river.elevation} m` : 'Not available'}
                                    </dd>
                                </div>
                                <div>
                                    <dt className="mb-1 text-xs tracking-[0.08em] text-muted-foreground uppercase">Contact</dt>
                                    <dd className="text-sm text-foreground">{river.contact_number || 'No contact information available'}</dd>
                                </div>
                                <div>
                                    <dt className="mb-1 text-xs tracking-[0.08em] text-muted-foreground uppercase">Station Page</dt>
                                    <dd className="text-sm">
                                        {river.station_link ? (
                                            <a
                                                href={river.station_link}
                                                target="_blank"
                                                rel="noreferrer"
                                                className="inline-flex items-center gap-1 text-foreground underline underline-offset-4 hover:text-muted-foreground"
                                            >
                                                Open station page
                                                <ArrowSquareOut size={13} weight="bold" />
                                            </a>
                                        ) : (
                                            <span className="text-muted-foreground">Not available</span>
                                        )}
                                    </dd>
                                </div>
                                <div className="md:col-span-2">
                                    <dt className="mb-1 text-xs tracking-[0.08em] text-muted-foreground uppercase">Nearby Landmarks</dt>
                                    <dd className="text-sm text-foreground">{river.nearby_landmark || 'No nearby landmarks specified'}</dd>
                                </div>
                                <div className="md:col-span-2">
                                    <dt className="mb-1 text-xs tracking-[0.08em] text-muted-foreground uppercase">Status</dt>
                                    <dd className="flex items-start gap-2 text-sm text-foreground">
                                        <Pulse size={15} weight="bold" className="mt-0.5 shrink-0 text-muted-foreground" />
                                        <span>
                                            Current level is {hasBaseline ? `${normalPercent.toFixed(1)}%` : 'n/a'} of normal.
                                            {river.status === 'critical' && ' Immediate attention required.'}
                                            {river.status === 'high' && ' Monitoring recommended.'}
                                            {river.status === 'low' && ' Below average levels.'}
                                        </span>
                                    </dd>
                                </div>
                            </dl>
                        </Card>
                    </div>
                </div>
            </div>
        </>
    );
}
