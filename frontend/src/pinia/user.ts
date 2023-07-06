import { cloneDeep } from "lodash-es";
import { defineStore } from "pinia";

import { API_URL } from "@/config";

interface User {
  id: number;
  username: string;
  email: string;
  createdAt: string;
}

const defaultState = {
  user: {
    id: 0,
    username: "",
    email: "",
    createdAt: ""
  } as User
};

export const useUserStore = defineStore("user", {
  state: () => cloneDeep(defaultState),
  getters: {
    isLoggedIn(store) {
      return Boolean(store?.user?.id);
    }
  },
  actions: {
    login(user: User) {
      this.user = user;
    },
    async logout() {
      await fetch(`${API_URL}/auth/logout`, {
        method: "POST",
        headers: new Headers({ "Content-Type": "application/json" }),
        credentials: "include"
      });

      this.$patch(cloneDeep(defaultState));
    }
  },
  persist: true
});
