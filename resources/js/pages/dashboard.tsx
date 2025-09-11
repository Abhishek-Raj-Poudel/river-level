import echo from '@/app';
import NotificationButton from '@/components/notification-button';
import AppLayout from '@/layouts/app-layout';
import { dashboard } from '@/routes';
import { SharedData, type BreadcrumbItem } from '@/types';
import { Head, usePage } from '@inertiajs/react';
import { useEffect, useState } from 'react';
import LocationForm from './location-form';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: dashboard().url,
    },
];

interface RiverData {
    id: number;
    river_name: string;
    lat: number;
    lng: number;
    level: number;
    threshold: number;
    exceeded_by: number;
}

interface RiverLevelEvent {
    river: RiverData;
    timestamp: string;
}

export default function Dashboard() {
    const { auth } = usePage<SharedData>().props;

    const [event, setEvent] = useState<string | null>(null);

    const [connectionStatus, setConnectionStatus] = useState<string>('Connecting...');
    const [debugInfo, setDebugInfo] = useState<string[]>([]);

    const addDebugInfo = (message: string) => {
        const timestamp = new Date().toLocaleTimeString();
        setDebugInfo((prev) => [`[${timestamp}] ${message}`, ...prev.slice(0, 9)]);
        console.log(message);
    };

    useEffect(() => {
        addDebugInfo('🔌 Setting up river level listener...');

        // Check if Echo is properly initialized
        if (!echo) {
            addDebugInfo('❌ Echo instance not found!');
            return;
        }

        // addDebugInfo(`📡 Echo instance found: ${echo.connector?.name || 'unknown connector'}`);

        try {
            const channel = echo.channel('river-levels');

            // Your main event listener
            channel.listen('river.level.exceeded', (e: RiverLevelEvent) => {
                addDebugInfo(`⚠️ River exceeded: ${e.river?.river_name || 'Unknown river'}`);
                setEvent(`Water level ${e.river.river_name} exceeds threshold of ${e.river.threshold}m`);

                if (Notification.permission === 'granted') {
                    new Notification(`River Alert: ${e?.river?.river_name}`, {
                        body: `Water level ${e.river.river_name} exceeds threshold of ${e.river.threshold}m`,
                        icon: '/favicon.ico',
                    });
                }
            });

            // Also try listening without the dot prefix (in case that's the issue)
            channel.listen('.river.level.exceeded', (e: RiverLevelEvent) => {
                addDebugInfo(`⚠️ River exceeded (with dot): ${e.river?.river_name || 'Unknown river'}`);
            });

            // And try the default Laravel event name format
            channel.listen('RiverLevelExceeded', (e: RiverLevelEvent) => {
                addDebugInfo(`⚠️ River exceeded (default name): ${e.river?.river_name || 'Unknown river'}`);
            });

            addDebugInfo('📻 Event listeners attached');
        } catch (error) {
            addDebugInfo(`❌ Error setting up channel: ${error}`);
        }

        return () => {
            addDebugInfo('🔌 Cleaning up river level listener...');
            try {
                echo.leaveChannel('river-levels');
                addDebugInfo('✅ Channel cleanup completed');
            } catch (error) {
                addDebugInfo(`❌ Error during cleanup: ${error}`);
            }
        };
    }, []);

    const testConnection = () => {
        addDebugInfo('🧪 Testing connection...');

        // Check if we can access the channel
        const channel = echo.channel('river-levels');
        addDebugInfo(`Channel state: ${channel ? 'exists' : 'null'}`);

        // Try to get connection info
        // if (echo.connector?.pusher) {
        //     const connectionState = echo.connector.pusher.connection.state;
        //     addDebugInfo(`Pusher connection state: ${connectionState}`);
        // }
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Dashboard" />

            <div className="p-6">
                <div className="mb-4 rounded-xl bg-gray-100 p-4 text-red-800 shadow">
                    <h3 className="font-bold">Flood Alert 🚨</h3>
                    <p>{event}</p>
                </div>
            </div>

            <div className="p-6">
                <h1 className="mb-4 text-xl font-bold">Welcome, user </h1>
                <p>
                    📍 Your location: {auth.user.lat}, {auth.user.lng}
                </p>
            </div>
            <div className="p-6">
                <NotificationButton />
            </div>

            <LocationForm user={auth.user} />
        </AppLayout>
    );
}
