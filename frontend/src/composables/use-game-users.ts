import type { PresenceChannel } from "laravel-echo";
import { orderBy } from "lodash-es";
import { computed, type MaybeRef, ref, unref } from "vue";

import type { GameStateType, Participant, WsUser } from "@/types";

export function useGameUsers(
  channel: PresenceChannel,
  currentUser: WsUser,
  participants: MaybeRef<Participant[]>,
  votingUsers: MaybeRef<Participant[]>
) {
  const users = ref<WsUser[]>(
    unref(participants).map((p) => ({
      socketId: null,
      broadcastingId: `${p.userId}.play`,
      userId: p.userId,
      participantId: p.id,
      username: p.username,
      kickedAt: p.kickedAt,
      joinType: "play", // Participants can only be players.
      hasVoted: false,
      connected: false
    }))
  );

  channel
    .here((newUsers: WsUser[]) => {
      for (const newUser of newUsers) {
        const newUserWithFields = {
          ...newUser,

          connected: true,
          hasVoted: unref(votingUsers)
            .map((p) => p.id)
            .includes(newUser.participantId ?? -1),
          self: newUser.broadcastingId === currentUser.broadcastingId
        };

        const existingIndex = users.value.findIndex((u) => u.broadcastingId === newUser.broadcastingId);
        if (existingIndex >= 0) {
          users.value[existingIndex] = newUserWithFields;
        } else {
          users.value.push(newUserWithFields);
        }
      }
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

  const sortedUsers = computed(() => orderBy([...users.value], ["connected", "id"], ["desc", "desc"]));

  return {
    users: sortedUsers
  };
}
