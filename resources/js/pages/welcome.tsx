import { RiversList } from '@/components/river-list';
import { River, RiverNew } from '@/types';
import { Head } from '@inertiajs/react';

interface Props {
    rivers: River[];
    rivers_new: RiverNew[];
}

export default function Welcome({ rivers, rivers_new }: Props) {
    return (
        <>
            <Head title="Welcome"></Head>
            <RiversList rivers={rivers} rivers_new={rivers_new} />;
        </>
    );
}
