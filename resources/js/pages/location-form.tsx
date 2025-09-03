import { FormEvent } from "react";
import { useForm } from "@inertiajs/react";

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
    lat: user?.lat?.toString() || "",
    lng: user?.lng?.toString() || "",
  });

  const handleSubmit = (e: FormEvent) => {
    e.preventDefault();
    post('/user/location');
  };

  const handleUseMyLocation = () => {
    if (navigator.geolocation) {
      navigator.geolocation.getCurrentPosition(
        (position: GeolocationPosition) => {
          setData("lat", position.coords.latitude.toString());
          setData("lng", position.coords.longitude.toString());
        },
        (error: GeolocationPositionError) => {
          alert("Unable to fetch location.");
          console.error(error);
        }
      );
    } else {
      alert("Geolocation not supported.");
    }
  };

  return (
    <div className="max-w-md mx-auto p-6 bg-white shadow rounded-xl">
      <h2 className="text-lg font-semibold mb-4">Set Your Location</h2>

      <form onSubmit={handleSubmit} className="space-y-4">
        <input
          type="text"
          value={data.lat}
          onChange={(e) => setData("lat", e.target.value)}
          placeholder="Latitude"
          className="w-full border p-2 rounded"
        />
        <input
          type="text"
          value={data.lng}
          onChange={(e) => setData("lng", e.target.value)}
          placeholder="Longitude"
          className="w-full border p-2 rounded"
        />

        <button
          type="button"
          onClick={handleUseMyLocation}
          className="w-full bg-blue-500 text-white py-2 rounded"
        >
          Use My Location
        </button>

        <button
          type="submit"
          disabled={processing}
          className="w-full bg-green-500 text-white py-2 rounded"
        >
          Save Location
        </button>
      </form>
    </div>
  );
}
