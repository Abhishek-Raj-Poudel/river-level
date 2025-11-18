import { River } from '@/types';
import { Head } from '@inertiajs/react';
import RiverDetail from './river-detail';

interface Props {
    river: River;
}

export default function RiverPage({ river }: Props) {
    return (
        <>
            <Head title={river.station_name || river.name} />
            <RiverDetail river={river} />
        </>
    );
}
