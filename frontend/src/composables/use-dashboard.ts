import { onMounted, ref } from "vue";

import { useUserStore } from "@/pinia/user";
import type { Game } from "@/types";

import { useApi } from "./use-api";

export function useDashboard() {
  const userStore = useUserStore();
  const api = useApi();

  const activeGames = ref<Game[]>([]);
  const hostedGames = ref<Game[]>([]);

  onMounted(async () => {
    const response = await api.get("/games");

    if (response.status === 200) {
      activeGames.value = response.data.filter((g: Game) => g.state !== "finished");
      hostedGames.value = response.data.filter((g: Game) => g.state === "finished" && g.userId === userStore.user.id);
    }
  });

  const finishGame = async (game: Game) => {
    await api.patch(`/games/${game.pin}`, { state: "finished" });

    activeGames.value = activeGames.value.filter((g) => g.id !== game.id);
    hostedGames.value = [game, ...hostedGames.value];
  };

  return {
    activeGames,
    hostedGames,
    finishGame
  };
}
