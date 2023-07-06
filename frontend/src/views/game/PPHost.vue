<script lang="ts" setup>
import QrCode from "@chenfengyuan/vue-qrcode";
import { ref } from "vue";
import { RouterLink } from "vue-router";

import JoinedUsers from "@/components/JoinedUsers.vue";
import PokerCard from "@/components/PokerCard.vue";
import PokerCardBack from "@/components/PokerCardBack.vue";
import { useGame, useGameActions } from "@/composables";

const { game, gameState, gameJoinUrl, users, votingUsers, revealedCards } = await useGame("host");

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
} = useGameActions(game);

const qrContainer = ref<HTMLElement | undefined>();
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
          <button v-if="canContinueGame" class="button is-success mb-3" @click="onContinueGame()">Next round</button>
          <button v-if="canFinishGame" class="button is-danger mb-3" @click="onFinishGame()">Finish</button>
        </div>
        <p class="menu-label">Joined Users:</p>
        <JoinedUsers :users="users" :game-state="game.state" />

        <div ref="qrContainer" class="mt-5">
          <QrCode
            v-if="qrContainer"
            :value="gameJoinUrl"
            :options="{ width: qrContainer.clientWidth, margin: 0 }"
            style="max-width: 100%; height: auto; aspect-ratio: 1/1"
          ></QrCode>
        </div>
      </div>
    </aside>

    <section v-if="gameState == 'lobby'" class="column is-relative is-12-mobile is-10">
      <div class="py-4">
        <!-- Split pin in middle for easier reading -->
        <h1 class="title is-1 has-text-centered">{{ `${game.pin.slice(0, 3)} ${game.pin.slice(3, 6)}` }}</h1>
      </div>
    </section>

    <section v-if="gameState === 'voting'" class="column is-relative is-12-mobile is-10">
      <div class="poker-cards-container">
        <p v-if="votingUsers?.length === 0" class="title">Please vote 🙃</p>
        <TransitionGroup name="list" tag="div" class="poker-cards-container__inner">
          <PokerCardBack v-for="(card, i) in votingUsers" :key="i" :username="card.username" />
        </TransitionGroup>
      </div>
    </section>

    <section v-if="gameState === 'revealing'" class="column is-relative is-12-mobile is-10">
      <div class="poker-cards-container">
        <p v-if="revealedCards?.length === 0" class="title">Nobody voted 🙃</p>
        <TransitionGroup name="list" tag="div" class="poker-cards-container__inner">
          <PokerCard v-for="(card, i) in revealedCards" :key="i" :card="card.vote" :username="card.username" />
        </TransitionGroup>
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
.list-move, /* apply transition to moving elements */
.list-enter-active,
.list-leave-active {
  transition: all 0.5s ease;
}

.list-enter-from,
.list-leave-to {
  opacity: 0;
  transform: translateX(30px);
}

/* ensure leaving items are taken out of layout flow so that moving
   animations can be calculated correctly. */
.list-leave-active {
  position: absolute;
}

.host-container {
  min-height: 100vh;
}

.poker-cards-container {
  padding: 10px;
  margin-bottom: 125px;

  &__inner {
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
  }
}
</style>
