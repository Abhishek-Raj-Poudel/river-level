import { RiversList } from '@/components/river-list';
import { WelcomeNavbar } from '@/components/welcome-navbar';
import { River } from '@/types';
import { Head } from '@inertiajs/react';

interface Props {
    rivers: River[];
}

export default function Welcome({ rivers }: Props) {
    return (
        <>
            <Head title="Welcome" />
            <WelcomeNavbar />
            <RiversList rivers={rivers} />
        </>
    );
}
