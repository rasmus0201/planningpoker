<script lang="ts" setup>
import { computed, onMounted, ref } from "vue";
import { RouterLink, useRoute, useRouter } from "vue-router";

import DraggableTokenCanvas from "@/components/DraggableTokenCanvas.vue";
import JoinedUsers from "@/components/JoinedUsers.vue";
import PokerCard from "@/components/PokerCard.vue";
import PokerCardBack from "@/components/PokerCardBack.vue";
import { useGameUsers, useSocket } from "@/composables";
import { API_URL } from "@/config";
import { useUserStore } from "@/pinia/user";
import { type Game, type GameStateType, type TokenMovedEvent, type UserPokerCard, type WsUser } from "@/types";

const userStore = useUserStore();
const router = useRouter();
const route = useRoute();

const { socket, wsSession, connect } = useSocket();
const { users } = useGameUsers(socket);
const game = ref<Game | undefined>();
const gameState = computed(() => game.value?.state ?? "initial");

const onDeleteSession = () => {
  socket.disconnect();
  const resolvedRoute = router.resolve({ ...route, query: { fresh: "true" } });

  window.location.href = resolvedRoute.href;
};

onMounted(async () => {
  if (route.query.fresh) {
    sessionStorage.removeItem("socket.io.sessionId");
    await router.replace({ query: {} });
  }

  try {
    const response = await fetch(`${API_URL}/games/${route.params.pin}`, {
      method: "GET",
      headers: new Headers({ "Content-Type": "application/json", Authorization: userStore.authHeader })
    });

    const json = await response.json();
    game.value = json.game;

    connect();
  } catch (error) {
    window.alert("Something went wrong...");
  }
});

socket.on("game lobby", () => setGameState("lobby"));
socket.on("game voting", () => setGameState("voting"));
socket.on("game reveal", () => setGameState("reveal"));
socket.on("game finished", () => setGameState("finished"));

const setGameState = (state: GameStateType) => {
  // eslint-disable-next-line @typescript-eslint/no-non-null-assertion
  game.value!.state = state;
};

const onMove = (e: TokenMovedEvent) => {
  socket.emit("token moved", e);
};

const votingUsers = ref<WsUser[]>([]);
socket.on("game vote", (user: WsUser) => {
  votingUsers.value.push(user);
});

const revealedCards = ref<UserPokerCard[]>([]);
socket.on("game reveal", (votes: UserPokerCard[]) => {
  votingUsers.value = [];
  revealedCards.value = votes;
});

socket.on("game voting", () => {
  votingUsers.value = [];
  revealedCards.value = [];
});
</script>

<template>
  <div v-if="game && wsSession.userId" class="columns play-container">
    <aside class="column is-12-mobile is-2 is-narrow-mobile">
      <div class="p-4">
        <p class="menu-label">{{ game.title }}</p>
        <p class="menu-label">Joined Users:</p>
        <JoinedUsers :users="users" />
        <button class="button is-small is-warning mt-5" @click="onDeleteSession()">Delete session</button>
      </div>
    </aside>

    <section v-if="gameState === 'lobby'" class="column is-relative is-12-mobile is-10">
      <DraggableTokenCanvas :key="wsSession.color" :color="wsSession.color ?? '#000000'" @move="onMove($event)" />
    </section>

    <section v-if="gameState === 'voting'" class="column is-relative is-12-mobile is-10">
      <div class="poker-cards-container">
        <p v-if="votingUsers?.length === 0" class="title">Please vote 🙃</p>
        <PokerCardBack v-for="(card, i) in votingUsers" :key="i" :username="card.username.substring(0, 3)" />
      </div>
    </section>

    <section v-if="gameState === 'reveal'" class="column is-relative is-12-mobile is-10">
      <div class="poker-cards-container">
        <p v-if="revealedCards?.length === 0" class="title">Nobody voted 🙃</p>
        <PokerCard
          v-for="(card, i) in revealedCards"
          :key="i"
          :card="card.vote"
          :username="card.username.substring(0, 3)"
        />
      </div>
    </section>

    <section v-if="gameState === 'finished'" class="column is-relative is-12-mobile is-10">
      <div class="p-4">
        <p class="title">Game finished 🙃</p>
        <RouterLink :to="{ name: 'home' }">Home</RouterLink>
      </div>
    </section>
  </div>
</template>

<style lang="scss" scoped>
.play-container {
  min-height: 100vh;
}

.poker-cards-container {
  display: flex;
  flex-wrap: wrap;
  gap: 10px;
  padding: 10px;
  margin-bottom: 125px;
}

.vote-button {
  height: 5rem;
  position: fixed;
  bottom: 10px;
  left: 10px;
  right: 10px;
  font-size: 2.5rem;
  border-radius: 2.5rem;

  &:disabled {
    pointer-events: none;
  }
}
</style>
