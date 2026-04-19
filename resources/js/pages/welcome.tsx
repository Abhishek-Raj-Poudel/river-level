import { RiversList } from '@/components/river-list';
import { WelcomeNavbar } from '@/components/welcome-navbar';
import { River } from '@/types';
import { Head, router } from '@inertiajs/react';
import { useEffect } from 'react';

interface Props {
    rivers: River[];
}

export default function Welcome({ rivers }: Props) {
    useEffect(() => {
        router.reload({
            only: ['rivers'],
            preserveScroll: true,
            preserveState: true,
        });
    }, []);

    return (
        <>
            <Head title="Welcome" />
            <WelcomeNavbar />
            <RiversList rivers={rivers} />
        </>
    );
}
