<script setup lang="ts">
import { usePreferredDark } from "@vueuse/core";
import { watch } from "vue";
import { RouterView, useRoute } from "vue-router";

import TheFooter from "@/components/TheFooter.vue";
import TheHeader from "@/components/TheHeader.vue";
import { useUserStore } from "@/pinia/user";

const userStore = useUserStore();
const route = useRoute();

const isDarkMode = usePreferredDark();

watch(
  isDarkMode,
  (value) => {
    if (value) {
      document.documentElement.classList.add("dark");
      document.body.classList.add("dark");
    } else {
      document.documentElement.classList.remove("dark");
      document.body.classList.remove("dark");
    }
  },
  { immediate: true }
);
</script>

<template>
  <div class="app-view">
    <template v-if="route.meta.gameLayout">
      <TheHeader :hide-by-default="true" />
      <main>
        <Suspense>
          <RouterView></RouterView>
        </Suspense>
      </main>
    </template>
    <template v-else>
      <TheHeader v-if="userStore.isLoggedIn" :hide-by-default="false" />
      <main class="container is-fluid is-relative">
        <Suspense>
          <RouterView></RouterView>
        </Suspense>
      </main>
      <TheFooter />
    </template>
  </div>
</template>

<style lang="scss" scoped>
.app-view {
  min-height: 100vh;
  display: flex;
  flex-direction: column;
}
</style>

<style>
.w-100 {
  width: 100%;
}
</style>
