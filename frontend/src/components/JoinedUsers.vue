<script lang="ts" setup>
import { mapJoinType } from "@/helpers";
import { GameStateType, WsUser } from "@/types";

const props = defineProps<{
  users: WsUser[];
  canKick: boolean;
  gameState: GameStateType;
}>();

const emit = defineEmits<{
  (e: "kick", user: WsUser): void;
  (e: "unkick", user: WsUser): void;
}>();

const hasVotingState = (user: WsUser) => user.joinType === "play" && props.gameState === "voting";
</script>

<template>
  <div class="is-flex is-flex-direction-column">
    <div v-for="(user, index) in users" :key="index" class="active-user">
      <span
        class="active-user__dot"
        :class="{
          'has-background-info': user.self,
          'has-background-success': user.connected,
          'has-background-grey': !user.connected
        }"
      ></span>
      <span>
        <span v-if="hasVotingState(user)">{{ user.hasVoted ? "🏆" : "💩" }}</span> {{ user.username }} ({{
          mapJoinType(user.joinType)
        }})
      </span>

      <span v-if="user.joinType === 'play' && canKick">
        <button v-if="user.kickedAt === null" class="button is-small is-light" @click="emit('kick', user)">Kick</button>
        <button v-else class="button is-small is-light" @click="emit('unkick', user)">Unkick</button>
      </span>
    </div>
  </div>
</template>

<style lang="scss" scoped>
.active-user {
  display: flex;
  align-items: center;
  gap: 10px;
  margin-bottom: 10px;

  &:last-of-type {
    margin-bottom: 0;
  }

  &__dot {
    display: inline-block;
    margin-top: 3px;
    width: 0.5em;
    height: 0.5em;
    min-width: 0.5em;
    min-height: 0.5em;
    line-height: 1em;
    border-radius: 50%;
  }
}
</style>
