import { useEffect, useState } from 'react';

export default function NotificationButton() {
    const [permission, setPermission] = useState<NotificationPermission>('default');
    const [isRequesting, setIsRequesting] = useState(false);

    useEffect(() => {
        if ('Notification' in window) {
            setPermission(Notification.permission);
        }
    }, []);

    const enableNotifications = async () => {
        if (!('Notification' in window)) {
            alert('Your browser does not support notifications');
            return;
        }

        setIsRequesting(true);

        try {
            // This triggers the browser's native notification permission dialog
            const result = await Notification.requestPermission();
            setPermission(result);

            if (result === 'granted') {
                // Show a test notification using browser's native notification
                new Notification('🔔 Notifications Enabled!', {
                    body: 'You will now receive river level alerts',
                    icon: '/favicon.ico',
                });
            } else if (result === 'denied') {
                alert('Notifications were blocked. You can enable them in your browser settings.');
            }
        } catch (error) {
            console.error('Error requesting notification permission:', error);
        } finally {
            setIsRequesting(false);
        }
    };

    if (!('Notification' in window)) {
        return <div className="rounded-lg bg-gray-100 p-3 text-sm text-gray-600">❌ This browser doesn't support notifications</div>;
    }

    if (permission === 'granted') {
        return (
            <div className="flex items-center gap-2 rounded-lg bg-green-100 p-3 text-green-800">
                <span>✅</span>
                <span>Browser notifications enabled</span>
            </div>
        );
    }

    if (permission === 'denied') {
        return (
            <div className="rounded-lg bg-red-100 p-3 text-red-800">
                <div className="mb-2 flex items-center gap-2">
                    <span>🚫</span>
                    <span>Notifications are blocked</span>
                </div>
                <p className="text-sm">To enable: Click the lock icon 🔒 in your address bar → Allow notifications → Refresh page</p>
            </div>
        );
    }

    return (
        <button
            onClick={enableNotifications}
            disabled={isRequesting}
            className="flex items-center gap-2 rounded-lg bg-blue-500 px-4 py-2 text-white hover:bg-blue-600 disabled:bg-blue-300"
        >
            <span>🔔</span>
            <span>{isRequesting ? 'Requesting...' : 'Enable Browser Notifications'}</span>
            {isRequesting && <div className="h-4 w-4 animate-spin rounded-full border-2 border-white border-t-transparent"></div>}
        </button>
    );
}
