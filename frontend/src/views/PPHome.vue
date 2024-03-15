<script lang="ts" setup>
import { RouterLink } from "vue-router";

import { useDashboard } from "@/composables";
import { useUserStore } from "@/pinia/user";

const userStore = useUserStore();

const { activeGames, hostedGames, finishGame } = useDashboard();
</script>

<template>
  <div v-if="!activeGames.length && !hostedGames.length" class="columns mt-5">
    <div class="column is-12">
      <h1 class="title">No games</h1>
    </div>
  </div>

  <div v-if="activeGames.length" class="columns mt-5 is-multiline">
    <div class="column is-12">
      <h1 class="title">Active Games:</h1>
    </div>
    <div v-for="(game, i) in activeGames" :key="i" class="column is-3 is-2-fullhd">
      <div class="box">
        <p class="subtitle mb-2 one-line-text">{{ game.title || `Game #${game.id}` }}</p>
        <p class="mb-2">Pin: {{ game.pin }}</p>
        <div class="buttons-container">
          <RouterLink
            v-if="game.userId === userStore.user.id"
            :to="{ name: 'game.host', params: { pin: game.pin } }"
            class="button is-info"
          >
            <span>Host</span>
          </RouterLink>
          <RouterLink :to="{ name: 'game.play', params: { pin: game.pin } }" class="button is-info">
            <span>Play</span>
          </RouterLink>
          <RouterLink :to="{ name: 'game.spectate', params: { pin: game.pin } }" class="button is-info">
            <span>Spectate</span>
          </RouterLink>
        </div>
        <div>
          <button class="button is-warning" @click="finishGame(game)">End game</button>
        </div>
      </div>
    </div>
  </div>

  <div v-if="hostedGames.length" class="columns mt-5 is-multiline">
    <div class="column is-12">
      <h1 class="title">Hosted Games:</h1>
    </div>
    <div v-for="(game, i) in hostedGames" :key="i" class="column is-3 is-2-fullhd">
      <div class="box">
        <p class="subtitle one-line-text">{{ game.title || `Game #${game.id}` }}</p>
        <div v-if="false">
          <button class="button is-info">See rounds</button>
        </div>
      </div>
    </div>
  </div>
</template>

<style lang="scss" scoped>
.one-line-text {
  white-space: nowrap;
  text-overflow: ellipsis;
  overflow: hidden;
}

.buttons-container {
  display: flex;
  gap: 8px;
  flex-wrap: wrap;
  margin-bottom: 8px;
}
</style>
