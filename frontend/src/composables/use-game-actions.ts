import { computed, type Ref } from "vue";

import type { Game } from "@/types";

import { useApi } from "./use-api";

export function useGameActions(game: Ref<Game | undefined>) {
  const api = useApi();
  const canStartGame = computed(() => game.value?.state === "lobby");
  const canContinueGame = computed(() => game.value?.state === "voting" || game.value?.state === "revealing");
  const canForceReveal = computed(() => game.value?.state === "voting");
  const canFinishGame = computed(() => game.value?.state !== "finished");

  const hasAnyAction = computed(
    () => canStartGame.value || canContinueGame.value || canForceReveal.value || canFinishGame.value
  );

  const onStartGame = () => {
    api.patch(`/games/${game.value?.pin}`, { state: "voting" });
  };

  const onFinishGame = () => {
    api.patch(`/games/${game.value?.pin}`, { state: "finished" });
  };

  const onForceReveal = () => {
    api.patch(`/games/${game.value?.pin}`, { state: "revealing" });
  };

  const onContinueGame = () => {
    api.patch(`/games/${game.value?.pin}`, { state: "voting" });
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
