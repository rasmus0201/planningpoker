<script lang="ts" setup>
import { mapJoinType } from "@/helpers";
import { GameStateType, WsUser } from "@/types";

const props = defineProps<{
  users: WsUser[];
  gameState: GameStateType;
}>();

const hasVotingState = (user: WsUser) => user.joinType === "play" && props.gameState === "voting";
</script>

<template>
  <div class="is-flex is-flex-direction-column">
    <div v-for="user in users" :key="user.socketId" class="active-user">
      <span
        class="active-user__dot"
        :class="{
          'has-background-info': user.self,
          'has-background-success': user.connected,
          'has-background-grey': !user.connected
        }"
      ></span>
      <span
        ><span v-if="hasVotingState(user)">{{ user.hasVoted ? "🏆" : "💩" }}</span> {{ user.username }} ({{
          mapJoinType(user.joinType)
        }})</span
      >
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
