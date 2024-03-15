import { cloneDeep } from "lodash-es";
import { defineStore } from "pinia";
import { computed, reactive, toRefs } from "vue";

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

export const useUserStore = defineStore(
  "user",
  () => {
    const state = reactive<typeof defaultState>(cloneDeep(defaultState));

    const isLoggedIn = computed(() => Boolean(state.user.id));

    const login = (user: User) => {
      state.user = user;
    };

    const logout = () => {
      Object.assign(state, cloneDeep(defaultState));
    };

    return {
      ...toRefs(state),
      isLoggedIn,
      login,
      logout
    };
  },
  {
    persist: true
  }
);
