import { computed, onBeforeUnmount, onMounted, ref } from "vue";

import { AuthenticatableSocket, WsUser } from "@/types";

export function useGameUsers(socket: AuthenticatableSocket) {
  const users = ref<WsUser[]>([]);
  const timer = ref<NodeJS.Timeout | null>(null);

  const refreshUsers = () => {
    socket.emit("users list");
    timer.value = setTimeout(refreshUsers, 5000);
  };

  onMounted(() => {
    timer.value = setTimeout(refreshUsers, 5000);
  });

  onBeforeUnmount(() => timer.value && clearTimeout(timer.value));

  socket.on("users", (newUsers: WsUser[]) => {
    newUsers.forEach((user: WsUser) => {
      user.self = user.userId === socket.userId;
    });

    users.value = newUsers;
  });

  socket.on("user connected", (user: WsUser) => {
    for (const existingUser of users.value) {
      if (existingUser.userId === user.userId) {
        existingUser.connected = true;
        return;
      }
    }

    users.value.push(user);
  });

  socket.on("user disconnected", (userId: string) => {
    for (const user of users.value) {
      if (user.userId === userId) {
        user.connected = false;
        return;
      }
    }
  });

  socket.on("connect", () => {
    for (const user of users.value) {
      if (user.self) {
        user.connected = true;
        return;
      }
    }
  });

  socket.on("disconnect", () => {
    for (const user of users.value) {
      if (user.self) {
        user.connected = false;
        return;
      }
    }
  });

  socket.on("game vote", ({ userId }: { userId: string }) => {
    for (const user of users.value) {
      if (user.userId === userId) {
        user.hasVoted = true;
      }
    }
  });

  socket.on("game reveal", () => {
    for (const user of users.value) {
      user.hasVoted = false;
    }
  });

  onBeforeUnmount(() => {
    socket.off("connect");
    socket.off("disconnect");
    socket.off("users");
    socket.off("user connected");
    socket.off("user disconnected");
    socket.off("game vote");
    socket.off("game reveal");
  });

  const sortedUsers = computed(() => users.value.sort((a, b) => a.userId.localeCompare(b.userId)));

  return {
    users: sortedUsers
  };
}
