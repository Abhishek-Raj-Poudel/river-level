import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { useMemo, useState } from 'react';

import { River } from '@/types';
import { Filter, MapPin, Search, Waves } from 'lucide-react';
import { RiverCard } from './river-card';

import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';

interface RiversListProps {
    rivers: River[];
}

export const RiversList = ({ rivers }: RiversListProps) => {
    const [searchTerm, setSearchTerm] = useState('');
    const [statusFilter, setStatusFilter] = useState<string | null>(null);
    const [districtFilter, setDistrictFilter] = useState<string | null>('Kathmandu');

    // Get unique districts from rivers
    const districts = useMemo(() => {
        const uniqueDistricts = Array.from(new Set(rivers.map((r) => r.district).filter((d): d is string => Boolean(d))));
        return uniqueDistricts.sort();
    }, [rivers]);

    const filteredRivers = rivers.filter((river) => {
        const matchesSearch =
            (river.station_name || river.name).toLowerCase().includes(searchTerm.toLowerCase()) ||
            river.name.toLowerCase().includes(searchTerm.toLowerCase()) ||
            river.country.toLowerCase().includes(searchTerm.toLowerCase()) ||
            river.continent.toLowerCase().includes(searchTerm.toLowerCase()) ||
            (river.district && river.district.toLowerCase().includes(searchTerm.toLowerCase()));

        const matchesStatus = !statusFilter || river.status === statusFilter;
        const matchesDistrict = !districtFilter || districtFilter === 'all' || river.district === districtFilter;

        return matchesSearch && matchesStatus && matchesDistrict;
    });

    const statusCounts = {
        low: rivers.filter((r) => r.status === 'low').length,
        normal: rivers.filter((r) => r.status === 'normal').length,
        high: rivers.filter((r) => r.status === 'high').length,
        critical: rivers.filter((r) => r.status === 'critical').length,
    };

    const districtCounts = useMemo(() => {
        const counts: Record<string, number> = {};
        districts.forEach((district) => {
            counts[district] = rivers.filter((r) => r.district === district).length;
        });
        return counts;
    }, [districts, rivers]);

    return (
        <div className="bg-gradient-background min-h-screen">
            <div className="container mx-auto max-w-7xl px-4 py-8">
                {/* Header */}
                <div className="mb-12 text-center">
                    <div className="mb-4 flex items-center justify-center gap-3">
                        <div className="rounded-xl bg-primary/20 p-3">
                            <Waves className="h-8 w-8 text-primary" />
                        </div>
                        <h1 className="text-4xl font-bold text-foreground md:text-5xl">River Monitor</h1>
                    </div>
                    <p className="mx-auto max-w-2xl text-xl text-muted-foreground">
                        Monitoring of Nepal's major rivers with water levels, temperature, and environmental data
                    </p>
                </div>

                {/* Search and Filters */}
                <div className="mb-8 space-y-4">
                    <div className="relative mx-auto w-full">
                        <Search className="absolute top-1/2 left-3 h-4 w-4 -translate-y-1/2 transform text-muted-foreground" />
                        <Input
                            placeholder="Search rivers"
                            value={searchTerm}
                            onChange={(e) => setSearchTerm(e.target.value)}
                            className="border-border/50 pl-10 focus:border-primary"
                        />
                    </div>

                    {/* District Filters */}
                    <div className="flex flex-wrap items-center justify-center gap-4">
                        <div className="flex items-center gap-2">
                            <MapPin className="h-4 w-4 text-muted-foreground" />
                            <span className="text-sm text-muted-foreground">Filter by district:</span>
                            <div className="w-[200px]">
                                <Select value={districtFilter || 'all'} onValueChange={(value) => setDistrictFilter(value === 'all' ? null : value)}>
                                    <SelectTrigger>
                                        <SelectValue placeholder="Select district" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="all">All Districts ({rivers.length})</SelectItem>
                                        {districts.map((district) => (
                                            <SelectItem key={district} value={district}>
                                                {district} ({districtCounts[district] || 0})
                                            </SelectItem>
                                        ))}
                                    </SelectContent>
                                </Select>
                            </div>
                        </div>

                        {districtFilter && districtFilter !== 'all' && (
                            <div className="flex items-center gap-2 rounded-full bg-primary/10 px-4 py-1.5 text-sm font-medium text-primary">
                                <span>Showing rivers in:</span>
                                <span className="font-bold">{districtFilter}</span>
                            </div>
                        )}
                    </div>

                    {/* Status Filters */}
                    <div className="flex flex-wrap items-center justify-center gap-2">
                        <div className="mr-4 flex items-center gap-1">
                            <Filter className="h-4 w-4 text-muted-foreground" />
                            <span className="text-sm text-muted-foreground">Filter by status:</span>
                        </div>

                        <Button
                            variant={statusFilter === null ? 'default' : 'outline'}
                            size="sm"
                            onClick={() => setStatusFilter(null)}
                            className="h-8"
                        >
                            All ({rivers.length})
                        </Button>

                        <Button
                            variant={statusFilter === 'normal' ? 'default' : 'outline'}
                            size="sm"
                            onClick={() => setStatusFilter(statusFilter === 'normal' ? null : 'normal')}
                            className="h-8"
                        >
                            <div className="mr-2 h-2 w-2 rounded-full bg-water-normal" />
                            Normal ({statusCounts.normal})
                        </Button>

                        <Button
                            variant={statusFilter === 'low' ? 'default' : 'outline'}
                            size="sm"
                            onClick={() => setStatusFilter(statusFilter === 'low' ? null : 'low')}
                            className="h-8"
                        >
                            <div className="mr-2 h-2 w-2 rounded-full bg-water-low" />
                            Low ({statusCounts.low})
                        </Button>

                        <Button
                            variant={statusFilter === 'high' ? 'default' : 'outline'}
                            size="sm"
                            onClick={() => setStatusFilter(statusFilter === 'high' ? null : 'high')}
                            className="h-8"
                        >
                            <div className="mr-2 h-2 w-2 rounded-full bg-water-high" />
                            High ({statusCounts.high})
                        </Button>

                        <Button
                            variant={statusFilter === 'critical' ? 'default' : 'outline'}
                            size="sm"
                            onClick={() => setStatusFilter(statusFilter === 'critical' ? null : 'critical')}
                            className="h-8"
                        >
                            <div className="mr-2 h-2 w-2 rounded-full bg-water-critical" />
                            Critical ({statusCounts.critical})
                        </Button>
                    </div>
                </div>

                {/* Results count */}
                <div className="mb-6 text-center">
                    <p className="text-muted-foreground">
                        Showing {filteredRivers.length} of {rivers.length} rivers
                    </p>
                </div>

                {/* Rivers Grid */}
                <div className="grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-3">
                    {filteredRivers.map((river) => (
                        <RiverCard key={river.id} river={river} />
                    ))}
                </div>

                {/* No results */}
                {filteredRivers.length === 0 && (
                    <div className="py-12 text-center">
                        <div className="mb-4 inline-block rounded-xl bg-muted/20 p-4">
                            <Search className="h-8 w-8 text-muted-foreground" />
                        </div>
                        <h3 className="mb-2 text-xl font-semibold text-foreground">No rivers found</h3>
                        <p className="text-muted-foreground">Try adjusting your search terms or filters</p>
                    </div>
                )}
            </div>
        </div>
    );
};
