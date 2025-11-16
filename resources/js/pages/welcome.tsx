import { RiversList } from '@/components/river-list';
import { River, RiverNew } from '@/types';
import { Head } from '@inertiajs/react';
import { useState } from 'react';

interface Props {
    rivers: River[];
    rivers_new: RiverNew[];
}

export default function Welcome({ rivers, rivers_new }: Props) {
    // console.log(rivers_new);
    const [selectedRiver, setSelectedRiver] = useState<River | null>(null);

    const handleRiverSelect = (river: River) => {
        setSelectedRiver(river);
    };

    return (
        <>
            <Head title="Welcome"></Head>
            <RiversList rivers={rivers} rivers_new={rivers_new} onRiverSelect={handleRiverSelect} />;
        </>
    );
}
