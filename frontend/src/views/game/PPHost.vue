<script lang="ts" setup>
import { computed, onMounted, ref } from "vue";
import { RouterLink, useRoute, useRouter } from "vue-router";

import JoinedUsers from "@/components/JoinedUsers.vue";
import PokerCard from "@/components/PokerCard.vue";
import PokerCardBack from "@/components/PokerCardBack.vue";
import { useGameActions, useGameTokens, useGameUsers, useSocket } from "@/composables";
import { API_URL } from "@/config";
import { darkenColor, dynamicForeground } from "@/helpers";
import { useUserStore } from "@/pinia/user";
import type { Game, GameStateType, UserPokerCard, WsUser } from "@/types";

const userStore = useUserStore();
const router = useRouter();
const route = useRoute();

const game = ref<Game | undefined>();
const gameState = computed(() => game.value?.state ?? "initial");

const { socket, connect } = useSocket();
const { users } = useGameUsers(socket);
const {
  canStartGame,
  canContinueGame,
  canForceReveal,
  canFinishGame,
  hasAnyAction,
  onStartGame,
  onContinueGame,
  onForceReveal,
  onFinishGame
} = useGameActions(game, socket);
const { tokenContainer, userTokens, getTranslatedToken } = useGameTokens(socket);

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
    game.value = json.game as Game;

    if (game.value.user_id !== userStore.user.id) {
      return router.replace({ name: "home" });
    }

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
  revealedCards.value = [];
});
</script>

<template>
  <div v-if="game" class="columns host-container">
    <aside class="column is-2 is-narrow-mobile">
      <div class="p-4">
        <p class="menu-label">{{ game.title }}</p>
        <p class="menu-label">Pin: {{ game.pin }}</p>
        <p v-if="hasAnyAction" class="menu-label">Actions:</p>
        <div v-if="hasAnyAction" class="is-flex is-flex-direction-column">
          <button v-if="canStartGame" class="button is-success mb-3" @click="onStartGame()">Start game</button>
          <button v-if="canForceReveal" class="button is-warning mb-3" @click="onForceReveal()">Force reveal</button>
          <button v-if="canContinueGame" class="button is-success mb-3" @click="onContinueGame()">Continue</button>
          <button v-if="canFinishGame" class="button is-danger mb-3" @click="onFinishGame()">Finish</button>
        </div>
        <p class="menu-label">Joined Users:</p>
        <JoinedUsers :users="users" />
        <button class="button is-small is-warning mt-5" @click="onDeleteSession()">Delete session</button>
      </div>
    </aside>

    <section v-if="gameState == 'lobby'" class="column is-relative is-12-mobile is-10">
      <div class="py-4">
        <h1 class="title is-1 has-text-centered">{{ game.pin }}</h1>
      </div>
      <div ref="tokenContainer" class="token-container">
        <div
          v-for="({ position, user }, i) in userTokens"
          :key="i"
          class="user-token"
          :style="{
            backgroundColor: user.color,
            borderColor: darkenColor(user.color ?? '#000000'),
            color: dynamicForeground(user.color ?? '#000000'),
            transform: getTranslatedToken(position)
          }"
        >
          <span class="user-token__inner">{{ user.username.substring(0, 3) }}</span>
        </div>
      </div>
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
.host-container {
  min-height: 100vh;
}
.token-container {
  width: 100%;
  height: 100%;
  position: relative;
  user-select: none;
  touch-action: none;
  overflow: hidden;
}

.user-token {
  position: absolute;
  top: 0;
  left: 0;
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: 600;
  font-size: 1rem;
  line-height: 1em;
  width: 64px;
  height: 64px;
  border-radius: 50%;
  user-select: none;
  z-index: 1;
  touch-action: none;
  user-select: none;
  background-color: #000;
  border: 6px solid #fff;
  transition: transform 35ms linear;
  box-shadow: 1px 1px 2px 2px rgba(255, 255, 255, 0.5);
}

.poker-cards-container {
  display: flex;
  flex-wrap: wrap;
  gap: 10px;
  padding: 10px;
  margin-bottom: 125px;
}
</style>
