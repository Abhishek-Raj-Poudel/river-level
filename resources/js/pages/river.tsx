import { River } from '@/data/rivers';
import { Head } from '@inertiajs/react';
import { RiverDetail } from './river-detail';

export default function RiverPage({ river }: { river: River }) {
    return (
        <>
            <Head title={river.name} />
            <RiverDetail river={river} />

        </>
    );
}
