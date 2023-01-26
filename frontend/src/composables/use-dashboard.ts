import { onMounted, ref } from "vue";

import { API_URL } from "@/config";
import { useUserStore } from "@/pinia/user";
import type { Game } from "@/types";

export function useDashboard() {
  const userStore = useUserStore();

  const activeGames = ref<Game[]>([]);
  const hostedGames = ref<Game[]>([]);

  onMounted(async () => {
    const response = await fetch(`${API_URL}/games`, {
      method: "GET",
      headers: new Headers({ "Content-Type": "application/json", Authorization: userStore.authHeader })
    });

    const json = await response.json();

    activeGames.value = json.games.filter((g: Game) => g.state !== "finished");
    hostedGames.value = json.games.filter((g: Game) => g.state === "finished" && g.user_id === userStore.user.id);
  });

  return {
    activeGames,
    hostedGames
  };
}
