import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

// @ts-ignore
window.Pusher = Pusher;

const echo = new Echo({
  broadcaster: 'reverb',
  key: import.meta.env.VITE_WS_KEY || 'local',
  wsHost: import.meta.env.VITE_WS_HOST || window.location.hostname,
  wsPort: import.meta.env.VITE_WS_PORT || 8080,
  wssPort: import.meta.env.VITE_WS_PORT || 8080,
  forceTLS: false,
  enabledTransports: ['ws', 'wss'],
  // Custom authorizer to use the latest token from localStorage
  authorizer: (channel: any) => {
    return {
      authorize: (socketId: string, callback: Function) => {
        const token = localStorage.getItem('token');
        fetch(`${import.meta.env.VITE_API_BASE_URL}/broadcasting/auth`, {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'Authorization': `Bearer ${token}`
          },
          body: JSON.stringify({
            socket_id: socketId,
            channel_name: channel.name
          })
        })
        .then(response => response.json())
        .then(data => {
          callback(false, data);
        })
        .catch(error => {
          callback(true, error);
        });
      }
    };
  },
});

export default echo;
