import { defineStore } from "pinia";

import { API_URL } from "@/config";

export const useUserStore = defineStore("user", {
  state: () => ({
    user: {
      id: 0,
      username: "",
      email: ""
    },
    token: ""
  }),
  getters: {
    authHeader(store) {
      return `Bearer ${store.token}`;
    }
  },
  actions: {
    login({ user, token }) {
      this.user = user;
      this.token = token;
    },
    async logout() {
      await fetch(`${API_URL}/auth/logout`, {
        method: "POST",
        headers: new Headers({ "Content-Type": "application/json", Authorization: this.authHeader })
      });

      this.user = {
        id: 0,
        username: "",
        email: ""
      };
      this.token = "";
    }
  },
  persist: true
});
