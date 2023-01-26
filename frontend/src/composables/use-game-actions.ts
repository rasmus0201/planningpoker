import { computed, Ref } from "vue";

import type { AuthenticatableSocket, Game } from "@/types";

export function useGameActions(game: Ref<Game | undefined>, socket: AuthenticatableSocket) {
  const canStartGame = computed(() => game.value?.state === "lobby");
  const canContinueGame = computed(() => game.value?.state === "reveal");
  const canForceReveal = computed(() => game.value?.state === "voting");
  const canFinishGame = computed(() => game.value?.state !== "finished");

  const hasAnyAction = computed(
    () => canStartGame.value || canContinueGame.value || canForceReveal.value || canFinishGame.value
  );

  const onStartGame = () => {
    socket.emit("game start");
  };

  const onForceReveal = () => {
    socket.emit("game forceReveal");
  };

  const onContinueGame = () => {
    socket.emit("game continue");
  };

  const onFinishGame = () => {
    socket.emit("game finish");
  };

  return {
    canStartGame,
    canContinueGame,
    canForceReveal,
    canFinishGame,
    hasAnyAction,

    onStartGame,
    onForceReveal,
    onContinueGame,
    onFinishGame
  };
}
