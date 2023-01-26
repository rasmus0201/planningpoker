<script lang="ts" setup>
import { onBeforeUnmount, onMounted, ref } from "vue";
import { useRouter } from "vue-router";

import { API_URL } from "@/config";
import { useUserStore } from "@/pinia/user";
import type { Game } from "@/types";

const router = useRouter();
const userStore = useUserStore();

const pin = ref("");
const joinType = ref<"play" | "spectate">("play");

const input = ref<HTMLInputElement>();
const autofocus = (e: KeyboardEvent) => {
  if (document.activeElement === input.value) {
    return;
  }

  if (e.shiftKey || e.ctrlKey || e.metaKey) {
    return;
  }

  input.value?.focus();
};

onMounted(() => {
  window.addEventListener("keydown", autofocus);
});

onBeforeUnmount(() => {
  window.removeEventListener("keydown", autofocus);
});

const state = ref<"init" | "loading" | "success" | "error">("init");
const onSubmit = async () => {
  state.value = "loading";
  try {
    const response = await fetch(`${API_URL}/games/${pin.value}`, {
      method: "GET",
      headers: new Headers({ "Content-Type": "application/json", Authorization: userStore.authHeader })
    });

    if (!response.ok) {
      throw new Error();
    }

    const json = await response.json();
    const game = json.game as Game;

    if (!game.pin || game.state === "finished") {
      throw new Error();
    }

    router.push({ name: `game.${joinType.value}`, params: { pin: pin.value }, query: { fresh: "true" } });

    state.value = "success";
  } catch (error) {
    state.value = "error";
  }
};
</script>

<template>
  <div class="form-container">
    <form class="form hero-body is-justify-content-center is-align-items-center" @submit.prevent="onSubmit()">
      <fieldset class="w-100" :disabled="state === 'loading'">
        <div class="columns is-flex is-flex-direction-column box">
          <div class="column">
            <label for="email" class="title mb-5 is-inline-block w-100 has-text-centered">Game Pin</label>
            <input
              ref="input"
              v-model="pin"
              autofocus
              autocomplete="off"
              class="input is-primary"
              required
              minlength="6"
              maxlength="6"
              type="tel"
              placeholder="123456"
            />
            <p v-if="state === 'error'" class="has-text-danger mt-1">Game does not exist.</p>
          </div>
          <div class="column is-flex buttons-container">
            <button class="button is-info is-fullwidth" type="submit" @click="joinType = 'play'">Play</button>
            <button class="button is-primary is-fullwidth" type="submit" @click="joinType = 'spectate'">
              Spectate
            </button>
          </div>
        </div>
      </fieldset>
    </form>
  </div>
</template>

<style lang="scss" scoped>
.form-container {
  position: absolute;
  display: flex;
  justify-content: center;
  height: 100%;
  width: 100%;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;

  @media (min-width: 1024px) {
    align-items: center;
  }
}

.form {
  max-width: 100%;

  @media (min-width: 768px) {
    max-width: 400px;
  }

  @media (min-width: 1024px) {
    margin-top: -20vh;
  }
}

.buttons-container {
  gap: 8px;
}
</style>
