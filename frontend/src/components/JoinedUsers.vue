<script lang="ts" setup>
import { mapJoinType } from "@/helpers";
import { WsUser } from "@/types";

defineProps<{
  users: WsUser[];
}>();
</script>

<template>
  <div class="is-flex is-flex-direction-column">
    <div v-for="user in users" :key="user.userId" class="active-user">
      <span
        class="active-user__dot"
        :class="user.connected ? 'has-background-success' : 'has-background-warning'"
      ></span>
      <span>{{ user.username }} ({{ mapJoinType(user.joinType) }})</span>
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
    line-height: 1em;
    border-radius: 50%;
  }
}
</style>
