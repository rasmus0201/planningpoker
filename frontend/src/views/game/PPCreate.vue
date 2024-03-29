<script lang="ts" setup>
import { ref } from "vue";
import { useRouter } from "vue-router";

import { useApi } from "@/composables";

const router = useRouter();

const api = useApi();

const state = ref<"init" | "loading" | "success" | "error">("init");

const dateFormatted = new Date().toLocaleDateString("da-DK", {
  year: "numeric",
  month: "2-digit",
  day: "2-digit"
});

const titles = [
  "Planning",
  "Refinement",
  "🥶🥶🥶",
  "💩💩💩",
  "👽👽👽",
  "👻👻👻",
  "🤖🤖🤖",
  "👾👾👾",
  "🤡🤡🤡",
  "🤠🤠🤠",
  "💪💪💪",
  "🤟🤟🤟",
  "🖖🖖🖖",
  "🦄🦄🦄",
  "🐉🐉🐉",
  "🐲🐲🐲",
  "🦖🦖🦖",
  "🦕🦕🦕",
  "🐊🐊🐊",
  "🐍🐍🐍",
  "🦥🦥🦥",
  "🦦🦦🦦",
  "🐔🐔🐔",
  "🐓🐓🐓",
  "🐣🐣🐣",
  "🐤🐤🐤",
  "🐥🐥🐥",
  "🐦🐦🐦",
  "🐧🐧🐧",
  "🦆🦆🦆",
  "🦢🦢🦢",
  "🦉🦉🦉",
  "🇷 🇸 🇴  🇼 🇦 🇸  🇭 🇪 🇷 🇪"
];
const randomTitle = titles[Math.floor(Math.random() * titles.length)];

const title = ref(`${randomTitle} ${dateFormatted}`);

const onCreate = async () => {
  state.value = "loading";
  try {
    const response = await api.post("/games", { title: title.value });
    const body = response.data;

    router.push({ name: "game.host", params: { pin: body.data.pin } });

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
