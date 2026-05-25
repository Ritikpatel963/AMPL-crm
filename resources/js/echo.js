import Echo from 'laravel-echo';

import Pusher from 'pusher-js';
window.Pusher = Pusher;

const configuredHost = import.meta.env.VITE_REVERB_HOST;
const usesPublicHost = configuredHost && !['127.0.0.1', 'localhost'].includes(configuredHost);
const forceTLS = usesPublicHost
    ? (import.meta.env.VITE_REVERB_SCHEME ?? 'https') === 'https'
    : window.location.protocol === 'https:';
const port = usesPublicHost
    ? Number(import.meta.env.VITE_REVERB_PORT ?? (forceTLS ? 443 : 80))
    : (forceTLS ? 443 : Number(import.meta.env.VITE_REVERB_PORT ?? 80));

window.Echo = new Echo({
    broadcaster: 'reverb',
    key: import.meta.env.VITE_REVERB_APP_KEY,
    wsHost: usesPublicHost ? configuredHost : window.location.hostname,
    wsPort: port,
    wssPort: port,
    forceTLS,
    authEndpoint: '/api/broadcasting/auth',
    enabledTransports: ['ws', 'wss'],
})

