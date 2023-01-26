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
    userTokens.value = userTokens.value.filter((u): u is UserToken => u.user.userId !== e.user.userId);
    userTokens.value.push(e);
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
    socket.off("session");
    socket.off("game voting");
  });

  return {
    tokenContainer,
    userTokens,

    getTranslatedToken
  };
}
