import Echo from "laravel-echo";
import Pusher, { Options } from "pusher-js";
import { computed } from "vue";
import { onBeforeRouteLeave } from "vue-router";

import { JoinType } from "@/types";

let ECHO_INSTANCE: Echo | null = null;

export function useWs(joinType: JoinType | undefined = undefined) {
  const socketId = computed(() => ECHO_INSTANCE?.socketId());

  if (ECHO_INSTANCE !== null) {
    return { ws: ECHO_INSTANCE, socketId };
  }

  const options = {
    broadcaster: "pusher",
    cluster: import.meta.env.VITE_PUSHER_CLUSTER,
    encrypted: true,
    disableStats: false,
    enabledTransports: ["ws", "wss"],
    authEndpoint: import.meta.env.VITE_PUSHER_AUTH_ENDPOINT,
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
    client: new Pusher(import.meta.env.VITE_PUSHER_KEY, options)
  });

  onBeforeRouteLeave(() => {
    ECHO_INSTANCE?.disconnect();
  });

  return { ws: ECHO_INSTANCE, socketId };
}
