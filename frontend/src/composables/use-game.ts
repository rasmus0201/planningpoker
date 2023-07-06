import { computed, ref } from "vue";
import { useRoute, useRouter } from "vue-router";

import { useUserStore } from "@/pinia/user";
import type { Game, JoinType, Participant, UserPokerCard, WsUser } from "@/types";

import { useApi } from "./use-api";
import { useGameUsers } from "./use-game-users";
import { useWs } from "./use-ws";

export async function useGame(joinType: JoinType) {
  const userStore = useUserStore();
  const router = useRouter();
  const route = useRoute();
  const api = useApi();

  const { ws } = useWs(joinType);

  const currentUser: WsUser = {
    socketId: null,
    broadcastingId: `${userStore.user.id}.${joinType}`,
    userId: userStore.user.id,
    kickedAt: null,
    joinType,
    participantId: null,
    username: userStore.user.username,
    hasVoted: false
  };

  const fact = ref<string>("");

  const game = ref<Game | undefined>();
  const gameState = computed(() => game.value?.state ?? "initial");
  const gameJoinUrl = computed(
    () =>
      new URL(
        router.resolve({
          name: "game.join",
          query: { pin: game.value?.pin }
        }).href,
        window.location.origin
      ).href
  );

  const gameParticipants = ref<Participant[]>([]);
  const votingUsers = ref<Participant[]>([]);
  const revealedCards = ref<UserPokerCard[]>([]);

  const createParticipant = async () => {
    try {
      await api.post(`/games/${route.params.pin}/participants`);
    } catch (error) {
      window.alert("Something went wrong...");
    }
  };

  const getGame = async () => {
    try {
      const response = await api.get(`/games/${route.params.pin}`);

      game.value = response.data.data.game as Game;
      fact.value = response.data.data.fact as string;
      gameParticipants.value = response.data.data.participants as Participant[];
      votingUsers.value = response.data.data.votingUsers as Participant[];
      revealedCards.value = response.data.data.votes as UserPokerCard[];

      if (joinType === "host" && game.value.userId !== userStore.user.id) {
        return router.replace({ name: "home" });
      }
    } catch (error) {
      window.alert("Something went wrong...");
    }
  };

  if (joinType === "play") {
    await createParticipant();
  }

  await getGame();

  const channel = ws.join(`games.${game.value?.pin}`);
  const { users } = useGameUsers(channel, currentUser, gameParticipants, votingUsers);

  channel.listen(".game.state", ({ state }) => {
    if (game.value) {
      game.value.state = state;
    }
  });

  channel.listen(".game.vote", ({ participant }: { participant: Participant }) => {
    votingUsers.value.push(participant);
  });

  channel.listen(".game.reveal", ({ votes }: { votes: UserPokerCard[] }) => {
    votingUsers.value = [];
    revealedCards.value = votes;
  });

  channel.listen(".game.new-round", () => {
    votingUsers.value = [];
    revealedCards.value = [];
  });

  return {
    fact,
    game,
    gameState,
    gameJoinUrl,
    users,

    votingUsers,
    revealedCards,

    ws,
    channel
  };
}
