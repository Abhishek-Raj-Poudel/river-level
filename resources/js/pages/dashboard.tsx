import echo from '@/app';
import NotificationButton from '@/components/notification-button';
import AppLayout from '@/layouts/app-layout';
import { dashboard } from '@/routes';
import { SharedData, type BreadcrumbItem } from '@/types';
import { Head, usePage } from '@inertiajs/react';
import { useEffect, useState } from 'react';
import LocationForm from './location-form';
import { Alert, AlertDescription, AlertTitle } from '@/components/ui/alert';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { MapPin, Droplets, AlertTriangle, CheckCircle2 } from 'lucide-react';

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

        if (!echo) {
            addDebugInfo('❌ Echo instance not found!');
            return;
        }

        try {
            const channel = echo.channel('river-levels');

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

            channel.listen('.river.level.exceeded', (e: RiverLevelEvent) => {
                addDebugInfo(`⚠️ River exceeded (with dot): ${e.river?.river_name || 'Unknown river'}`);
            });

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

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Dashboard" />

            <div className=" container mx-auto w-11/12 space-y-6 p-6">
                {/* Welcome Section */}
                <div>
                    <h1 className="text-3xl font-bold tracking-tight">Welcome back, {auth.user.name || 'user'}</h1>
                    <p className="text-muted-foreground mt-2">Monitor river levels and manage your flood alerts</p>
                </div>

                {/* Alert Section */}
                {event && (
                    <Alert variant="destructive" className="border-red-600 bg-red-50">
                        <AlertTriangle className="h-5 w-5" />
                        <AlertTitle className="text-lg font-semibold">Flood Alert</AlertTitle>
                        <AlertDescription className="mt-2 text-base">
                            {event}
                        </AlertDescription>
                    </Alert>
                )}

                {/* Main Content Grid */}
                <div className="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
                    {/* Location Card */}
                    <Card>
                        <CardHeader>
                            <CardTitle className="flex items-center gap-2">
                                <MapPin className="h-5 w-5 text-blue-600" />
                                Your Location
                            </CardTitle>
                            <CardDescription>Current monitoring position</CardDescription>
                        </CardHeader>
                        <CardContent>
                            <div className="space-y-2">
                                <div className="flex justify-between">
                                    <span className="text-sm text-muted-foreground">Latitude:</span>
                                    <span className="font-mono text-sm font-medium">{auth.user.lat || 'Not set'}</span>
                                </div>
                                <div className="flex justify-between">
                                    <span className="text-sm text-muted-foreground">Longitude:</span>
                                    <span className="font-mono text-sm font-medium">{auth.user.lng || 'Not set'}</span>
                                </div>
                            </div>
                        </CardContent>
                    </Card>

                    {/* Notification Status Card */}
                    <Card>
                        <CardHeader>
                            <CardTitle className="flex items-center gap-2">
                                <Droplets className="h-5 w-5 text-blue-600" />
                                Notifications
                            </CardTitle>
                            <CardDescription>Alert preferences</CardDescription>
                        </CardHeader>
                        <CardContent>
                            <NotificationButton />
                        </CardContent>
                    </Card>

                    {/* System Status Card */}
                    <Card>
                        <CardHeader>
                            <CardTitle className="flex items-center gap-2">
                                <CheckCircle2 className="h-5 w-5 text-green-600" />
                                System Status
                            </CardTitle>
                            <CardDescription>Monitoring service</CardDescription>
                        </CardHeader>
                        <CardContent>
                            <div className="flex items-center gap-2">
                                <Badge variant="outline" className="bg-green-50 text-green-700 border-green-300">
                                    Active
                                </Badge>
                                <span className="text-sm text-muted-foreground">All systems operational</span>
                            </div>
                        </CardContent>
                    </Card>
                </div>

                {/* Location Form Section */}
                <Card>
                    <CardHeader>
                        <CardTitle>Update Location</CardTitle>
                        <CardDescription>
                            Set your location to receive relevant flood alerts
                        </CardDescription>
                    </CardHeader>
                    <CardContent>
                        <LocationForm user={auth.user} />
                    </CardContent>
                </Card>
            </div>
        </AppLayout>
    );
}
