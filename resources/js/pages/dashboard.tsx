import { PlaceholderPattern } from '@/components/ui/placeholder-pattern';
import AppLayout from '@/layouts/app-layout';
import { dashboard } from '@/routes';
import { SharedData, User, type BreadcrumbItem } from '@/types';
import { Head, usePage } from '@inertiajs/react';
import LocationForm from './location-form';
import { useEffect, useState } from 'react';
import { Alert, AlertDescription, AlertTitle } from '@/components/ui/alert';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: dashboard().url,
    },
];

type AlertResponse = {
    alert: boolean;
    message: string;
    nearby_rivers: {
        id: number;
        river_name: string;
        level: number;
        threshold: number;
        distance: number;
    }[];
};

export default function Dashboard() {
    const [alert, setAlert] = useState<AlertResponse | null>(null);

    const { auth } = usePage<SharedData>().props;
    useEffect(() => {
        fetch("/alerts", { credentials: "include" })
            .then((res) => res.json())
            .then((data: AlertResponse) => setAlert(data))
            .catch(() => { });
    }, []);
    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Dashboard" />
            <div className="p-6">
                {alert?.alert && (


                    <Alert  variant="destructive">
                        <AlertTitle>Flood Alert 🚨</AlertTitle>
                        <AlertDescription>
                            <p>{alert?.message}</p>
                        </AlertDescription>
                    </Alert>

                )}
                {/* <div className="mb-4 rounded-xl bg-red-100 p-4 text-red-800 shadow"> */}
                {/*                         <h3 className="font-bold">Flood Alert 🚨</h3> */}
                {/*                         <p>{alert.message}</p> */}
                {/*                     </div> */}
                <h1 className="text-xl font-bold mb-4">Welcome, user </h1>
                <p>📍 Your location: {auth.user.lat}, {auth.user.lng}</p>
            </div>

            <LocationForm user={auth.user} />
        </AppLayout>
    );
}
