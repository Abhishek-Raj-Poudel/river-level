import { Input } from '@/components/ui/input';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { useReveal } from '@/hooks/use-reveal';
import { statusBadgeClass, statusDotClass, statusLabel, type RiverStatus } from '@/lib/status';
import { cn } from '@/lib/utils';
import { River } from '@/types';
import { Funnel, MagnifyingGlass, MapPin, Waves } from '@phosphor-icons/react';
import { useEffect, useMemo, useRef, useState } from 'react';
import { RiverCard } from './river-card';

interface RiversListProps {
    rivers: River[];
}

const STATUS_ORDER: RiverStatus[] = ['normal', 'low', 'high', 'critical'];

export const RiversList = ({ rivers }: RiversListProps) => {
    const [searchTerm, setSearchTerm] = useState('');
    const [statusFilter, setStatusFilter] = useState<RiverStatus | null>(null);
    const [districtFilter, setDistrictFilter] = useState<string | null>(null);
    const searchRef = useRef<HTMLInputElement>(null);
    const heroRef = useReveal<HTMLDivElement>();
    const controlsRef = useReveal<HTMLDivElement>();

    useEffect(() => {
        const handleKeyDown = (event: KeyboardEvent) => {
            const target = event.target as HTMLElement | null;
            const isTyping = target?.tagName === 'INPUT' || target?.tagName === 'TEXTAREA' || target?.isContentEditable;

            if (event.key === '/' && !isTyping) {
                event.preventDefault();
                searchRef.current?.focus();
            }
        };

        window.addEventListener('keydown', handleKeyDown);

        return () => window.removeEventListener('keydown', handleKeyDown);
    }, []);

    const districts = useMemo(() => {
        const uniqueDistricts = Array.from(new Set(rivers.map((r) => r.district).filter((d): d is string => Boolean(d))));
        return uniqueDistricts.sort();
    }, [rivers]);

    const filteredRivers = useMemo(() => {
        return rivers.filter((river) => {
            const haystack = [river.station_name, river.name, river.country, river.continent, river.district].filter(Boolean).join(' ').toLowerCase();

            const matchesSearch = haystack.includes(searchTerm.toLowerCase());
            const matchesStatus = !statusFilter || river.status === statusFilter;
            const matchesDistrict = !districtFilter || river.district === districtFilter;

            return matchesSearch && matchesStatus && matchesDistrict;
        });
    }, [rivers, searchTerm, statusFilter, districtFilter]);

    const statusCounts = useMemo(() => {
        return rivers.reduce(
            (counts, river) => {
                counts[river.status] += 1;
                return counts;
            },
            { low: 0, normal: 0, high: 0, critical: 0 } as Record<RiverStatus, number>,
        );
    }, [rivers]);

    const districtCounts = useMemo(() => {
        const counts: Record<string, number> = {};
        districts.forEach((district) => {
            counts[district] = rivers.filter((r) => r.district === district).length;
        });
        return counts;
    }, [districts, rivers]);

    return (
        <div className="relative flex-1">
            <div aria-hidden className="ambient-spot pointer-events-none absolute inset-x-0 top-0 h-[520px]" />

            <div className="relative">
                {/* Hero */}
                <div ref={heroRef} className="reveal container mx-auto max-w-7xl px-6 pt-20 pb-14 text-center md:pt-28">
                    <div className="mb-6 flex items-center justify-center gap-3">
                        <span className="flex items-center justify-center rounded-md bg-status-low-bg p-3 text-status-low-fg">
                            <Waves size={26} weight="fill" />
                        </span>
                        <span className="font-mono text-xs tracking-[0.18em] text-muted-foreground uppercase">Nepal Hydrology</span>
                    </div>
                    <h1 className="mx-auto max-w-3xl font-serif text-4xl leading-[1.05] tracking-[-0.03em] text-foreground md:text-6xl">
                        Live river levels across Nepal
                    </h1>
                    <p className="mx-auto mt-6 max-w-2xl text-lg leading-relaxed text-muted-foreground">
                        Water level, temperature and elevation readings from monitoring stations, updated throughout the day.
                    </p>
                </div>

                <div className="container mx-auto max-w-7xl px-6 pb-24">
                    {/* Controls */}
                    <div ref={controlsRef} className="reveal mb-10 space-y-5">
                        <div className="relative mx-auto max-w-2xl">
                            <MagnifyingGlass size={16} weight="bold" className="absolute top-1/2 left-3.5 -translate-y-1/2 text-muted-foreground" />
                            <Input
                                ref={searchRef}
                                placeholder="Search by river, station or district"
                                value={searchTerm}
                                onChange={(e) => setSearchTerm(e.target.value)}
                                className="h-11 border-border bg-card pr-14 pl-10 shadow-none"
                            />
                            <kbd className="pointer-events-none absolute top-1/2 right-3 -translate-y-1/2 rounded-[4px] border border-border bg-secondary px-1.5 py-0.5 font-mono text-[11px] text-muted-foreground">
                                /
                            </kbd>
                        </div>

                        <div className="flex flex-wrap items-center justify-center gap-x-6 gap-y-4">
                            <div className="flex items-center gap-2">
                                <MapPin size={15} weight="bold" className="text-muted-foreground" />
                                <span className="font-mono text-xs tracking-[0.12em] text-muted-foreground uppercase">District</span>
                                <div className="w-[210px]">
                                    <Select
                                        value={districtFilter ?? 'all'}
                                        onValueChange={(value) => setDistrictFilter(value === 'all' ? null : value)}
                                    >
                                        <SelectTrigger className="shadow-none">
                                            <SelectValue placeholder="All districts" />
                                        </SelectTrigger>
                                        <SelectContent>
                                            <SelectItem value="all">All Districts ({rivers.length})</SelectItem>
                                            {districts.map((district) => (
                                                <SelectItem key={district} value={district}>
                                                    {district} ({districtCounts[district] ?? 0})
                                                </SelectItem>
                                            ))}
                                        </SelectContent>
                                    </Select>
                                </div>
                            </div>

                            <div className="flex items-center gap-2">
                                <Funnel size={15} weight="bold" className="text-muted-foreground" />
                                <span className="font-mono text-xs tracking-[0.12em] text-muted-foreground uppercase">Status</span>
                            </div>

                            <div className="flex flex-wrap items-center justify-center gap-2">
                                <button
                                    type="button"
                                    onClick={() => setStatusFilter(null)}
                                    className={cn(
                                        'rounded-full border px-3 py-1 text-xs font-medium tracking-[0.05em] uppercase transition-colors',
                                        statusFilter === null
                                            ? 'border-foreground/20 bg-foreground text-background'
                                            : 'border-border bg-card text-muted-foreground hover:text-foreground',
                                    )}
                                >
                                    All ({rivers.length})
                                </button>

                                {STATUS_ORDER.map((status) => (
                                    <button
                                        key={status}
                                        type="button"
                                        onClick={() => setStatusFilter(statusFilter === status ? null : status)}
                                        className={cn(
                                            'flex items-center gap-1.5 rounded-full border px-3 py-1 text-xs font-medium tracking-[0.05em] uppercase transition-colors',
                                            statusFilter === status
                                                ? cn('border-transparent', statusBadgeClass(status))
                                                : 'border-border bg-card text-muted-foreground hover:text-foreground',
                                        )}
                                    >
                                        <span className={cn('h-1.5 w-1.5 rounded-full', statusDotClass(status))} />
                                        {statusLabel(status).replace(' Level', '')} ({statusCounts[status]})
                                    </button>
                                ))}
                            </div>
                        </div>
                    </div>

                    {/* Results count */}
                    <div className="mb-6">
                        <p className="font-mono text-xs tracking-[0.12em] text-muted-foreground uppercase">
                            {filteredRivers.length} of {rivers.length} stations
                        </p>
                    </div>

                    {/* Rivers Grid */}
                    {filteredRivers.length > 0 ? (
                        <div className="grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-3">
                            {filteredRivers.map((river, index) => (
                                <RiverCard key={river.id} river={river} index={index} />
                            ))}
                        </div>
                    ) : (
                        <div className="border border-dashed border-border py-20 text-center">
                            <span className="mx-auto mb-4 flex h-12 w-12 items-center justify-center rounded-md bg-secondary text-muted-foreground">
                                <MagnifyingGlass size={22} weight="bold" />
                            </span>
                            <h3 className="mb-1 font-serif text-xl text-foreground">No stations found</h3>
                            <p className="text-sm text-muted-foreground">Try a different search term or clear the filters.</p>
                        </div>
                    )}
                </div>
            </div>
        </div>
    );
};
