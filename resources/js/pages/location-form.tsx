import { Button } from '@/components/ui/button';
import { useForm } from '@inertiajs/react';
import { FormEvent } from 'react';

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
        <div className="max-w-md rounded-xl p-6 shadow">
            <h2 className="mb-4 text-lg font-semibold">Set Your Location</h2>

            <form onSubmit={handleSubmit} className="space-y-4">
                <div className="flex w-full gap-2">
                    <input
                        type="text"
                        value={data.lat}
                        onChange={(e) => setData('lat', e.target.value)}
                        placeholder="Latitude"
                        className="w-full rounded border p-2"
                    />
                    <input
                        type="text"
                        value={data.lng}
                        onChange={(e) => setData('lng', e.target.value)}
                        placeholder="Longitude"
                        className="w-full rounded border p-2"
                    />
                </div>

                <Button type="button" onClick={handleUseMyLocation} className="w-full">
                    Use My Location
                </Button>

                <Button type="submit" disabled={processing} variant="secondary" className="w-full">
                    Save Location
                </Button>
            </form>
        </div>
    );
}
