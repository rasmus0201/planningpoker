<script lang="ts" setup>
import { computed, onMounted, ref } from "vue";
import { RouterLink } from "vue-router";

import JoinedUsers from "@/components/JoinedUsers.vue";
import PokerCard from "@/components/PokerCard.vue";
import PokerCardWritable from "@/components/PokerCardWritable.vue";
import { useApi, useGame } from "@/composables";
import { PokerCards } from "@/types";

const { fact, game, gameState, revealedCards, users, ws, channel } = await useGame("play");
const api = useApi(ws);

const hasVoted = ref(false);
const activeCard = ref("");
const canVote = computed(() => !hasVoted.value && activeCard.value);

onMounted(async () => {
  const response = await api.get(`/games/${game.value?.pin}/participants`);
  const body = response.data.data;

  if (body.currentVote) {
    hasVoted.value = true;
    activeCard.value = body.currentVote.vote;
  }
});

const selectCard = (card: string) => {
  activeCard.value = card;
};

const vote = async () => {
  await api.post(`/games/${game.value?.pin}/votes`, { vote: activeCard.value });
  hasVoted.value = true;
};

channel.listen(".game.reveal", () => {
  hasVoted.value = false;
  activeCard.value = "";
});

channel.listen(".game.new-round", () => {
  hasVoted.value = false;
  activeCard.value = "";
});
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

    <section v-if="gameState === 'voting'" class="column is-12-mobile is-10">
      <div class="poker-cards-container">
        <PokerCard
          v-for="(card, i) in PokerCards"
          :key="i"
          :card="card"
          :disabled="hasVoted"
          :active="activeCard"
          @click="selectCard(card)"
        />
        <PokerCardWritable :disabled="hasVoted" :active="activeCard" @select="selectCard($event)" />
      </div>
      <button class="button is-info vote-button" :disabled="!canVote" @click="vote()">Vote!</button>
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
