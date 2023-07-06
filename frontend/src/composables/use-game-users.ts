import { MaybeRef } from "@vueuse/core";
import { PresenceChannel } from "laravel-echo";
import { computed, ref, unref } from "vue";

import { GameStateType, Participant, WsUser } from "@/types";

export function useGameUsers(channel: PresenceChannel, currentUser: WsUser, votingUsers: MaybeRef<Participant[]>) {
  const users = ref<WsUser[]>([]);

  channel
    .here((newUsers: WsUser[]) => {
      newUsers.forEach((user: WsUser) => {
        user.connected = true;
        user.hasVoted = user.participantId
          ? unref(votingUsers)
              .map((p) => p.id)
              .includes(user.participantId)
          : false;
        user.self = user.broadcastingId === currentUser.broadcastingId;
      });

      users.value = newUsers;
    })
    .joining((user: WsUser) => {
      for (const existingUser of users.value) {
        if (existingUser.broadcastingId === user.broadcastingId) {
          existingUser.connected = true;
          return;
        }
      }

      users.value.push(user);
    })
    .leaving((user: WsUser) => {
      for (const existingUser of users.value) {
        if (existingUser.broadcastingId === user.broadcastingId) {
          existingUser.connected = false;
          return;
        }
      }
    })
    .error((error: string) => {
      console.error(error);
    });

  channel.listen(".game.vote", ({ participant }: { participant: Participant }) => {
    for (const user of users.value) {
      if (user.participantId === participant.id) {
        user.hasVoted = true;
      }
    }
  });

  channel.listen(".game.state", ({ state }: { state: GameStateType }) => {
    if (state === "revealing" || state === "voting") {
      for (const user of users.value) {
        user.hasVoted = false;
      }
    }
  });

  const sortedUsers = computed(() => [...users.value].sort((a, b) => String(a.userId).localeCompare(String(b.userId))));

  return {
    users: sortedUsers
  };
}
