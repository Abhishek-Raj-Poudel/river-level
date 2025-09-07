import { useState, useEffect } from 'react';

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
                    icon: '/favicon.ico'
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
        return (
            <div className="p-3 bg-gray-100 text-gray-600 rounded-lg text-sm">
                ❌ This browser doesn't support notifications
            </div>
        );
    }

    if (permission === 'granted') {
        return (
            <div className="p-3 bg-green-100 text-green-800 rounded-lg flex items-center gap-2">
                <span>✅</span>
                <span>Browser notifications enabled</span>
            </div>
        );
    }

    if (permission === 'denied') {
        return (
            <div className="p-3 bg-red-100 text-red-800 rounded-lg">
                <div className="flex items-center gap-2 mb-2">
                    <span>🚫</span>
                    <span>Notifications are blocked</span>
                </div>
                <p className="text-sm">
                    To enable: Click the lock icon 🔒 in your address bar → Allow notifications → Refresh page
                </p>
            </div>
        );
    }

    return (
        <button
            onClick={enableNotifications}
            disabled={isRequesting}
            className="px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600 disabled:bg-blue-300 flex items-center gap-2"
        >
            <span>🔔</span>
            <span>{isRequesting ? 'Requesting...' : 'Enable Browser Notifications'}</span>
            {isRequesting && (
                <div className="animate-spin h-4 w-4 border-2 border-white border-t-transparent rounded-full"></div>
            )}
        </button>
    );
}
