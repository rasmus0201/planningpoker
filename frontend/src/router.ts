import { createRouter, createWebHistory } from "vue-router";

import { useUserStore } from "@/pinia/user";
import PPForgotPassword from "@/views/auth/PPForgotPassword.vue";
import PPLogin from "@/views/auth/PPLogin.vue";
import PPRegister from "@/views/auth/PPRegister.vue";
import PPResetPassword from "@/views/auth/PPResetPassword.vue";
import PPCreate from "@/views/game/PPCreate.vue";
import PPHost from "@/views/game/PPHost.vue";
import PPJoin from "@/views/game/PPJoin.vue";
import PPPlay from "@/views/game/PPPlay.vue";
import PPSpectate from "@/views/game/PPSpectate.vue";
import PPAccountSettings from "@/views/PPAccountSettings.vue";
import PPCredits from "@/views/PPCredits.vue";
import PPHome from "@/views/PPHome.vue";
import PPLegal from "@/views/PPLegal.vue";

const routes = [
  { path: "/", name: "home", component: PPHome },
  { path: "/account", name: "account.settings", component: PPAccountSettings },
  { path: "/account/logout", name: "account.logout", component: PPLogin },

  { path: "/game/create", name: "game.create", component: PPCreate },
  { path: "/game/join", name: "game.join", component: PPJoin },

  {
    path: "/game/host/:pin",
    name: "game.host",
    component: PPHost,
    meta: { gameLayout: true, joinType: "host" }
  },
  {
    path: "/game/play/:pin",
    name: "game.play",
    component: PPPlay,
    meta: { gameLayout: true, joinType: "play" }
  },
  {
    path: "/game/spectate/:pin",
    name: "game.spectate",
    component: PPSpectate,
    meta: { gameLayout: true, joinType: "spectate" }
  },

  { path: "/login", name: "auth.login", component: PPLogin },
  { path: "/register", name: "auth.register", component: PPRegister },
  { path: "/forgot-password", name: "auth.forgotPassword", component: PPForgotPassword },
  { path: "/reset-password", name: "auth.resetPassword", component: PPResetPassword },

  { path: "/legal", name: "legal", component: PPLegal },
  { path: "/credits", name: "credits", component: PPCredits }
];

const router = createRouter({
  history: createWebHistory(),
  routes
});

router.beforeEach(async (to, from, next) => {
  if (to.name === "legal") {
    return next();
  }

  const userStore = useUserStore();
  if (userStore.token && to.name?.toString().startsWith("auth.")) {
    return next({ name: "home" });
  }

  if (!userStore.token && !to.name?.toString().startsWith("auth.")) {
    return next({ name: "auth.login" });
  }

  if (to.name === "account.logout") {
    await userStore.logout();

    return next({ name: "auth.login" });
  }

  return next();
});

export default router;
