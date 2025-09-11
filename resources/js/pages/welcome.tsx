import { RiversList } from '@/components/river-list';
import { River } from '@/data/rivers';
import { Head } from '@inertiajs/react';
import { useState } from 'react';

interface Props {
    rivers: River[];
}

export default function Welcome({rivers}:Props) {
    console.log(rivers);
    // const { auth } = usePage<SharedData>().props;
    const [selectedRiver, setSelectedRiver] = useState<River | null>(null);

    const handleRiverSelect = (river: River) => {
        setSelectedRiver(river);
    };

    // const handleBackToList = () => {
    //   setSelectedRiver(null);
    // };
    return (
        <>
            <Head title="Welcome"></Head>
            <RiversList rivers={rivers} onRiverSelect={handleRiverSelect} />;
        </>
    );
}
