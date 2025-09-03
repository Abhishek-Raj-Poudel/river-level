import { PlaceholderPattern } from '@/components/ui/placeholder-pattern';
import AppLayout from '@/layouts/app-layout';
import { dashboard } from '@/routes';
import { SharedData, User, type BreadcrumbItem } from '@/types';
import { Head, usePage } from '@inertiajs/react';
import LocationForm from './location-form';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: dashboard().url,
    },
];


export default function Dashboard() {

    const { auth } = usePage<SharedData>().props;
    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Dashboard" />
            <div className="p-6">
                <h1 className="text-xl font-bold mb-4">Welcome, user </h1>
                <p>📍 Your location: {auth.user.lat}, {auth.user.lng}</p>
            </div>

            <LocationForm user={auth.user} />
        </AppLayout>
    );
}
