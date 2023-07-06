<script lang="ts" setup>
import { RouterLink } from "vue-router";

import JoinedUsers from "@/components/JoinedUsers.vue";
import PokerCard from "@/components/PokerCard.vue";
import PokerCardBack from "@/components/PokerCardBack.vue";
import { useGame } from "@/composables";

const { fact, game, gameState, revealedCards, votingUsers, users } = await useGame("spectate");
</script>

<template>
  <div v-if="game" class="columns play-container">
    <aside class="column is-12-mobile is-2 is-narrow-mobile">
      <div class="p-4">
        <p class="menu-label">{{ game.title }}</p>
        <p class="menu-label">Joined Users:</p>
        <JoinedUsers :users="users" :game-state="game.state" :can-kick="false" />
      </div>
    </aside>

    <section v-if="gameState === 'lobby'" class="column is-12-mobile is-10">
      <h1 class="title p-4">Random fact: {{ fact }}</h1>
    </section>

    <section v-if="gameState === 'voting'" class="column is-relative is-12-mobile is-10">
      <div class="poker-cards-container">
        <p v-if="votingUsers?.length === 0" class="title">Please vote 🙃</p>
        <PokerCardBack v-for="(card, i) in votingUsers" :key="i" :username="card.username" />
      </div>
    </section>

    <section v-if="gameState === 'revealing'" class="column is-relative is-12-mobile is-10">
      <div class="poker-cards-container">
        <p v-if="revealedCards?.length === 0" class="title">Nobody voted 🙃</p>
        <PokerCard v-for="(card, i) in revealedCards" :key="i" :card="card.vote" :username="card.username" />
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
</style>
