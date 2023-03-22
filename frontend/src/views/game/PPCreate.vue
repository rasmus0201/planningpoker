<script lang="ts" setup>
import { ref } from "vue";
import { useRouter } from "vue-router";

import { API_URL } from "@/config";
import { useUserStore } from "@/pinia/user";

const router = useRouter();
const userStore = useUserStore();

const state = ref<"init" | "loading" | "success" | "error">("init");

const dateFormatted = new Date().toLocaleDateString("da-DK", {
  year: "numeric",
  month: "2-digit",
  day: "2-digit"
});
const title = ref(`Planning ${dateFormatted}`);

const onCreate = async () => {
  state.value = "loading";
  try {
    const response = await fetch(`${API_URL}/games`, {
      method: "POST",
      headers: new Headers({ "Content-Type": "application/json", Authorization: userStore.authHeader }),
      body: JSON.stringify({ title: title.value })
    });

    if (!response.ok) {
      throw new Error();
    }

    const json = await response.json();

    router.push({ name: "game.host", params: { pin: json.game.pin }, query: { fresh: "true" } });

    state.value = "success";
  } catch (error) {
    state.value = "error";
  }
};
</script>

<template>
  <div class="columns is-centered py-4">
    <div class="column is-one-third">
      <div class="box">
        <form @submit.prevent="onCreate()">
          <fieldset :disabled="state === 'loading'">
            <div v-if="state === 'error'" class="field">
              <p class="has-text-danger">Please try again.</p>
            </div>
            <div class="field">
              <label class="label">Title</label>
              <div class="control">
                <input v-model="title" class="input" type="text" />
              </div>
            </div>

            <button type="submit" class="button is-primary">Create game</button>
          </fieldset>
        </form>
      </div>
    </div>
  </div>
</template>
