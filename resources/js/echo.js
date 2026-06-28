import Echo from "laravel-echo";
import Pusher from "pusher-js";

window.Pusher = Pusher;

window.Echo = new Echo({
    broadcaster: "reverb",
    key: import.meta.env.VITE_REVERB_APP_KEY,
    wsHost: import.meta.env.VITE_REVERB_HOST,
    wsPort: import.meta.env.VITE_REVERB_PORT ?? 80,
    wssPort: import.meta.env.VITE_REVERB_PORT ?? 443,
    forceTLS: (import.meta.env.VITE_REVERB_SCHEME ?? "https") === "https",
    enabledTransports: ["ws", "wss"],
});

// Debug koneksi WebSocket di console browser
window.Echo.connector.pusher.connection.bind("connected", () => {
    console.log("[WebSocket] Terhubung ke Reverb ✅");
});

window.Echo.connector.pusher.connection.bind("error", (err) => {
    console.error("[WebSocket] Koneksi error ❌", err);
});

window.Echo.connector.pusher.connection.bind("disconnected", () => {
    console.warn("[WebSocket] Terputus dari Reverb ⚠️");
});
