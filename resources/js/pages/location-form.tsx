import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { useForm } from '@inertiajs/react';
import { FormEvent } from 'react';
import { MapPin, Navigation, Loader2 } from 'lucide-react';

interface User {
    id: number;
    name: string;
    email: string;
    lat?: number | null;
    lng?: number | null;
}

interface LocationFormProps {
    user: User;
}

export default function LocationForm({ user }: LocationFormProps) {
    const { data, setData, post, processing } = useForm({
        lat: user?.lat?.toString() || '',
        lng: user?.lng?.toString() || '',
    });

    const handleSubmit = (e: FormEvent) => {
        e.preventDefault();
        post('/user/location');
    };

    const handleUseMyLocation = () => {
        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(
                (position: GeolocationPosition) => {
                    setData('lat', position.coords.latitude.toString());
                    setData('lng', position.coords.longitude.toString());
                },
                (error: GeolocationPositionError) => {
                    alert('Unable to fetch location.');
                    console.error(error);
                },
            );
        } else {
            alert('Geolocation not supported.');
        }
    };

    return (
        <form onSubmit={handleSubmit} className="space-y-6">
            <div className="grid gap-6 sm:grid-cols-2">
                {/* Latitude Input */}
                <div className="space-y-2">
                    <Label htmlFor="latitude" className="text-sm font-medium">
                        Latitude
                    </Label>
                    <div className="relative">
                        <MapPin className="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground" />
                        <Input
                            id="latitude"
                            type="text"
                            value={data.lat}
                            onChange={(e) => setData('lat', e.target.value)}
                            placeholder="27.7172"
                            className="pl-10"
                        />
                    </div>
                </div>

                {/* Longitude Input */}
                <div className="space-y-2">
                    <Label htmlFor="longitude" className="text-sm font-medium">
                        Longitude
                    </Label>
                    <div className="relative">
                        <MapPin className="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground" />
                        <Input
                            id="longitude"
                            type="text"
                            value={data.lng}
                            onChange={(e) => setData('lng', e.target.value)}
                            placeholder="85.3240"
                            className="pl-10"
                        />
                    </div>
                </div>
            </div>

            {/* Action Buttons */}
            <div className="flex flex-col gap-3 sm:flex-row">
                <Button
                    type="button"
                    onClick={handleUseMyLocation}
                    variant="outline"
                    className="w-full sm:w-auto"
                >
                    <Navigation className="mr-2 h-4 w-4" />
                    Use My Location
                </Button>
                <Button
                    type="submit"
                    disabled={processing}
                    className="w-full sm:flex-1"
                >
                    {processing ? (
                        <>
                            <Loader2 className="mr-2 h-4 w-4 animate-spin" />
                            Saving...
                        </>
                    ) : (
                        <>
                            <MapPin className="mr-2 h-4 w-4" />
                            Save Location
                        </>
                    )}
                </Button>
            </div>
        </form>
    );
}
