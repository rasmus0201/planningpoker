import { io } from "socket.io-client";
import { onBeforeUnmount, ref } from "vue";
import { onBeforeRouteLeave, useRoute } from "vue-router";

import { WS_URL } from "@/config";
import { useUserStore } from "@/pinia/user";
import type { AuthenticatableSocket, WsSession } from "@/types";

export function useSocket() {
  const route = useRoute();
  const userStore = useUserStore();

  const wsSession = ref<WsSession>({
    sessionId: "",
    userId: "",
    gamePin: "",
    color: "",
    user: {
      userId: "",
      username: "",
      hasVoted: false,
      connected: false
    }
  });

  const socket: AuthenticatableSocket = io(WS_URL, {
    reconnection: false,
    autoConnect: false,
    rememberUpgrade: true
  });

  socket.on("connect_error", (err) => {
    console.log(err);
  });

  socket.on("disconnect", function (reason) {
    console.log(reason);
    setTimeout(() => connect(), 500);
  });

  socket.on("session", (session: WsSession) => {
    // Attach the session ID to the next reconnection attempts
    socket.auth = { sessionId: session.sessionId };

    // Store it in the sessionStorage
    sessionStorage.setItem("socket.io.sessionId", session.sessionId);

    // Save the ID of the user
    socket.userId = session.userId;

    wsSession.value = session;
  });

  socket.onAny((event, ...args) => {
    console.log(event, args);
  });

  const connect = () => {
    const sessionId = sessionStorage.getItem("socket.io.sessionId");
    if (sessionId) {
      // If the server was restarted, we need to send the token+username with..
      socket.auth = {
        gamePin: route.params.pin ?? "",
        joinType: route.meta.joinType ?? "play",
        sessionId,
        token: userStore.authHeader,
        username: userStore.user.username
      };
    } else {
      socket.auth = {
        gamePin: route.params.pin ?? "",
        joinType: route.meta.joinType ?? "play",
        token: userStore.authHeader,
        username: userStore.user.username
      };
    }

    socket.connect();

    socket.emit("ready");
  };

  onBeforeRouteLeave(() => {
    sessionStorage.removeItem("socket.io.sessionId");
    socket.disconnect();
  });

  onBeforeUnmount(() => {
    socket.off("connect_error");
    socket.off("disconnect");
    socket.off("session");
  });

  return {
    socket,
    wsSession,

    connect
  };
}
