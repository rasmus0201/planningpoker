import Echo from "laravel-echo";
import Pusher, { type Options } from "pusher-js";
import { computed } from "vue";
import { onBeforeRouteLeave } from "vue-router";

import type { JoinType } from "@/types";

let ECHO_INSTANCE: Echo | null = null;

export function useWs(joinType: JoinType | undefined = undefined) {
  const socketId = computed(() => ECHO_INSTANCE?.socketId());

  if (ECHO_INSTANCE !== null) {
    return { ws: ECHO_INSTANCE, socketId };
  }

  const options = {
    broadcaster: "reverb",
    wsHost: import.meta.env.VITE_REVERB_HOST,
    wsPort: parseInt(import.meta.env.VITE_REVERB_PORT ?? "80"),
    wssPort: parseInt(import.meta.env.VITE_REVERB_PORT ?? "443"),
    forceTLS: (import.meta.env.VITE_REVERB_SCHEME ?? "https") === "https",
    cluster: "",
    encrypted: true,
    enabledTransports: ["ws", "wss"],
    authEndpoint: import.meta.env.VITE_REVERB_AUTH_ENDPOINT,
    auth: {
      withCredentials: true,
      headers: {},
      params: {
        join_type: joinType
      }
    }
  } as Options;

  ECHO_INSTANCE = new Echo({
    ...options,
    key: import.meta.env.VITE_REVERB_APP_KEY,
    client: new Pusher(import.meta.env.VITE_REVERB_APP_KEY, options)
  });

  onBeforeRouteLeave(() => {
    ECHO_INSTANCE?.disconnect();
  });

  return { ws: ECHO_INSTANCE, socketId };
}
