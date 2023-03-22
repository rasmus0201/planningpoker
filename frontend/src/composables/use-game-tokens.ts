import { onBeforeUnmount, ref } from "vue";

import type { AuthenticatableSocket, TokenMovedEvent, WsUser } from "@/types";

interface UserToken {
  position: TokenMovedEvent;
  user: WsUser;
}

export function useGameTokens(socket: AuthenticatableSocket) {
  const userTokens = ref<UserToken[]>([]);
  const tokenContainer = ref<HTMLElement>();

  socket.on("token moved", (e: UserToken) => {
    const index = userTokens.value.findIndex((u) => u.user.userId === e.user.userId);
    if (index === -1) {
      userTokens.value.push(e);
      return;
    }

    userTokens.value = userTokens.value.map((u) => {
      if (u.user.userId !== e.user.userId) {
        return u;
      }

      return e;
    });
  });

  socket.on("user disconnected", (userId: string) => {
    userTokens.value = userTokens.value.filter((u) => u.user.userId !== userId);
  });

  socket.on("session", () => {
    userTokens.value = [];
  });

  socket.on("game voting", () => {
    socket.off("token moved");
  });

  const mapRange = (value: number, from: number, to: number) => to * (value / Math.max(1, from));

  const getTranslatedToken = (position: TokenMovedEvent) => {
    if (!tokenContainer.value) {
      return "";
    }

    const mappedX = mapRange(position.x, position.containerWidth, tokenContainer.value.clientWidth);
    const mappedY = mapRange(position.y, position.containerHeight, tokenContainer.value.clientHeight);

    return `translate(${mappedX}px, ${mappedY}px)`;
  };

  onBeforeUnmount(() => {
    socket.off("token moved");
    socket.off("user disconnected");
    socket.off("session");
    socket.off("game voting");
  });

  return {
    tokenContainer,
    userTokens,

    getTranslatedToken
  };
}
