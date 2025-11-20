<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reverb Broadcast</title>
    @vite(['resources/js/app.js']) {{-- Load JS --}}
</head>
<body>
    <h1>Listening for Messages...</h1>
    <pre id="output"></pre>

    <script>
    document.addEventListener("DOMContentLoaded", function () {
      const OUT = document.getElementById("output");
    
      function dbg(...args) {
        console.log("[reverb-debug]", ...args);
        try {
          if (OUT) {
            OUT.textContent += args.map(a => (typeof a === "object" ? JSON.stringify(a, null, 2) : String(a))).join(" ") + "\n";
          }
        } catch (e) { /* ignore UI errors */ }
      }
    
      // quick sanity checks
      if (typeof window === "undefined") return;
      dbg("DOM loaded");
    
      if (!window.Echo) {
        dbg("window.Echo is undefined — Echo not initialized");
        return;
      }
      dbg("window.Echo exists");
      dbg("Echo.options:", window.Echo.options || "(no options)");
    
      // If using Pusher as connector, expose pusher internals for debugging
      const pusher = window.Echo && window.Echo.connector && window.Echo.connector.pusher;
      if (pusher) {
        dbg("Pusher instance found:", {
          state: pusher.connection && pusher.connection.state,
          socket_id: pusher.connection && pusher.connection.socket_id
        });
    
        // connection state changes
        if (pusher.connection && pusher.connection.bind) {
          pusher.connection.bind('state_change', (states) => {
            dbg("Pusher connection state change:", states);
          });
          pusher.connection.bind('connected', () => dbg("Pusher connected"));
          pusher.connection.bind('disconnected', () => dbg("Pusher disconnected"));
          pusher.connection.bind('error', (err) => dbg("Pusher connection error:", err));
        }
    
        // global event catcher (if available)
        if (typeof pusher.bind_global === "function") {
          pusher.bind_global((eventName, data) => {
            dbg("Pusher global event:", eventName, data);
          });
        }
      } else {
        dbg("No pusher instance found on Echo.connector");
      }
    
      // Subscribe using Echo and add debug hooks
      try {
        const channelName = "traffic-config";
        dbg("Subscribing to channel:", channelName);
    
        const channel = window.Echo.channel(channelName);
    
        // application-level event listener (same as you had)
        channel.listen(".traffic.config", (event) => {
          dbg("Received .traffic.config event:", event);
          if (OUT) OUT.textContent = JSON.stringify(event, null, 2);
        });
    
        // try to hook into pusher channel events (subscription succeeded/error)
        if (pusher) {
          // ensure underlying pusher channel exists (subscribe returns existing if present)
          const pChan = pusher.channel(channelName) || pusher.subscribe(channelName);
          if (pChan) {
            dbg("Underlying pusher channel object:", !!pChan);
            // subscription lifecycle events
            if (typeof pChan.bind === "function") {
              pChan.bind('pusher:subscription_succeeded', () => dbg("pusher:subscription_succeeded for", channelName));
              pChan.bind('pusher:subscription_error', (err) => dbg("pusher:subscription_error for", channelName, err));
            }
          } else {
            dbg("pusher.channel(...) returned nothing (channel not created)");
          }
        }
    
        dbg("Subscribe done, waiting for events...");
      } catch (err) {
        dbg("Error while subscribing/listening:", err && err.message ? err.message : err);
      }
    
    });
    </script>

</body>
</html>
