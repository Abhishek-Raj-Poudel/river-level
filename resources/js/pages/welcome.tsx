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
            <Head title="Nepal River Levels" />
            <div className="flex min-h-screen flex-col bg-background">
                <WelcomeNavbar />
                <RiversList rivers={rivers} />
            </div>
        </>
    );
}
